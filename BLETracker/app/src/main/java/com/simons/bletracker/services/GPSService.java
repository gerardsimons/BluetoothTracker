package com.simons.bletracker.services;

import android.app.Service;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.location.Location;
import android.os.Binder;
import android.os.Bundle;
import android.os.IBinder;
import android.util.Log;
import android.widget.Toast;

import com.google.android.gms.common.ConnectionResult;
import com.google.android.gms.common.GooglePlayServicesUtil;
import com.google.android.gms.common.api.GoogleApiClient;
import com.google.android.gms.location.LocationRequest;
import com.google.android.gms.location.LocationServices;

public class GPSService extends Service implements GoogleApiClient.ConnectionCallbacks,
        GoogleApiClient.OnConnectionFailedListener, com.google.android.gms.location.LocationListener {

    /** Use this in conjunction with PendingIntent to run while the app is killed or in background **/
    public class ProximityIntentReceiver extends BroadcastReceiver {

        @SuppressWarnings("deprecation")
        @Override
        public void onReceive(Context context, Intent intent) {
            //action to be performed
            Log.d(TAG + "ProximityIntentReceiver", "Received something something");
        }
    }

    private static final String TAG = GPSService.class.getSimpleName();
    private static GoogleApiClient GoogleApi;

    private final IBinder mBinder = new LocalBinder();

    private LocationRequest mLocationRequest;
    private Location latestLocation;

    private static final int PLAY_SERVICES_RESOLUTION_REQUEST = 8694;
    private static final int UPDATE_INTERVAL = 1000;
    private static final int FASTEST_INTERVAL = 500;
    private static final int DISPLACEMENT = 1;

    public static final String ACTION_NAME = "com.simons.bletracker.gps_read";
    public static final String NEW_LOCATION_KEY = "NEW_LOCATION";

    public class LocalBinder extends Binder {
        public GPSService getService() {
            return GPSService.this;
        }
    }

    @Override
    public void onConnected(Bundle bundle) {
        Log.d(TAG,"Succesfully connected");
        startLocationUpdates();
    }

    @Override
    public void onConnectionSuspended(int i) {
        Log.d(TAG,"Connection suspended");
    }

    @Override
    public void onLocationChanged(Location newLocation) {
        latestLocation = newLocation;

        Log.d(TAG,"New location received = " + newLocation);

        //Broadcast new location
        Intent intent = new Intent();
        intent.putExtra(NEW_LOCATION_KEY,newLocation);
        sendBroadcast(intent);
    }

    @Override
    public void onConnectionFailed(ConnectionResult connectionResult) {
        Log.e(TAG, "GoogleApiClient connection failed");
    }

    @Override
    public boolean onUnbind(Intent intent) {
        Log.d(TAG, "Unbind event");
        return super.onUnbind(intent);
    }

    /**
     * Method to verify google play services on the device
     * */
    private boolean checkPlayServices() {
        int resultCode = GooglePlayServicesUtil
                .isGooglePlayServicesAvailable(this);
        if (resultCode != ConnectionResult.SUCCESS) {
            if (GooglePlayServicesUtil.isUserRecoverableError(resultCode)) {
//                GooglePlayServicesUtil.getErrorDialog(resultCode, this,
//                        PLAY_SERVICES_RESOLUTION_REQUEST).show();
            } else {
                Toast.makeText(getApplicationContext(),
                        "This device is not supported.", Toast.LENGTH_LONG)
                        .show();
                stopSelf();
            }
            return false;
        }
        Log.d(TAG,"Google Play Services are available");
        return true;
    }

    /**
     * Starting the location updates
     * */
    public void startLocationUpdates() {
        LocationServices.FusedLocationApi.requestLocationUpdates(GoogleApi, mLocationRequest, this);
        Log.d(TAG, "Periodic location updates started!");


        //BELOW IS FOR UPDATING WHILE APP IS KILLED (?)
//        String proximitys = "ACTION";
//        IntentFilter filter = new IntentFilter(proximitys);
//        registerReceiver(mybroadcast, filter);
//        Intent intent = new Intent(proximitys);
//        PendingIntent proximityIntent = PendingIntent.getBroadcast(this, 0,
//                intent, PendingIntent.FLAG_CANCEL_CURRENT);
//        LocationServices.FusedLocationApi.requestLocationUpdates(GoogleApi, mLocationRequest, proximityIntent);


    }

    /**
     * Creating location request object
     * */
    protected void createLocationRequest() {
        mLocationRequest = new LocationRequest();
        mLocationRequest.setInterval(UPDATE_INTERVAL);
        mLocationRequest.setFastestInterval(FASTEST_INTERVAL);
        mLocationRequest.setPriority(LocationRequest.PRIORITY_HIGH_ACCURACY);
        mLocationRequest.setSmallestDisplacement(DISPLACEMENT); // 10 meters
    }

    /**
     * Creating google api client object
     * */
    protected synchronized void buildGoogleApiClient() {
        Log.d(TAG, "Building new Google API Client");
        GoogleApi = new GoogleApiClient.Builder(this)
                .addConnectionCallbacks(this)
                .addOnConnectionFailedListener(this)
                .addApi(LocationServices.API).build();
    }

    @Override
    public void onCreate() {
        super.onCreate();

        Log.d(TAG,"GPSService created");
        // First we need to check availability of play services
        if (checkPlayServices()) {

            createLocationRequest();

            // Building the GoogleApi client
            if(GoogleApi == null) {
                buildGoogleApiClient();
            }
            GoogleApi.connect();
        }
    }

    @Override
    public void onDestroy() {
        super.onDestroy();

        Log.d(TAG, "GPSService was destroyed.");
        if (GoogleApi.isConnected()) {
            GoogleApi.disconnect();
            GoogleApi = null;
        }
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        Log.i(TAG, "Received start id " + startId + ": " + intent);
        // We want this service to continue running until it is explicitly
        // stopped, so return sticky.
        return START_STICKY;
    }

    /*
     *      DEBUG METHOD TO MOCK LOCATION; USEFUL TO TEST LOCATION UPDATES DISPLACEMENT
     */
    public static void _mockLocation(float latitude, float longitude) {
        Log.d(TAG, "Set mock location : (" + latitude + "," + longitude + ")");

        LocationServices.FusedLocationApi.setMockMode(GoogleApi, true);
        Location targetLocation = new Location("");//provider name is unecessary
        targetLocation.setLatitude(latitude);//your coords of course
        targetLocation.setLongitude(longitude);
        LocationServices.FusedLocationApi.setMockLocation(GoogleApi, targetLocation);
        LocationServices.FusedLocationApi.setMockMode(GoogleApi, false);
    }

    @Override
    public IBinder onBind(Intent intent) {
        return mBinder;
    }
}
