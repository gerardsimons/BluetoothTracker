package com.simons.bluetoothtracker.activities;


import android.app.Activity;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.hardware.Sensor;
import android.hardware.SensorEvent;
import android.hardware.SensorEventListener;
import android.hardware.SensorManager;
import android.os.Bundle;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.widget.Toast;

import com.simons.bluetoothtracker.BluetoothTrackerApplication;
import com.simons.bluetoothtracker.CompassSettings;
import com.simons.bluetoothtracker.R;
import com.simons.bluetoothtracker.controllers.BluetoothLeService;
import com.simons.bluetoothtracker.controllers.CompassController;
import com.simons.bluetoothtracker.controllers.OrientationSensor;
import com.simons.bluetoothtracker.views.CompassView;

import java.util.Random;

public class TestDeviceControlActivity extends Activity implements SensorEventListener {
    private final static String TAG = "TestDeviceControlActivity";

    private BluetoothTrackerApplication application;

    //private CompassView compassView;
    private CompassController compassController;

    private boolean enableSensors = false;

    private SensorManager mSensorManager;

    private OrientationSensor orientationSensor;

    private float azimuth = 0f;

    private boolean mConnected = false;

    //The rate in milliseconds we want to measure, this is used to keep all types of measurments roughly synchronized (RSSI, motion,...)
    public static final int MEASUREMENTS_RATE = 100;

    private static final String MEASUREMENT = "com.simons.bluetoothtracker.intent.action.rssi";

    private static final String RSSI_KEY = "rssi";
    private static final String AZIMUTH_KEY = "azimuth";
    private static final String AZIMUTH_DELTA_KEY = "azimuth_delta";

    BroadcastReceiver receiver = new BroadcastReceiver() {
        @Override
        public void onReceive(Context ctx, Intent intent) {
            Bundle bundle = intent.getExtras();
            if (bundle.containsKey(RSSI_KEY)) {
                int rssi = intent.getIntExtra(RSSI_KEY, 999);
                Toast.makeText(TestDeviceControlActivity.this, "Received RSSI = " + rssi, Toast.LENGTH_SHORT).show();
                Log.i(TAG, "Received RSSI = " + rssi);

                compassController.addData(rssi, azimuth);
            }
            if (bundle.containsKey(AZIMUTH_KEY)) {
                float newAzimuth = intent.getFloatExtra(AZIMUTH_KEY, -1);
                Toast.makeText(TestDeviceControlActivity.this, "Received Azimuth = " + newAzimuth, Toast.LENGTH_SHORT).show();
                Log.i(TAG, "Received Azimuth = " + newAzimuth);

                Random r = new Random();

                azimuth = newAzimuth;
//                compassController.addData(-1 * r.nextInt(100), azimuth);
                compassController.setRotation(azimuth);
            }
            if (bundle.containsKey(AZIMUTH_DELTA_KEY)) {
                float azimuthDelta = intent.getFloatExtra(AZIMUTH_DELTA_KEY, -1);
                Toast.makeText(TestDeviceControlActivity.this, "Received Azimuth Delta = " + azimuthDelta, Toast.LENGTH_SHORT).show();
                Log.i(TAG, "Received Azimuth Delta = " + azimuthDelta);

                Random r = new Random();

                azimuth += azimuthDelta;
                //compassController.addData(-1 * r.nextInt(100),azimuth);
                compassController.setRotation(azimuth);
            }
        }
    };


    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.device_control);

        application = (BluetoothTrackerApplication) getApplication();

        loadCompass();

        mSensorManager = (SensorManager) getSystemService(Context.SENSOR_SERVICE);
        if (mSensorManager.getDefaultSensor(Sensor.TYPE_ACCELEROMETER) != null) {
            // Success! There's a magnetometer.
            //	    motionSensor = mSensorManager.getDefaultSensor(Sensor.TYPE_ACCELEROMETER);
            //	    magneticSensor = mSensorManager.getDefaultSensor(Sensor.TYPE_MAGNETIC_FIELD);
            orientationSensor = new OrientationSensor(mSensorManager, this);
        } else {
            // Failure! No magnetometer.
            Log.e(TAG, "The device has no accelerometer.");
            finish();
        }
    }

    private void loadCompass() {
        if (application != null) {
            CompassView compassView = (CompassView) findViewById(R.id.compassView);

            CompassSettings settings = application.loadCompassSettings();
            settings.showPointer = false;
            settings.showColors = true;
            settings.showDebugText = true;
            settings.calibrationLimit = 2;

            compassController = new CompassController(settings, compassView);
            compassController.setFilterAlpha(0F);

//          compassController.addData(-1,355);
//          compassController.addData(-1,5);
//          compassController.addData(-10,15);

//          compassController.addData(new int[]{-1,10,-10},new float[]{355,5,15});

//          compassController.computePointer();


            //Test values for 5 fragments
//          compassController.setRotation(90);
//
//          Fill the compass with equal values
//
            if (true) {
                float rotationDelta = 360F / settings.nrOfFragments;
                float rotation = rotationDelta / 2;

                for (int i = 0; i < settings.nrOfFragments; i++) {
//                    if (i > .8 * settings.nrOfFragments)
//                        compassController.addData(-50, rotation);
//                    else
//                    {
//                        int rssi = (int) Math.round(Math.random() * -30 - 70);
//                        compassController.addData(rssi, rotation);
//                    }
                    compassController.addData(-20,rotation);
                    compassController.addData(-40,rotation);
                    rotation += rotationDelta;
                }
                azimuth = rotationDelta / 2F;
                compassController.setRotation(0);
            }
            azimuth = 36;
            compassController.setRotation(azimuth);
//            for(int i = 0 ; i < 5 ; i++) {
//                compassController.addData(-30,azimuth);
//            }
//            compassController.addData(0,180);
//            compassController.addData(0,0);
        }
    }

    @Override
    protected void onResume() {
        super.onResume();

        IntentFilter actionFilter = new IntentFilter();
        actionFilter.addAction(MEASUREMENT);

        registerReceiver(receiver, actionFilter);
        if (orientationSensor != null && enableSensors)
            orientationSensor.register(this, MEASUREMENTS_RATE);
    }

    @Override
    protected void onPause() {
        super.onPause();

        unregisterReceiver(receiver);

        orientationSensor.unregister();
    }

    private static IntentFilter makeGattUpdateIntentFilter() {
        final IntentFilter intentFilter = new IntentFilter();
        intentFilter.addAction(BluetoothLeService.ACTION_GATT_CONNECTED);
        intentFilter.addAction(BluetoothLeService.ACTION_GATT_DISCONNECTED);
        intentFilter.addAction(BluetoothLeService.ACTION_RSSI_VALUE_READ);
        return intentFilter;
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.gatt_services, menu);
        if (mConnected) {
            menu.findItem(R.id.menu_connect).setVisible(false);
            menu.findItem(R.id.menu_disconnect).setVisible(true);
        } else {
            menu.findItem(R.id.menu_connect).setVisible(true);
            menu.findItem(R.id.menu_disconnect).setVisible(false);
        }
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        invalidateOptionsMenu();
        switch (item.getItemId()) {
            case R.id.menu_connect:
                mConnected = true;
                return true;
            case R.id.menu_disconnect:
                mConnected = false;
                return true;
            case android.R.id.home:
                onBackPressed();
                return true;
            case R.id.menu_settings:
                Intent intent = new Intent(this,SettingsActivity.class);
                startActivityForResult(intent, SettingsActivity.SETTINGS_REQUEST_CODE);
                return true;
        }
        return super.onOptionsItemSelected(item);
    }

    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (requestCode == SettingsActivity.SETTINGS_REQUEST_CODE) {
            if(resultCode == RESULT_OK) { //Changes have been made to the compass settings
                Log.i(TAG,"Changes to compass settings made.");
                CompassView compassView = (CompassView) findViewById(R.id.compassView);
                boolean needToResetCompass = data.getBooleanExtra(SettingsActivity.INT_VALUES_CHANGED_KEY,false);
                if(needToResetCompass) {
                    Log.i(TAG,"Need to reset compass.");
                    loadCompass();
                }
                else {
                    Log.i(TAG,"No need to reset compass.");
                    compassController.setCompassViewSettings(application.loadCompassSettings());
                }
            }
            if (resultCode == RESULT_CANCELED) { //No changes made
                //Write your code if there's no result
                Log.i(TAG,"No changes to compass settings made.");
            }
        }
    }

    @Override
    public void onAccuracyChanged(Sensor sensor, int accuracy) {
        Log.i(TAG,"Accuracy sensors has changed to : " + accuracy);
    }

    @Override
    public void onSensorChanged(SensorEvent event) {
        if (orientationSensor != null && compassController != null) {

            azimuth = (float)Math.toDegrees(orientationSensor.getM_azimuth_radians());
            //Flip the orientation
//            azimuth = 360F - azimuth;
            //Log.d(TAG, "azimuth = " + azimuth);
            compassController.setRotation(azimuth);
        }
    }
}