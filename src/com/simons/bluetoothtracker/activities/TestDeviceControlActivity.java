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
import android.widget.Toast;

import com.simons.bluetoothtracker.BluetoothTrackerApplication;
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

    //    private GraphViewSeries motionSeries;
    //    private GraphViewSeries thresholdSeries;
    //    private LineGraphView graphView;



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
            if(bundle.containsKey(RSSI_KEY)) {
                int rssi = intent.getIntExtra(RSSI_KEY,999);
                Toast.makeText(TestDeviceControlActivity.this,"Received RSSI = " + rssi,Toast.LENGTH_SHORT).show();
                Log.i(TAG,"Received RSSI = " + rssi);

                compassController.addData(rssi,azimuth);
            }
            if(bundle.containsKey(AZIMUTH_KEY)) {
                int newAzimuth = intent.getIntExtra(AZIMUTH_KEY,-1);
                Toast.makeText(TestDeviceControlActivity.this,"Received Azimuth = " + newAzimuth,Toast.LENGTH_SHORT).show();
                Log.i(TAG,"Received Azimuth = " + newAzimuth);

                Random r = new Random();

                azimuth = newAzimuth;
                compassController.addData(-1 * r.nextInt(100),azimuth);
                compassController.setRotation(azimuth);
            }
            if(bundle.containsKey(AZIMUTH_DELTA_KEY)) {
                int azimuthDelta = intent.getIntExtra(AZIMUTH_DELTA_KEY,-1);
                Toast.makeText(TestDeviceControlActivity.this,"Received Azimuth Delta = " + azimuthDelta,Toast.LENGTH_SHORT).show();
                Log.i(TAG,"Received Azimuth Delta = " + azimuthDelta);

                Random r = new Random();

                azimuth += azimuthDelta;
                compassController.addData(-1 * r.nextInt(100),azimuth);
                compassController.setRotation(azimuth);
            }
        }
    };

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.device_control_test);

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

//            int maxValuesSize = application.loadIntValue(BluetoothTrackerApplication.MAX_VALUES_SIZE_KEY);
//            int nrOfFragments = application.loadIntValue(BluetoothTrackerApplication.FRAGMENTS_NUMBER_KEY);
//            int calibrationLimit = application.loadIntValue(BluetoothTrackerApplication.CALIBRATION_LIMIT_KEY);

            //Compass settings
            int maxValuesSize = 5;
            int nrOfFragments = 8;
            int calibrationLimit = 10;

            compassController = new CompassController(nrOfFragments, calibrationLimit, maxValuesSize, compassView);
            compassController.setFilterAlpha(0F);

            //Test values for 5 fragments
//            compassController.setRotation(90);
//
//            //Fill the compass with equal values
//
            float rotationDelta = 360F / nrOfFragments;
            float rotation = 90F + rotationDelta;

            for(int i = 0 ; i < nrOfFragments ; i++) {
                compassController.addData(0,rotation);
                rotation += rotationDelta;
            }

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
        if (orientationSensor != null)
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
    public void onAccuracyChanged(Sensor sensor, int accuracy) {

    }

    private float toDegrees(float rads) {
        float degrees = (float) (rads * (180F / Math.PI));
        if (degrees < 0) {
            degrees = 360 + degrees;
        }
        return degrees;
    }

    @Override
    public void onSensorChanged(SensorEvent event) {
        if (orientationSensor != null && compassController != null) {

            azimuth = toDegrees(orientationSensor.getM_azimuth_radians());
            //Flip the orientation
            azimuth = 360F - azimuth;
            //Log.d(TAG, "azimuth = " + azimuth);
            compassController.setRotation(azimuth);
        }
    }
}