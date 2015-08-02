package com.example.gerard.googleserviceexample;

import android.app.Dialog;

import android.content.DialogInterface;
import android.content.Intent;
import android.content.IntentSender;
import android.location.Location;
import android.net.Uri;
import android.support.v4.app.DialogFragment;
import android.support.v7.app.ActionBarActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;
import com.google.android.gms.common.ConnectionResult;
import com.google.android.gms.common.GooglePlayServicesUtil;
import com.google.android.gms.common.api.GoogleApiClient;
import com.google.android.gms.location.LocationRequest;
import com.google.android.gms.location.LocationServices;


public class MyActivity extends ActionBarActivity implements
        GoogleApiClient.ConnectionCallbacks,
        GoogleApiClient.OnConnectionFailedListener,
        com.google.android.gms.location.LocationListener {

    // TAG for Log.d
    private static final String TAG = MyActivity.class.getName();

    // Keys for save bundle during lifecycle
    private static final String KEYLOCCOUNTER = "locCnt";
    private static final String KEYGOOGLEERROR = "googleerror";
    private static final String KEYLASTLOC = "lastloc";
    private static final String KEYWANTLOCATIONS = "wantlocs";
    private static final String KEYDISTTOHOME = "disttohome";
    private static final String DIALOG_ERROR = "dialog_error";

    // Constants for Google Location API
    private static final int REQUEST_RESOLVE_ERROR = 1001;
    private static final int INTERVAL = 2000;
    private static final int FASTESTINTERVAL = 1000;
    private static final int NUMLOOKUPS = 10;

    // Black Rock City is home!
    private static final double HOMELAT = 40.789598;
    private static final double HOMELON = -119.203214;

    // Variables that hold location API/info/etc..
    private GoogleApiClient mGoogleApiClient = null;
    private LocationRequest mLocationRequest = null;
    private Location mLastLocation = null;
    private boolean wantLocations = true;
    private boolean mGoogleError = false;
    private double distToHome = 0.0;
    private int locCounter = NUMLOOKUPS;


    // **********************************************************************
    // Lifecycle
    //   http://developer.android.com/guide/components/activities.html#Lifecycle
    // **********************************************************************

    // The app is created
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_my);

        updateValuesFromBundle(savedInstanceState);

        // Update location info if we come back from a pause/rotate/etc...
        updateLocationText();

        // Get the API client if we currently want location info
        if (wantLocations) mGoogleApiClient = getGoogleApiClient();

        Log.d(TAG, "onCreate");
    }

    // The app is destroyed
    @Override
    protected void onDestroy() {
        super.onDestroy();
        Log.d(TAG, "onDestroy");
    }

    // Becoming visible
    @Override
    protected void onStart() {
        super.onStart();
        Log.d(TAG, "onStart");
    }

    // No longer visible
    @Override
    protected void onStop() {
        super.onStop();
        Log.d(TAG, "onStop");
    }

    // And finally, interacting with the user
    @Override
    protected void onResume() {
        super.onResume();
        // We have resumed.  Start location updates again (if we want them)
        // This will get the APIClient if needed, and so forth
        startLocationUpdates();
        Log.d(TAG, "onResume");
    }

    // Not interacting with the user (for example, a system dialog is covering us)
    @Override
    protected void onPause() {
        // Stop location updates, otherwise we'll receive onLocationChanged() updates
        // while we're running in the background
        stopLocationUpdates();
        super.onPause();
        Log.d(TAG, "onPause");
    }

    // Everytime we hide the application or even if we rotate the screen the
    // app is killed after giving us a chance to save our state in a Bundle.
    // So we save our vars that will let us restart our last state once the app
    // comes back.  An easy way to cause this is to rotate the view.
    public void onSaveInstanceState(Bundle savedInstanceState) {
        super.onSaveInstanceState(savedInstanceState);
        savedInstanceState.putInt(KEYLOCCOUNTER, locCounter);
        savedInstanceState.putParcelable(KEYLASTLOC, mLastLocation);
        savedInstanceState.putBoolean(KEYGOOGLEERROR, mGoogleError);
        savedInstanceState.putBoolean(KEYWANTLOCATIONS, wantLocations);
        savedInstanceState.putDouble(KEYDISTTOHOME, distToHome);
    }

    // We're back, unpack our state from the Bundle
    private void updateValuesFromBundle(Bundle savedInstanceState) {
        if (savedInstanceState == null) return;

        if (savedInstanceState.keySet().contains(KEYLOCCOUNTER))
            locCounter = savedInstanceState.getInt(KEYLOCCOUNTER);
        if (savedInstanceState.keySet().contains(KEYLASTLOC))
            mLastLocation = savedInstanceState.getParcelable(KEYLASTLOC);
        if (savedInstanceState.keySet().contains(KEYGOOGLEERROR))
            mGoogleError = savedInstanceState.getBoolean(KEYGOOGLEERROR);
        if (savedInstanceState.keySet().contains(KEYWANTLOCATIONS))
            wantLocations = savedInstanceState.getBoolean(KEYWANTLOCATIONS);
        if (savedInstanceState.keySet().contains(KEYDISTTOHOME))
            distToHome = savedInstanceState.getDouble(KEYDISTTOHOME);
    }


    // **********************************************************************
    // GOOGLE FUSED LOCATION CODE
    // **********************************************************************

    // Get the API Client that we need to request location updates
    // This may not succeed, then we startup the error dialog that Google can
    // give us to resolve the issue (which may be, for example, needing to login)
    private GoogleApiClient getGoogleApiClient() {
        int resultCode = GooglePlayServicesUtil.isGooglePlayServicesAvailable(getApplicationContext());

        if (resultCode == ConnectionResult.SUCCESS) {
            setTextView(R.id.apiStatus, "Avail");
            Log.d(TAG, "Google play services!");
            return new GoogleApiClient.Builder(this)
                    .addApi(LocationServices.API)
                    .addConnectionCallbacks(this)
                    .addOnConnectionFailedListener(this)
                    .build();
        }
        setTextView(R.id.apiStatus, "FAIL");
        int RQS_GooglePlayServices = 0;
        GooglePlayServicesUtil.getErrorDialog(resultCode, this, RQS_GooglePlayServices);
        Log.d(TAG, "No google play services");
        return null;
    }

    // When the Google API client connects, it calls this because
    // of the ".addConnectionCallbacks" above.  We've connected now,
    // so we can get location or request regular location updates.
    @Override
    public void onConnected(Bundle connectionHint) {
        setTextView(R.id.apiStatus, "Connected");

       /*
        // Get location now
        mLastLocation = LocationServices.FusedLocationApi.getLastLocation(
                mGoogleApiClient);
        if (mLastLocation != null)
            setTextView(R.id.location, mLastLocation.getLatitude() + ", " + mLastLocation.getLongitude());
        else
            setTextView(R.id.location, "NULL location?");
        */

        // Request location updates
        mLocationRequest = LocationRequest.create();
        mLocationRequest.setPriority(LocationRequest.PRIORITY_HIGH_ACCURACY);
        // // This doesn't get updates in the genymotion emulator
        //mLocationRequest.setPriority(LocationRequest.PRIORITY_BALANCED_POWER_ACCURACY);
        mLocationRequest.setInterval(INTERVAL);
        mLocationRequest.setFastestInterval(FASTESTINTERVAL);

        LocationServices.FusedLocationApi.requestLocationUpdates(
                mGoogleApiClient, mLocationRequest, this);
    }

    // Whenever we want to start/restart location updates, we can call this.
    // This happens onResume, when we reopen the app, presuming we want location updates.
    // We also have a button that can reset the location counter and call this.
    private void startLocationUpdates() {
        if (!wantLocations) return;
        if (mGoogleError) return;
        // Get the client if we don't have it (such as if we are resuming)
        // If we do this, this will call connect for us
        if (mGoogleApiClient==null) mGoogleApiClient = getGoogleApiClient();
        if (mGoogleApiClient==null || mGoogleError) return;
        // If we have the client but it's not connected, then connect it.
        if (!mGoogleApiClient.isConnected())
            // Start the connection process which will start the location request
            mGoogleApiClient.connect();
        else
            // It's possible we've connected already but just want to start
            // getting location updates again.  We'll cheat by calling the
            // onConnected callback - we can just use a null arg because we
            // don't actually care about the Bundle supplied to the callback
            onConnected(null);
    }

    // Stop location updates if we are currently getting them
    private void stopLocationUpdates() {
        if (wantLocations && mGoogleApiClient != null)
            LocationServices.FusedLocationApi.removeLocationUpdates(mGoogleApiClient, this);
        //if (mGoogleApiClient != null) mGoogleApiClient.disconnect();
    }

    // ****************************************
    // Google API Client connection problems:
    // ****************************************

    // This is the connection suspended callback.
    @Override
    public void onConnectionSuspended(int i) {
        Log.d(TAG, "GoogleAPIClient connection suspended");
        setTextView(R.id.apiStatus, "Suspended");
    }

    // We've had a failure.  If Google tells us there is a resolution, then
    // attempt the resolution.  Otherwise create an ErrorDialogFragment,
    // which is a dialog that uses the error code from the connection result
    // to pick the right error dialog.  (See class below)
    @Override
    public void onConnectionFailed(ConnectionResult result) {
        if (mGoogleError) return;
        setTextView(R.id.apiStatus, "Error?");
        mGoogleError = true;
        if (result.hasResolution()) {
            try {
                result.startResolutionForResult(this, REQUEST_RESOLVE_ERROR);
            } catch (IntentSender.SendIntentException e) {
                // Error with resolution, try again
                mGoogleApiClient.connect();
            }
        } else {
            // Fragment for error dialog
            ErrorDialogFragment dialogFragment = new ErrorDialogFragment();
            // Pass error to be displayed
            Bundle args = new Bundle();
            args.putInt(DIALOG_ERROR, result.getErrorCode());
            dialogFragment.setArguments(args);
            dialogFragment.show(getSupportFragmentManager(), "errordialog");
        }
    }

    // Google API error fragment dialog - once closed assume (hope?) the error is fixed
    public void onDialogDismissed() {
        setTextView(R.id.apiStatus, "Err dismissed");
        mGoogleError = false;
    }

    // The Error Dialog Fragment that uses the result error code from Google
    // to get the error dialog to show.
    public static class ErrorDialogFragment extends DialogFragment {
        public ErrorDialogFragment() { }

        @Override
        public Dialog onCreateDialog(Bundle savedInstanceState) {
            // Get error and show dialog
            int errorCode = this.getArguments().getInt(DIALOG_ERROR);
            return GooglePlayServicesUtil.getErrorDialog(errorCode,this.getActivity(), REQUEST_RESOLVE_ERROR);
        }

        @Override
        public void onDismiss(DialogInterface dialog) {
            ((MyActivity)getActivity()).onDialogDismissed();
        }
    }

    // Hopefully we've resolved connection issues with GoogleAPI, try to connect again
    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (requestCode == REQUEST_RESOLVE_ERROR) {
            setTextView(R.id.apiStatus, "Err resolved");
            mGoogleError = false;
            if (resultCode == RESULT_OK) {
                // Make sure the app is not already connected or attempting to connect
                if (!mGoogleApiClient.isConnecting() && !mGoogleApiClient.isConnected()) {
                    mGoogleApiClient.connect();
                }
            }
        }
    }

    // Calculate distance between two points (spherical model of earth, ~3% max error)
    // Uses haversine formula:  http://www.movable-type.co.uk/scripts/latlong.html
    public double distance(double latA, double lonA, double latB, double lonB) {
        double R = 6371.0;   // km
        double φ1 = latA * Math.PI / 180;
        double φ2 = latB * Math.PI / 180;
        double Δφ = (latB - latA) * Math.PI / 180;
        double Δλ = (lonB - lonA) * Math.PI / 180;

        double a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                Math.cos(φ1) * Math.cos(φ2) *
                        Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
        double c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        double d = R*c;
        return Math.rint(d*10)/10;
    }

    // Our callback for everytime location changes.
    public void onLocationChanged(Location location) {
        locCounter--;

        mLastLocation = location;
        updateLocationText();

        if (locCounter < 1) {
            stopLocationUpdates();
            wantLocations = false;
        }
    }

    // Update the location info textViews if we have the info, either because
    // a location update was called or because we've come back via onResume
    private void  updateLocationText() {
        if (mLastLocation == null) {
            ((Button) findViewById(R.id.map)).setEnabled(false);
            return;
        }

        setTextView(R.id.locCounter, "" + locCounter);
        setTextView(R.id.location, mLastLocation.getLatitude() + ", " + mLastLocation.getLongitude());
        distToHome = distance(mLastLocation.getLatitude(), mLastLocation.getLongitude(), HOMELAT, HOMELON);
        setTextView(R.id.distanceHome, distToHome + "km");

        // Enable the map button since we now have a location
        ((Button) findViewById(R.id.map)).setEnabled(true);
    }

    // **********************************************************************
    // VIEW CODE
    // **********************************************************************
    // Update a TextView by it's id
    private void setTextView(int id, String j) {
        ((TextView)findViewById(id)).setText(j);
    }

    // Button for restarting location updates
    public void moreLocationsPlease(View view) {
        wantLocations = true;
        locCounter=NUMLOOKUPS;
        startLocationUpdates();
        Log.d(TAG, "More locations please");
    }

    // Call google Maps with an intent
    public void map(View view) {
        // Directions to 'home' - though only works on the same continent, so not great for a demo.  :)
        //Uri uri = Uri.parse("https://www.google.com/maps/dir/'"+mLastLocation.getLatitude()+","+mLastLocation.getLongitude()+"'/'"+HOMELAT+","+HOMELON+"'");
        Uri uri = Uri.parse("geo:"+mLastLocation.getLatitude()+","+mLastLocation.getLongitude());
        Intent intent = new Intent(android.content.Intent.ACTION_VIEW,uri);
        // Hint to use google maps
        intent.setClassName("com.google.android.apps.maps","com.google.android.maps.MapsActivity");
        startActivity(intent);
    }

    /*  We don't use the settings/menu, so these are commented out
    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.my, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();
        if (id == R.id.action_settings) {
            return true;
        }
        return super.onOptionsItemSelected(item);
    }
    */

}
