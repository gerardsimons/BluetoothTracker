package com.simons.bletracker.controllers;

import android.content.BroadcastReceiver;
import android.content.ComponentName;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.ServiceConnection;
import android.location.Location;
import android.os.IBinder;
import android.util.Log;
import android.widget.Toast;

import com.simons.bletracker.Configuration;
import com.simons.bletracker.controllers.StateController.OnStateChangedListener;
import com.simons.bletracker.models.IntegerBarcode;
import com.simons.bletracker.models.MacAddress;
import com.simons.bletracker.models.sql.BLETag;
import com.simons.bletracker.models.sql.Customer;
import com.simons.bletracker.models.sql.GPSMeasurement;
import com.simons.bletracker.models.sql.Order;
import com.simons.bletracker.models.sql.OrderCase;
import com.simons.bletracker.models.sql.RSSIMeasurement;
import com.simons.bletracker.models.sql.Route;
import com.simons.bletracker.models.sql.SQLLocation;
import com.simons.bletracker.models.sql.SensorMeasurement;
import com.simons.bletracker.remote.ServerAPI;
import com.simons.bletracker.services.BLEDiscoveryService;
import com.simons.bletracker.services.GPSService;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.UnsupportedEncodingException;
import java.util.ArrayList;
import java.util.Iterator;

/**
 * Created by gerard on 23/07/15.
 * <p/>
 * This is the main controller driving this application. It takes care of data management relating to tags, gps and others.
 * It also determines what services to run depending on this data and the state of the application.
 */
public class BLETracker implements OnStateChangedListener, GPSService.GPSListener {

    private static final String TAG = "BLETracker";
    private static BLETracker Instance;
    private static Context AppContext;     //A context is required to start and bind to the GPS and BLE services

    private BLEAuthorizationController authorizationController;
    private StateController stateController;
    private ServerAPI serverAPI;

    private String deviceId;
    private String installId;

    private Location departureLocation;
    private Route activeRoute;

    private ArrayList<OrderCase> orderCases;
    /**
     * The order cases we are currently tracking *
     */
    private ArrayList<Order> orders;
    /**
     * The orders we are currently tracking *
     */
    private ArrayList<GPSMeasurement> gpsMeasurements;
    private ArrayList<RSSIMeasurement> rssiMeasurements;
    private ArrayList<SensorMeasurement> sensorMeasurements;

    private boolean isBLETracking = false;
    private boolean isGSPTracking = false;

    private GPSService gpsService;
    private BLEDiscoveryService discoveryService;

    /**
     * SERVICE CONNECTIONS
     */
    private final ServiceConnection gpsServiceConnection = new ServiceConnection() {
        @Override
        public void onServiceConnected(ComponentName componentName, IBinder service) {
            isGSPTracking = true;

            Log.d(TAG, "Connected to GPS service.");
            gpsService = ((GPSService.LocalBinder) service).getService();
            //Immediately start requestion periodic location updates
            gpsService.startLocationUpdates();

            gpsService.registerListener(BLETracker.this);
        }

        @Override
        public void onServiceDisconnected(ComponentName componentName) {
            Log.d(TAG, "Disconnected from GPS service.");
            gpsService = null;
        }
    };

    private final ServiceConnection rssiServiceConnection = new ServiceConnection() {
        @Override
        public void onServiceConnected(ComponentName componentName, IBinder service) {
            isBLETracking = true;

            Log.d(TAG, "Connected to BLE discovery service.");
            discoveryService = ((BLEDiscoveryService.LocalBinder) service).getService();

            if (!discoveryService.initialize()) {
                Log.e(TAG, "Unable to initialize Bluetooth");
            } else discoveryService.startScanning();
        }

        @Override
        public void onServiceDisconnected(ComponentName componentName) {
            Log.d(TAG, "Disconnected from BLE Discovery service.");
            discoveryService = null;
        }
    };

    /**
     * BROADCAST RECEIVERS *
     */
    BroadcastReceiver bleDiscoveryReceiver = new BroadcastReceiver() {
        @Override
        public void onReceive(Context context, Intent intent) {
            if (intent.hasExtra(BLEDiscoveryService.DEVICE_THRESHOLD)) {

                //Get the values from the intent
                String name = intent.getStringExtra(BLEDiscoveryService.DEVICE_NAME);
                String address = intent.getStringExtra(BLEDiscoveryService.DEVICE_ADDRESS);
                int rssi = intent.getIntExtra(BLEDiscoveryService.DEVICE_RSSI, -999);

                BLETag tag = null;
                try {
                    tag = new BLETag(name, new MacAddress(address), rssi);
                } catch (UnsupportedEncodingException e) {
                    e.printStackTrace();
                }

                Log.d(TAG, "New BLE tag : " + tag.toString());
                processRSSI(tag, rssi);
            }
        }
    };

    //The broadcast receiver directed towards receiving location updates from the GPSService (if started)
    BroadcastReceiver gpsReceiver = new BroadcastReceiver() {
        @Override
        public void onReceive(Context context, Intent intent) {

            Toast.makeText(AppContext, "Received something...", Toast.LENGTH_LONG).show();

            if (intent.hasExtra(GPSService.NEW_LOCATION_KEY)) {

                Location newLocation = intent.getParcelableExtra(GPSService.NEW_LOCATION_KEY);
                Log.d(TAG, "New location received = " + newLocation);
                Toast.makeText(AppContext, "New GPS Received : " + newLocation, Toast.LENGTH_LONG).show();

                processGPSReading(newLocation);
            }
        }
    };

    private BLETracker() {
        stateController = StateController.GetInstance();
        stateController.registerListener(this);
        serverAPI = ServerAPI.GetInstance();
        authorizationController = BLEAuthorizationController.getInstance();

        orders = new ArrayList<>();
        orderCases = new ArrayList<>();

        gpsMeasurements = new ArrayList<>();
        rssiMeasurements = new ArrayList<>();
        sensorMeasurements = new ArrayList<>();
    }

    private Order getOrderWithID(int id) {
        for (Order order : orders) {
            if (order.getID() == id) {
                return order;
            }
        }
        return null;
    }

    public static void SetContext(Context context) {
        BLETracker.AppContext = context;
    }

    public static BLETracker GetInstance() {
        if (AppContext == null) {
            throw new RuntimeException("No context is set");
        }
        if (Instance == null) {
            Instance = new BLETracker();
        }
        return Instance;
    }

    //TODO: Change ServerRequestTask so it can handle multiple (3 in this case) requests and return all requests at once, rather than nesting them like here
    public void newOrderCaseScanned(String orderCaseCode, final MacAddress bleTagMac) {

        //Get the start location to be used to measure departure
//         TODO: Only do this one time?
//        departureLocation = GPSService.GetLocation();
//        Log.d(TAG,"Setting as departure location : " + departureLocation);

        //Convert to barcode
        final IntegerBarcode barcode = new IntegerBarcode(orderCaseCode, Configuration.BARCODE_COMPONENT_LENGTHS);

        //Extract the IDs from the barcode
        int customerId = barcode.getComponent(1);
        final int orderId = barcode.getComponent(2);
        final int orderCaseId = barcode.getComponent(3);

        Log.d(TAG, String.format("Barcode scanned. CustomerID = %d\n orderId = %d\n orderCaseId = %d", customerId, orderId, orderCaseId));

        //Check if order already created
        Order existingOrder = getOrderWithID(orderId);
        if (existingOrder == null) { //No existing order found, so create in database
            Log.d(TAG, "Creating new order #" + orderId);

            final Order newOrder = new Order(orderId, new Customer(customerId));
            existingOrder = newOrder;

            serverAPI.addNewOrder(newOrder.getID(), customerId, new ServerAPI.ServerRequestListener() {
                @Override
                public void onRequestFailed() {
                    Log.e(TAG, "Unable to add new order to server.");
                }

                @Override
                public void onRequestCompleted(JSONObject response) {
                    Log.d(TAG, "Added new order to server succesfully");
                    Log.d(TAG, "Response = \n" + response);
                }
            });
        }

        //Always create the new order case as well
        Log.d(TAG, "Creating new ordercase #" + orderId + "-" + orderCaseId);
        final OrderCase orderCase = new OrderCase(orderCaseId, existingOrder);
        serverAPI.addNewOrderCase(existingOrder.getID(), orderCase.getID(), bleTagMac.getMinifiedAddress(), barcode.toString(), new ServerAPI.ServerRequestListener() {
            @Override
            public void onRequestFailed() {
                Log.e(TAG, "Unable to add new order case");
            }

            @Override
            public void onRequestCompleted(JSONObject response) {
                Log.d(TAG, "Added new ordercase to server.");
                Log.d(TAG, "Response = \n" + response);
                //Keep track of the new order case

                try {
                    JSONObject orderJSON = response.getJSONObject("order");
                    JSONObject customerJSON = orderJSON.getJSONObject("customer");
                    JSONObject locationJSON = customerJSON.getJSONObject("location");
                    SQLLocation customerLocation = new SQLLocation(
                            locationJSON.getInt("id"),
                            locationJSON.getString("name"),
                            (float)locationJSON.getDouble("latitude"),
                            (float)locationJSON.getDouble("longitude"),
                            "street",
                            0,
                            "city",
                            "zip_code"
                    );

                    int orderId = orderJSON.getInt("id");
                    Order existingOrder = getOrderWithID(orderId);
                    Customer customer = new Customer(customerJSON.getInt("id"), customerJSON.getString("name"), customerLocation);

                    if(existingOrder != null) {
                        existingOrder.setCustomer(customer);
                    }
                    else {
                        existingOrder = new Order(orderJSON.getInt("id"),customer);
                        orders.add(existingOrder);
                    }
//                    orderCase.setOrder(existingOrder);

                    orderCases.add(new OrderCase(orderCaseId,existingOrder));

                    //Everything went well, we can safely progress to the next state
                    stateController.doAction(Action.SCAN_CASE);
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }
        });
    }

    /**
     * Process a new location update, depending on the state the location may be handled in different ways
     *
     * @param newLocation
     */
    public void processGPSReading(Location newLocation) {

        if (isGSPTracking) {
            State currentState = stateController.getState();
            switch (currentState) {
                case READY_FOR_DEPARTURE:
                    //Check if it has departed
                    if (departureLocation != null) {
                        float distance = departureLocation.distanceTo(newLocation);
                        if (distance >= Configuration.DEPART_DISTANCE) {
                            //It has departed
                            Log.d(TAG,"We have departed!");
                            Toast.makeText(AppContext,"Unit has departed",Toast.LENGTH_LONG).show();
                            stateController.doAction(Action.DEPART);
                        }
                        else {
                            float distanceLeft = Configuration.DEPART_DISTANCE - distance;
                            Log.d(TAG,"Distance until departed = " + distanceLeft);
                            Toast.makeText(AppContext,"Distance until departed = " +distanceLeft,Toast.LENGTH_LONG).show();
                        }
                    } else {
                        departureLocation = newLocation;
                        Log.d(TAG, "New departure location set: " + departureLocation.toString());
                    }
                    break;
                case EN_ROUTE: //Cache data
                    //If there are still some cases undelivered, check whether they have arrived to their destinations
                    Iterator<OrderCase> i = orderCases.iterator();
                    while (i.hasNext()) {
                        OrderCase orderCase = i.next();
                        //Determine if the current location is sufficiently close to the customer's location
                        SQLLocation customerLocation = orderCase.getOrder().getCustomer().getLocation();
                        if (customerLocation != null) {
                            float[] results = new float[3];
                            Location.distanceBetween(customerLocation.getLatitude(), customerLocation.getLongitude(), newLocation.getLatitude(), newLocation.getLongitude(), results);
                            float distance = results[0];

                            Log.d(TAG, "Distance between the current location and order #" + orderCase.getOrder().getID() + " is " + distance);
                            if (distance < Configuration.ARRIVE_DISTANCE) { //TODO: Do all location proximity checking in a new controller
                                Log.d(TAG, "SO... You may consider it arrived!");

                                //Remove from list
                                i.remove();
                            } else { //Not yet arrived
                                String message = "Order #" + orderCase.getOrder().getID() + " has " + (distance - Configuration.ARRIVE_DISTANCE) + " m to go.";
                                Log.d(TAG,message);
                                Toast.makeText(AppContext,message,Toast.LENGTH_LONG).show();
                            }
                        }
                        else {
                            Log.w(TAG,"No customer location found for order case, I don't know where it should go!");
                        }
                    }

                    //When no more order cases to be delivered, we can consider this finished
                    if(orderCases.size() == 0) {
                        String message = "All packages have been delivered.";
                        Toast.makeText(AppContext,message,Toast.LENGTH_LONG);
                        Log.d(TAG, message);

                        stateController.doAction(Action.FINISHED);
                        break;
                    }

                    Log.d(TAG,"New gps measurement added");
                    gpsMeasurements.add(new GPSMeasurement((float)newLocation.getLatitude(),(float)newLocation.getLongitude(),System.currentTimeMillis() / 1000L));
                    checkFlush();

                    break;
                case ALL_DELIVERED: //Only check if it has returned
                    float distance = departureLocation.distanceTo(newLocation);
                    if (distance < Configuration.ARRIVE_DISTANCE) {
                        //It has returned
                        String message = "'Oost,West,Thuis Best' --- The BLETracker has returned home.";
                        Log.d(TAG, message);
                        Toast.makeText(AppContext, message, Toast.LENGTH_LONG);
                        stateController.doAction(Action.RETURN);
                    }
                    else {
                        String message = distance + " m to go before home.";
                        Log.d(TAG, message);
                        Toast.makeText(AppContext, message, Toast.LENGTH_LONG);
                    }
                    break;
            }
        }
        else {
            Log.d(TAG,"Not currently doing GPS tracking...");
        }


        //Or if any of its order cases have arrived

        //Or when no more order cases need to arrive whether it has returned
    }

    /**
     * Connect to the BLE Discovery Service and register as receiver to receive updates.
     */
    private void startBLETracking() {
        if (!isBLETracking) {
            Log.d(TAG, "Starting BLE Tracking!");
            AppContext.registerReceiver(bleDiscoveryReceiver, new IntentFilter(BLEDiscoveryService.ACTION_NAME));
            Intent serviceIntent = new Intent(AppContext, BLEDiscoveryService.class);
            AppContext.bindService(serviceIntent, rssiServiceConnection, Context.BIND_AUTO_CREATE);
        } else Log.w(TAG, "Already BLE tracking!");
    }

    /**
     * Connect to the GPS service and register as receiver if not already running/connected
     */
    private void startGPSTracking() {
        if (!isGSPTracking) {
            Log.d(TAG, "Starting GPS Tracking!");
            AppContext.registerReceiver(gpsReceiver, new IntentFilter(GPSService.ACTION_NAME));
            Intent serviceIntent = new Intent(AppContext, GPSService.class);
            AppContext.bindService(serviceIntent, gpsServiceConnection, Context.BIND_AUTO_CREATE);
        } else Log.w(TAG, "Already GPS tracking!");
    }

    /**
     * Check whether or not the tracking data should be flushed i.e. pushed to the server
     * it flushes when the combined size of all the measurements lists exceeds CACHE_SIZE
     */
    private void checkFlush() {
        int allData = rssiMeasurements.size() + gpsMeasurements.size() + sensorMeasurements.size();

        Log.d(TAG,"Cache size = " + allData);

        if (allData >= Configuration.TRACKER_CACHE_SIZE) {
            flushTrackingData();
        }
    }

    public void processRSSI(BLETag tag, int rssi) {
        if (authorizationController.isAuthorized(tag)) {
            if (isBLETracking) {
                RSSIMeasurement rssiMeasurement = new RSSIMeasurement(rssi, System.currentTimeMillis() / 1000L, tag);
                rssiMeasurements.add(rssiMeasurement);

                //Check if we need to push the data to the server
                checkFlush();
            } else {
                Log.d(TAG, "BLETracker is not currently tracking STATE = " + stateController.getState().toString());
            }
        } else {
            //This BLETracker is not authorized to track this BLE_Tag
        }
    }

    /**
     * Flush the tracking data (GPS, RSSI and sensoric) to the server
     */
    public void flushTrackingData() {
        if (!rssiMeasurements.isEmpty() || !gpsMeasurements.isEmpty()) {
            if (activeRoute != null) {
                Log.d(TAG, "Flushing all tracking data...");
                Toast.makeText(AppContext,"Flushing tracking data",Toast.LENGTH_SHORT).show();
                serverAPI.sendTrackingData(activeRoute.getId(), rssiMeasurements, gpsMeasurements, new ServerAPI.ServerRequestListener() {
                    @Override
                    public void onRequestFailed() {
                        Log.e(TAG, "Unable to send tracking data to server");
                    }

                    @Override
                    public void onRequestCompleted(JSONObject response) {
                        Log.d(TAG,"Response = " + response.toString());

                        Log.d(TAG, "Succesfully sent " + rssiMeasurements.size() + " rssi measurements and " + gpsMeasurements.size() + "GPS points to the server");

                        //Safe to clear the cache
                        rssiMeasurements.clear();
                        gpsMeasurements.clear();
                    }
                });
            }
            else {
                Log.e(TAG,"No current route set!");
            }
        }
    }

    //** DEBUG FUNCTIONS **/
    public void _debugDepart() {
//        serverAPI.startRoute();
    }

    /**
     * Interface implementation that handles new state transitions
     * @param transition defines the transition that happened, contains the action, and the from and to state
     */
    @Override
    public void OnStateTransitioned(Transition transition) {
        //If going from idle to 'ready for departure' we should activate the GPS tracker
        if(transition.fromState == State.IDLE && transition.toState == State.READY_FOR_DEPARTURE) {
            Log.d(TAG,"We are ready to depart, activating GPS service.,");
            startGPSTracking();
        }
        if(transition.action == Action.DEPART) { //Create and start route

            //Unwrap the ordercase ids and order ids that are to be tracked
            int[] orderIds = new int[orderCases.size()];
            int[] orderCaseIds = new int[orderCases.size()];

            for(int i = 0 ; i < orderCases.size() ; ++i) {
                OrderCase orderCase = orderCases.get(i);
                orderIds[i] = orderCase.getID();
                orderCaseIds[i] = orderCase.getOrder().getID();
            }

            serverAPI.createRoute(deviceId, installId, orderIds, orderCaseIds, new ServerAPI.ServerRequestListener() {
                @Override
                public void onRequestFailed() {
                    Log.e(TAG,"Too bad: this don't work son");
                }

                @Override
                public void onRequestCompleted(JSONObject response) {
                    try {
                        int routeId = response.getInt(ServerAPI.GetKeys.ID);
                        activeRoute = new Route(routeId);
                    } catch (JSONException e) {
                        e.printStackTrace();
                    }
                }
            });

            startBLETracking();
        }
    }

    /**
     * GETTERS AND SETTERS **
     */

    public void setDeviceId(String deviceId) {
        this.deviceId = deviceId;
    }

    public String getDeviceId() {
        return deviceId;
    }

    public void setInstallId(String installId) {
        this.installId = installId;
    }

    public String getInstallId() {
        return installId;
    }

    @Override
    public void OnNewLocationReceived(Location newLocation) {

        Log.d(TAG,"New location callback");

        processGPSReading(newLocation);
    }
}
