package com.simons.bletracker.activities;

import android.app.Activity;
import android.content.BroadcastReceiver;
import android.content.ComponentName;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.ServiceConnection;
import android.location.Location;
import android.os.Bundle;
import android.os.IBinder;
import android.util.Log;
import android.widget.Toast;

import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.MapFragment;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.MarkerOptions;
import com.simons.bletracker.R;
import com.simons.bletracker.services.GPSService;

public class MapActivity extends Activity {

    private int mockLocationIndex = 0;
    private final float[] mockLocations = new float[]{
            51.925518F, 4.468968F,   //Rotterdam Centraal
            51.930665F,4.469419F     //Thuis Rotterdam
    };
    private static final String TAG = MapActivity.class.getSimpleName();
    private GoogleMap map;

    /** Use this in conjunction with PendingIntent to run while the app is killed or in background **/
    public class ProximityIntentReceiver extends BroadcastReceiver {

        @SuppressWarnings("deprecation")
        @Override
        public void onReceive(Context context, Intent intent) {
            //action to be performed
            Log.d(TAG + "ProximityIntentReceiver", "Received something something");
        }
    }

    /**
     * Helper function to connect to GPS service and register as receiver
     */
    private void startGPSTracking() {
        Log.d(TAG, "Starting GPS Tracking!");

        IntentFilter filter = new IntentFilter();
        filter.addAction(GPSService.ACTION_NAME);
        filter.addCategory(Intent.CATEGORY_DEFAULT);
        registerReceiver(gpsReceiver, filter);

        Intent serviceIntent = new Intent(this, GPSService.class);
        bindService(serviceIntent, gpsServiceConnection, Context.BIND_AUTO_CREATE);
    }

    /**
     * SERVICE CONNECTIONS *
     */
    private final ServiceConnection gpsServiceConnection = new ServiceConnection() {
        @Override
        public void onServiceConnected(ComponentName componentName, IBinder service) {
//            isGSPTracking = true;

            Log.d(TAG, "Connected to GPS service.");
//            gpsService = ((GPSService.LocalBinder) service).getService();
            //Immediately start requestion periodic location updates

        }

        @Override
        public void onServiceDisconnected(ComponentName componentName) {
            Log.d(TAG, "Disconnected from GPS service.");
//            gpsService = null;
        }
    };

    @Override
    public void onPause() {
        super.onPause();

        unregisterReceiver(gpsReceiver);
        unbindService(gpsServiceConnection);
    }

    //The broadcast receiver directed towards receiving location updates from the GPSService (if started)
    BroadcastReceiver gpsReceiver = new BroadcastReceiver() {
        @Override
        public void onReceive(Context context, Intent intent) {
            if (intent.hasExtra(GPSService.NEW_LOCATION_KEY)) {
                Location newLocation = intent.getParcelableExtra(GPSService.NEW_LOCATION_KEY);
                Log.d(TAG, "New location received = " + newLocation);
                Toast.makeText(MapActivity.this,"New GPS Received : " + newLocation,Toast.LENGTH_LONG).show();

                processNewLocation(newLocation);
            }
        }
    };

    public void processNewLocation(Location newLocation) {

        Log.d(TAG, "New location received = " + newLocation);
        if(map != null) {
            //IF you want an ugly circle thingy
//            map.addCircle(new CircleOptions().center(new LatLng(newLocation.getLatitude(),newLocation.getLongitude())).radius(10000)
//                    .strokeColor(Color.BLACK)
//                    .fillColor(Color.BLUE));

            map.addMarker(new MarkerOptions().position(new LatLng(newLocation.getLatitude(),newLocation.getLongitude())));
            map.animateCamera(CameraUpdateFactory.newLatLngZoom(new LatLng(newLocation.getLatitude(), newLocation.getLongitude()), 15.0f));
        }
        else Log.w(TAG,"Map object is null");
    }

    @Override
    public void onResume() {
        super.onResume();

        startGPSTracking();
    }

    @Override
    public void onCreate(Bundle bundle) {
        super.onCreate(bundle);
        setContentView(R.layout.activity_map);

        map = ((MapFragment) getFragmentManager().findFragmentById(R.id.map)).getMap();
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
    }

}
