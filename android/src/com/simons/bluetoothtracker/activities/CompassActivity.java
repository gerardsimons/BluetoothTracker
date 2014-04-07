/*
 * Copyright (C) 2013 The Android Open Source Project
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

package com.simons.bluetoothtracker.activities;

import android.app.Activity;
import android.content.BroadcastReceiver;
import android.content.ComponentName;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.ServiceConnection;
import android.os.Bundle;
import android.os.IBinder;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import com.simons.bluetoothtracker.BluetoothTrackerApplication;
import com.simons.bluetoothtracker.settings.CompassSettings;
import com.simons.bluetoothtracker.R;
import com.simons.bluetoothtracker.controllers.BluetoothLeService;
import com.simons.bluetoothtracker.controllers.CompassController;
import com.simons.bluetoothtracker.controllers.CompassOrientationSensor;
import com.simons.bluetoothtracker.controllers.DeviceMeasurementsManager;
import com.simons.bluetoothtracker.interfaces.CompassCalibrationListener;
import com.simons.bluetoothtracker.interfaces.CompassOrientationSensorListener;
import com.simons.bluetoothtracker.models.Compass;
import com.simons.bluetoothtracker.sqlite.Measurements;
import com.simons.bluetoothtracker.sqlite.MeasurementsDataSource;
import com.simons.bluetoothtracker.views.CompassView;

import java.util.List;

/**
 * For a given BLE device, this Activity provides the user interface to connect,
 * display data, and display GATT services and characteristics supported by the
 * device. The Activity communicates with {@code BluetoothLeService}, which in
 * turn interacts with the Bluetooth LE API.
 */
public class CompassActivity extends Activity {
    private final static String TAG = "CompassActivity";

    public static final String EXTRAS_DEVICE_NAME = "DEVICE_NAME";
    public static final String EXTRAS_DEVICE_ADDRESS = "DEVICE_ADDRESS";

    private TextView mConnectionState;

    private String mDeviceName;
    private String mDeviceAddress;
    //Defaults to false, enables additional development options
    private boolean developmentMode;

    private TextView rssiValuesTextView;
    //We are not using below anymore
//    private TextView timeDeltasTextView;
//    private TextView rssiDeltasTextView;

    private BluetoothTrackerApplication application;

    //private CompassView compassView;
    private CompassSettings compassSettings;
    private CompassController compassController;

    private BluetoothLeService mBluetoothLeService;

    private CompassOrientationSensor orientationSensor;

    private MeasurementsDataSource measurementsDataSource;

    private float azimuth = 0f;

    private DeviceMeasurementsManager measurementsManager;

    private boolean mConnected = false;
    private boolean paused = false;

    private int refreshRate = 100;

    // Code to manage Service lifecycle.
    private final ServiceConnection mServiceConnection = new ServiceConnection() {

        @Override
        public void onServiceConnected(ComponentName componentName, IBinder service) {
            mBluetoothLeService = ((BluetoothLeService.LocalBinder) service).getService();
            if (!mBluetoothLeService.initialize()) {
                Log.e(TAG, "Unable to initialize Bluetooth");
                finish();
            }
            // Automatically connects to the device upon successful start-up initialization.
            mBluetoothLeService.connect(mDeviceAddress,refreshRate);
        }

        @Override
        public void onServiceDisconnected(ComponentName componentName) {
            mBluetoothLeService = null;
        }
    };

    // Handles various events fired by the Service.
    // ACTION_GATT_CONNECTED: connected to a GATT server.
    // ACTION_GATT_DISCONNECTED: disconnected from a GATT server.
    // ACTION_GATT_SERVICES_DISCOVERED: discovered GATT services.
    // ACTION_DATA_AVAILABLE: received data from the device.  This can be a result of read
    //                        or notification operations.
    private final BroadcastReceiver mGattUpdateReceiver = new BroadcastReceiver() {
        @Override
        public void onReceive(Context context, Intent intent) {
            final String action = intent.getAction();
            if (BluetoothLeService.ACTION_GATT_CONNECTED.equals(action)) {
                mConnected = true;
                updateConnectionState(R.string.connected);
                measurementsManager = new DeviceMeasurementsManager();
                invalidateOptionsMenu();
                Log.i(TAG, "Connected to GATT server.");
                Toast.makeText(CompassActivity.this, "Connected to GATT server", Toast.LENGTH_SHORT).show();
                if (compassController != null) {
                    compassController.clearData();
                }
            } else if (BluetoothLeService.ACTION_GATT_DISCONNECTED.equals(action)) {
                mConnected = false;
                updateConnectionState(R.string.disconnected);
                invalidateOptionsMenu();
                Log.i(TAG, "Disconnected from GATT server.");
                Toast.makeText(CompassActivity.this, "Disconnected from GATT server", Toast.LENGTH_SHORT).show();
            } else if (BluetoothLeService.ACTION_RSSI_VALUE_READ.equals(action)) {
                int newRSSI = intent.getExtras().getInt(BluetoothLeService.RSSI_VALUE_KEY);
                if (isValidRSSI(newRSSI) && !paused) {
                    updateMeasurements(newRSSI);
                }
            }
        }
    };

    private boolean isValidRSSI(int rssi) {
        return (rssi > Compass.MIN_RSSI && rssi < Compass.MAX_RSSI);
    }

    private void updateMeasurements(int rssi) {
        if (measurementsManager != null) {
            //Log.d(TAG, "New RSSI value = " + rssi);
            measurementsManager.addRssiMeasurement(rssi);
            compassController.addData(rssi, azimuth);
            updateUI();
        }
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.device_control);

        application = (BluetoothTrackerApplication) getApplication();
        compassSettings = application.loadCompassSettings();


//        Log.d(TAG,"Found refresh rate value of " + refreshRate);

        final Intent intent = getIntent();
        mDeviceName = intent.getStringExtra(EXTRAS_DEVICE_NAME);
        mDeviceAddress = intent.getStringExtra(EXTRAS_DEVICE_ADDRESS);

        developmentMode = application.getDeveloperMode();

        //Some additional setup when in development mode
        if(developmentMode) {
            measurementsDataSource = new MeasurementsDataSource(this);
            measurementsDataSource.open();
        }

        // Sets up UI references.
        ((TextView) findViewById(R.id.device_address)).setText(mDeviceAddress);

        mConnectionState = (TextView) findViewById(R.id.connection_state);

        rssiValuesTextView = (TextView) findViewById(R.id.rssi_values);
//        timeDeltasTextView = (TextView) findViewById(R.id.timeDeltaValues);
//        rssiDeltasTextView = (TextView) findViewById(R.id.deltaRssiValues);

        //testCompass(compassView, 8);

        loadCompass();

        //	getActionBar().setTitle(mDeviceName);
        //	getActionBar().setDisplayHomeAsUpEnabled(true);
        Intent gattServiceIntent = new Intent(this, BluetoothLeService.class);
        bindService(gattServiceIntent, mServiceConnection, BIND_AUTO_CREATE);

        measurementsManager = new DeviceMeasurementsManager();

        orientationSensor = new CompassOrientationSensor(this);
        orientationSensor.setListener(new CompassOrientationSensorListener() {
            @Override
            public void onNewValueReceived(float newAzimuth) {
//                Log.d(TAG,"New azimuth received = " + newAzimuth);
                azimuth = newAzimuth;
                compassController.setRotation(azimuth);
            }
        });
    }

    private void loadCompass() {
        if (application != null) {
            CompassView compassView = (CompassView) findViewById(R.id.compassView);
            CompassSettings compassSettings = application.loadCompassSettings();
            compassController = new CompassController(compassSettings, compassView);
            compassController.setCompassCalibrationListener(new CompassCalibrationListener() {
                @Override
                public void onCalibrationFinished() {
                    //Pause compass controller
                    CompassActivity.this.paused = true;
                    //Show export possibilities if development mode is on
                    if(developmentMode) {
                        LinearLayout exportToolbar = (LinearLayout) findViewById(R.id.exportToolBar);
                        exportToolbar.setVisibility(View.VISIBLE);

                        //Inform user about what he/she should do
                        Toast.makeText(CompassActivity.this,"Point compass towards label and press export to save.",Toast.LENGTH_LONG).show();

                        Button exportButton = (Button) findViewById(R.id.exportButton);
                        exportButton.setOnClickListener(new View.OnClickListener() {
                            @Override
                            public void onClick(View view) {
                                exportCompass();
                            }
                        });
                    }
                }
            });
        }
    }

    private void exportCompass() {
        Log.i(TAG,"Exporting...");

        final Measurements measurements = compassController.exportCompassData();
        measurements.setTrueAzimuth((int) azimuth);

//        AlertDialog.Builder builder = new AlertDialog.Builder(this);
//        final EditText text = new EditText(this);
//
//        //Ask for the username
//        builder.setTitle("Export").setMessage("What is your name?").setView(text);
//        builder.setPositiveButton("Create", new DialogInterface.OnClickListener() {
//            public void onClick(DialogInterface di, int i) {
//                final String name = text.getText().toString();
//                measurements.setUserName(name);
//
//            }
//        });
//        builder.setNegativeButton("Cancel", new DialogInterface.OnClickListener() {
//            public void onClick(DialogInterface di, int i) {
//                //Export was canceled
//            }
//        });
//        builder.create().show();

        measurementsDataSource.insertMeasurements(measurements);


    }

    private void updateUI() {
        if (measurementsManager != null) {

            String rssiValuesText = new String();
            String timeDeltasText = new String();
            String rssiDeltasText = new String();
            String separator = " ";

            //How many values do we want to show
            int amount = 20;

            List<Integer> rssiValues = measurementsManager.getLastRssiValues(amount);
            List<Float> rssiDeltas = measurementsManager.getLastRssiDeltas(amount);
            List<Float> timeDeltas = measurementsManager.getLastTimeDeltas(amount);
            //List<Float> motionDistances = measurementsManager.getLastAverageDistances(graphAmount);

            //Log.d(TAG, measurementsManager.getAverageOfRSSIValues(5).toString());

            for (int i = 0; i < rssiValues.size(); i++) {
                rssiValuesText += rssiValues.get(i) + separator;
                timeDeltasText += timeDeltas.get(i) + separator;
            }
            for (int i = 0; i < rssiDeltas.size(); i++) {
                rssiDeltasText += Math.round(rssiDeltas.get(i)) + separator;
            }

            rssiValuesTextView.setText(rssiValuesText);
//            timeDeltasTextView.setText(timeDeltasText);
//            rssiDeltasTextView.setText(rssiDeltasText);

            //motionGraphSeries.resetData(new GraphViewData[] {});
            //Append motion values to graph, x is the time delta, y
            //	    for (int i = 0; i < motionDistances.size(); i++) {
            //		float motionValue = motionDistances.get(i);
            //		double d = motionValue;
            //		motionSeries.appendData(new GraphViewData(i, d), true, graphAmount);
            //		thresholdSeries.appendData(new GraphViewData(1, DeviceMeasurmentsManager.MOTION_THRESHOLD), true, graphAmount);
            //	    }
        }
    }

    @Override
    protected void onResume() {
        super.onResume();
        registerReceiver(mGattUpdateReceiver, makeGattUpdateIntentFilter());
        if (orientationSensor != null)
            orientationSensor.start();
    }

    @Override
    protected void onPause() {
        super.onPause();
        mBluetoothLeService.stopReading();
        unregisterReceiver(mGattUpdateReceiver);
        orientationSensor.stop();
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        unbindService(mServiceConnection);
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
                mBluetoothLeService.connect(mDeviceAddress,refreshRate);
                mConnected = true;
                return true;
            case R.id.menu_disconnect:
                mConnected = false;
                mBluetoothLeService.disconnect();
                return true;
            case android.R.id.home:
                onBackPressed();
                mBluetoothLeService.disconnect();
                return true;
            case R.id.menu_settings:
                Intent intent = new Intent(this,SettingsActivity.class);
                startActivity(intent);
                return true;
        }
        return super.onOptionsItemSelected(item);
    }

    private void updateConnectionState(final int resourceId) {
        runOnUiThread(new Runnable() {
            @Override
            public void run() {
                mConnectionState.setText(resourceId);
            }
        });
    }

    private static IntentFilter makeGattUpdateIntentFilter() {
        final IntentFilter intentFilter = new IntentFilter();
        intentFilter.addAction(BluetoothLeService.ACTION_GATT_CONNECTED);
        intentFilter.addAction(BluetoothLeService.ACTION_GATT_DISCONNECTED);
        intentFilter.addAction(BluetoothLeService.ACTION_RSSI_VALUE_READ);
        return intentFilter;
    }

//    @Override
//    public void onSensorChanged(SensorEvent event) {
//        if (measurementsManager != null && orientationSensor != null && compassController != null) {
////            azimuth = (float) Math.toDegrees(orientationSensor.getM_azimuth_radians());
//            azimuth = orientationSensor.getAzimuth();
//            Log.d(TAG, "azimuth = " + azimuth);
//            compassController.setRotation(azimuth);
//        }
//    }
}
