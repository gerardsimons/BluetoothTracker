package com.simons.bletracker.controllers;

import android.location.Location;
import android.util.Log;

import com.simons.bletracker.Configuration;
import com.simons.bletracker.models.IntegerBarcode;
import com.simons.bletracker.models.sql.Customer;
import com.simons.bletracker.models.sql.GPSMeasurement;
import com.simons.bletracker.models.sql.Order;
import com.simons.bletracker.models.sql.OrderCase;
import com.simons.bletracker.models.sql.RSSIMeasurement;
import com.simons.bletracker.remote.ServerAPI;
import com.simons.bletracker.services.BLEDiscoveryService;
import com.simons.bletracker.services.GPSService;

import org.json.JSONObject;

import java.util.ArrayList;

/**
 * Created by gerard on 23/07/15.
 *
 * This is the main controller driving this application. It takes care of data management relating to tags, gps and others.
 * It also determines what services to run depending on this data and the state of the application.
 *
 */
public class BLETracker {

    private static final String TAG = "BLETracker";
    private static BLETracker instance;
    private static int CACHE_SIZE = 10;
    private static int DISTANCE_TRIGGER = 300; //The distance in meters which is considered to trigger departure and arrivals

    private StateController stateController;
    private ServerAPI serverAPI;

    private String deviceId;
    private String installId;

    private Location departureLocation;
    private ArrayList<OrderCase> orderCases;
    private ArrayList<Order> orders;

    private ArrayList<GPSMeasurement> gpsMeasurements;
    private ArrayList<RSSIMeasurement> rssiMeasurements;

    private GPSService gpsService;
    private BLEDiscoveryService discoveryService;

    private BLETracker() {
        serverAPI = ServerAPI.GetInstance();
        stateController = StateController.GetInstance();

        gpsService = GPSService.GetInstance();
    }

    private Order getOrderWithID(int id) {
        for(Order order : orders) {
            if(order.getID() == id) {
                return order;
            }
        }
        return null;
    }

    public static BLETracker GetInstance() {
        if(instance == null) {
            instance = new BLETracker();
        }
        return instance;
    }

    public void newOrderCaseScanned(String orderCaseCode,String bleTagMac) {

        //Get the start location to be used to measure departure
        departureLocation = gpsService.getLocation();

        //Convert to barcode
        IntegerBarcode barcode = new IntegerBarcode(orderCaseCode, Configuration.BARCODE_COMPONENT_LENGTHS);

        //Extract the IDs from the barcode
        int customerId = barcode.getComponent(1);
        int orderId = barcode.getComponent(2);
        int orderCaseId = barcode.getComponent(3);

        Log.d(TAG,String.format("Barcode scanned. CustomerID = %d\n orderId = %d\n orderCaseId = %d",customerId,orderId,orderCaseId));

        //Check if order already created
        Order existingOrder = getOrderWithID(orderId);
        if(existingOrder == null) { //No existing order found, so create in database
            Order newOrder = new Order(orderId,new Customer(customerId));
            orders.add(newOrder);
            existingOrder = newOrder;

            serverAPI.addNewOrder(newOrder.getID(), new ServerAPI.ServerRequestListener() {
                @Override
                public void onRequestFailed() {
                    Log.e(TAG, "Unable to add new order to server.");
                }

                @Override
                public void onRequestCompleted(JSONObject response) {
                    Log.d(TAG,"Added new order to server succesfully");
                }
            });
        }

        //Always create the new order case as well
        final OrderCase orderCase = new OrderCase(orderCaseId,existingOrder);
        serverAPI.addNewOrderCase(existingOrder.getID(), orderCase.getID(), new ServerAPI.ServerRequestListener() {
            @Override
            public void onRequestFailed() {
                Log.e(TAG,"Unable to add new order case");
            }

            @Override
            public void onRequestCompleted(JSONObject response) {
                Log.d(TAG,"Added new ordercase to server.");
            }
        });

        //Keep track of the new order case
        orderCases.add(orderCase);
    }

    public void newGPSData(Location newLocation) {
        StateController.State currentState = stateController.getState();

        switch (currentState) {
            case READY_FOR_DEPARTURE:
                    //Check if it has departed
                float distance = departureLocation.distanceTo(newLocation);
                if(distance > DISTANCE_TRIGGER) {
                    //It has departed
                }
                break;
            case EN_ROUTE: //Cache data

                //If there are still some cases undelivered
                for(OrderCase orderCase : orderCases) {
//                    orderCase.
                }

                break;
            case ARRIVED: //Only check if it has returned
                break;
        }


        //Or if any of its order cases have arrived

        //Or when no more order cases need to arrive whether it has returned
    }

    public void onRSSIReceived() {

    }

    /**
     *  Flush the tracking data (GPS, RSSI and sensoric) to the server
     */
    public void flushTrackingData() {
        if(!rssiMeasurements.isEmpty() || !gpsMeasurements.isEmpty()) {
            serverAPI.sendTrackingData(deviceId, installId, (RSSIMeasurement[]) rssiMeasurements.toArray(), (GPSMeasurement[]) gpsMeasurements.toArray(), new ServerAPI.ServerRequestListener() {
                @Override
                public void onRequestFailed() {
                    Log.e(TAG,"Unable to send tracking data to server");
                }

                @Override
                public void onRequestCompleted(JSONObject response) {
                    Log.d(TAG,"Succesfully sent " + rssiMeasurements.size() + " rssi measurements and " + gpsMeasurements.size() + "GPS points to the server");

                    //Safe to clear the cache
                    rssiMeasurements.clear();
                    gpsMeasurements.clear();
                }
            });
        }
    }


    /*** GETTERS AND SETTERS ***/

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

}
