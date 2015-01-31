package com.devriesdev.whereatassettracking.system;

import android.content.Context;
import android.content.Intent;
import android.location.Location;

/*import com.google.android.gms.common.ConnectionResult;
import com.google.android.gms.common.GooglePlayServicesClient;
import com.google.android.gms.location.LocationClient;
import com.google.android.gms.location.LocationListener;
import com.google.android.gms.location.LocationRequest;*/

/**
 * Created by danie_000 on 8/15/2014.
 */
public class Locator {

    private static final String TAG = "Locator";

    private final static int CONNECTION_FAILURE_RESOLUTION_REQUEST = 9000;

    private final Context context;
    private final String ACTION_UICALLBACK;

    private static final long UPDATE_INTERVAL = 5000;
    private static final long FASTEST_INTERVAL = 1000;

    //private LocationClient locationClient;
    //private LocationRequest locationRequest;
    //boolean updatesRequested;

    private Location lastLocation;

    //private GooglePlayServicesLocator googlePlayServicesLocator;
    //private boolean googlePlayServicesAvailable = false;

    public Locator(final Context context, final String UICallbackAction) {
        this.context = context;
        ACTION_UICALLBACK = UICallbackAction;

        notifyUI("Locator created!");
    }

    public void init(boolean googlePlayServicesAvailable) {
        //this.googlePlayServicesAvailable = googlePlayServicesAvailable;

        /*if (googlePlayServicesAvailable) {

            googlePlayServicesLocator = new GooglePlayServicesLocator();

            locationClient = new LocationClient(this.context, googlePlayServicesLocator, googlePlayServicesLocator);

            locationRequest = LocationRequest.create();
            locationRequest.setPriority(LocationRequest.PRIORITY_HIGH_ACCURACY);
            locationRequest.setInterval(UPDATE_INTERVAL);

        }

        updatesRequested = false;*/
    }

    /*private class GooglePlayServicesLocator implements
            GooglePlayServicesClient.ConnectionCallbacks,
            GooglePlayServicesClient.OnConnectionFailedListener,
            LocationListener {

        public GooglePlayServicesLocator() {

        }

        @Override
        public void onConnected(Bundle dataBundle) {
            notifyUI("Google Play services connected");
            if (updatesRequested) {
                locationClient.requestLocationUpdates(locationRequest, this);
            }

            lastLocation = locationClient.getLastLocation();
        }

        @Override
        public void onDisconnected() {
            notifyUI("Google Play services disconnected");
        }

        @Override
        public void onConnectionFailed(ConnectionResult connectionResult) {
            notifyUI("Could not connect to Google Play services");
        }

        @Override
        public void onLocationChanged(Location location) {
            if (updatesRequested) {
                lastLocation = location;
            }
        }
    }*/

    public boolean isConnected() {
        //return locationClient.isConnected();
        return true;
    }

    public void connect(boolean requestUpdates) {
        /*locationClient.connect();
        updatesRequested = requestUpdates;*/
    }

    public void disconnect() {
        /*if (locationClient.isConnected()) {
            locationClient.removeLocationUpdates(googlePlayServicesLocator);
        }
        locationClient.disconnect();*/
    }

    public void requestUpdates() {
        /*if (!updatesRequested) {
            updatesRequested = true;
            locationClient.requestLocationUpdates(locationRequest,googlePlayServicesLocator);
        }*/
    }

    public void stopUpdates() {
        /*if (updatesRequested) {
            updatesRequested = false;
            locationClient.removeLocationUpdates(googlePlayServicesLocator);
        }*/
    }

    private void notifyUI(String message) {
        Intent intent = new Intent();
        intent.setAction(ACTION_UICALLBACK)
                .putExtra("MESSAGE", message)
                .putExtra("TIMESTAMP", System.currentTimeMillis());
        context.sendBroadcast(intent);
    }

    public Location getLastLocation() {
        return lastLocation;
    }

    public void setLastLocation(Location location) {
        lastLocation = location;
    }
}
