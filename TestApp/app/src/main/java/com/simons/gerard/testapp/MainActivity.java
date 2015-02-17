package com.simons.gerard.testapp;

import android.content.BroadcastReceiver;
import android.content.ComponentName;
import android.content.Context;
import android.content.Intent;
import android.content.ServiceConnection;
import android.content.pm.PackageManager;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.os.Bundle;
import android.os.IBinder;
import android.support.v7.app.ActionBarActivity;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.widget.TextView;
import android.widget.Toast;

public class MainActivity extends ActionBarActivity {

    private static final String TAG = "MainActivity";
    private static final String BASE_URL = "http://demo.whereatcloud.com/yesdelft/";

    private boolean mScanning = true;

    private BluetoothLeDiscoveryService discoveryService;

    BroadcastReceiver receiver = new BroadcastReceiver() {
        @Override
        public void onReceive(Context context, Intent intent) {
            if(intent.hasExtra(BluetoothLeDiscoveryService.DEVICE_THRESHOLD)) {

                //Get the values from the intent
                String name = intent.getStringExtra(BluetoothLeDiscoveryService.DEVICE_NAME);
                String address = intent.getStringExtra(BluetoothLeDiscoveryService.DEVICE_ADDRESS);
                int rssi = intent.getIntExtra(BluetoothLeDiscoveryService.DEVICE_RSSI, -1);
                boolean threshold = intent.getBooleanExtra(BluetoothLeDiscoveryService.DEVICE_THRESHOLD,false);


            }
        }
    };

    private final ServiceConnection mServiceConnection = new ServiceConnection() {

        @Override
        public void onServiceConnected(ComponentName componentName, IBinder service) {
            Log.d(TAG,"Connected to discovery service.");
            discoveryService = ((BluetoothLeDiscoveryService.LocalBinder) service).getService();
            if (!discoveryService.initialize()) {
                Log.e(TAG, "Unable to initialize Bluetooth");
                mScanning = true;
                finish();
            }
            else discoveryService.startScanning();
        }

        @Override
        public void onServiceDisconnected(ComponentName componentName) {
            Log.d(TAG,"Disconnected from discovery service.");
            discoveryService = null;
            mScanning = false;
        }
    };

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        if (!getPackageManager().hasSystemFeature(PackageManager.FEATURE_BLUETOOTH_LE)) {
            Toast.makeText(this, "NO BLE SUPPORTED", Toast.LENGTH_SHORT).show();
            finish();
        }

        Intent serviceIntent = new Intent(this,BluetoothLeDiscoveryService.class);
        bindService(serviceIntent, mServiceConnection, BIND_AUTO_CREATE);

//        getGPSCoordinates();
    }

    private void startNewPath() {

    }

    private void getGPSCoordinates() {
        // Acquire a reference to the system Location Manager
        LocationManager locationManager = (LocationManager) MainActivity.this.getSystemService(Context.LOCATION_SERVICE);

        // Define a listener that responds to location updates
        LocationListener locationListener = new LocationListener() {
            public void onLocationChanged(Location location) {

//                Log.d(TAG,"New location received.");

                // Called when a new location is found by the network location provider.
                double latitude = location.getLatitude();
                double longitude = location.getLongitude();

                TextView latitudeText = (TextView)findViewById(R.id.latitudeText);
                TextView longitudeText = (TextView)findViewById(R.id.longitudeText);

                latitudeText.setText(Double.toString(latitude));
                longitudeText.setText(Double.toString(longitude));


            }

            public void onStatusChanged(String provider, int status, Bundle extras) {}

            public void onProviderEnabled(String provider) {}

            public void onProviderDisabled(String provider) {}
        };

// Register the listener with the Location Manager to receive location updates
        locationManager.requestLocationUpdates(LocationManager.GPS_PROVIDER, 0, 0, locationListener);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_main, menu);
        return true;
    }


    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();
        //noinspection SimplifiableIfStatement
        if (id == R.id.action_settings) {
            return true;
        }

        return super.onOptionsItemSelected(item);
    }
}
