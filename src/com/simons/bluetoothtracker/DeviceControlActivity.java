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

package com.simons.bluetoothtracker;

import java.util.List;

import android.app.Activity;
import android.content.BroadcastReceiver;
import android.content.ComponentName;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.ServiceConnection;
import android.hardware.Sensor;
import android.hardware.SensorEvent;
import android.hardware.SensorEventListener;
import android.hardware.SensorManager;
import android.hardware.TriggerEvent;
import android.hardware.TriggerEventListener;
import android.os.Bundle;
import android.os.IBinder;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.widget.TextView;
import android.widget.Toast;

/**
 * For a given BLE device, this Activity provides the user interface to connect,
 * display data, and display GATT services and characteristics supported by the
 * device. The Activity communicates with {@code BluetoothLeService}, which in
 * turn interacts with the Bluetooth LE API.
 */
public class DeviceControlActivity extends Activity implements SensorEventListener {
    private final static String TAG = DeviceControlActivity.class.getSimpleName();

    public static final String EXTRAS_DEVICE_NAME = "DEVICE_NAME";
    public static final String EXTRAS_DEVICE_ADDRESS = "DEVICE_ADDRESS";

    private TextView mConnectionState;

    private String mDeviceName;
    private String mDeviceAddress;

    private TextView rssiValuesTextView;
    private TextView timeDeltasTextView;
    private TextView rssiDeltasTextView;

    private BluetoothLeService mBluetoothLeService;

    private SensorManager mSensorManager;
    private Sensor motionSensor;

    private DeviceMeasurmentsManager measurementsManager;

    private boolean mConnected = false;

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
	    //mBluetoothLeService.connect(mDeviceAddress);

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
		measurementsManager = new DeviceMeasurmentsManager();
		invalidateOptionsMenu();
		Toast.makeText(DeviceControlActivity.this, "Connected to GATT server", Toast.LENGTH_SHORT).show();
	    } else if (BluetoothLeService.ACTION_GATT_DISCONNECTED.equals(action)) {
		mConnected = false;
		updateConnectionState(R.string.disconnected);
		invalidateOptionsMenu();
	    } else if (BluetoothLeService.ACTION_RSSI_VALUE_READ.equals(action)) {
		int newRSSI = intent.getExtras().getInt(BluetoothLeService.RSSI_VALUE_KEY);
		updateMeasurements(newRSSI);
		Toast.makeText(DeviceControlActivity.this, "Disconnected from GATT server", Toast.LENGTH_SHORT).show();
	    }
	}
    };

    private void updateMeasurements(int rssi) {
	if (measurementsManager != null) {
	    measurementsManager.addRssiMeasurement(rssi);
	    updateUI();
	}
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
	super.onCreate(savedInstanceState);
	setContentView(R.layout.gatt_services_characteristics);

	final Intent intent = getIntent();
	mDeviceName = intent.getStringExtra(EXTRAS_DEVICE_NAME);
	mDeviceAddress = intent.getStringExtra(EXTRAS_DEVICE_ADDRESS);

	// Sets up UI references.
	((TextView) findViewById(R.id.device_address)).setText(mDeviceAddress);

	mConnectionState = (TextView) findViewById(R.id.connection_state);

	rssiValuesTextView = (TextView) findViewById(R.id.rssi_values);
	timeDeltasTextView = (TextView) findViewById(R.id.timeDeltaValues);
	rssiDeltasTextView = (TextView) findViewById(R.id.deltaRssiValues);

	getActionBar().setTitle(mDeviceName);
	getActionBar().setDisplayHomeAsUpEnabled(true);
	Intent gattServiceIntent = new Intent(this, BluetoothLeService.class);
	bindService(gattServiceIntent, mServiceConnection, BIND_AUTO_CREATE);

	measurementsManager = new DeviceMeasurmentsManager();

	mSensorManager = (SensorManager) getSystemService(Context.SENSOR_SERVICE);
	if (mSensorManager.getDefaultSensor(Sensor.TYPE_ACCELEROMETER) != null) {
	    // Success! There's a magnetometer.
	    motionSensor = mSensorManager.getDefaultSensor(Sensor.TYPE_ACCELEROMETER);
	} else {
	    // Failure! No magnetometer.
	    Log.e(TAG, "No signification motion sensor.");
	    finish();
	}
    }

    private void updateUI() {

	if (measurementsManager != null) {

	    String rssiValuesText = new String();
	    String timeDeltasText = new String();
	    String rssiDeltasText = new String();
	    String separator = " ";

	    int amount = 20;

	    List<Integer> rssiValues = measurementsManager.getLastRssiValues(amount);
	    List<Float> rssiDeltas = measurementsManager.getLastRssiDeltas(amount);
	    List<Float> timeDeltas = measurementsManager.getLastTimeDeltas(amount);

	    //Log.d(TAG, measurementsManager.getAverageOfRSSIValues(5).toString());

	    for (int i = 0; i < rssiValues.size(); i++) {
		rssiValuesText += rssiValues.get(i) + separator;
		timeDeltasText += timeDeltas.get(i) + separator;
	    }
	    for (int i = 0; i < rssiDeltas.size(); i++) {
		rssiDeltasText += Math.round(rssiDeltas.get(i)) + separator;
	    }

	    rssiValuesTextView.setText(rssiValuesText);
	    timeDeltasTextView.setText(timeDeltasText);
	    rssiDeltasTextView.setText(rssiDeltasText);
	}
    }

    @Override
    protected void onResume() {
	super.onResume();
	registerReceiver(mGattUpdateReceiver, makeGattUpdateIntentFilter());
	mSensorManager.registerListener(this, motionSensor, SensorManager.SENSOR_DELAY_NORMAL);
    }

    @Override
    protected void onPause() {
	super.onPause();
	mBluetoothLeService.stopReading();
	unregisterReceiver(mGattUpdateReceiver);
	mSensorManager.unregisterListener(this, motionSensor);
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
	    mBluetoothLeService.connect(mDeviceAddress);
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
	case R.id.restart_bluetooth:
	    mBluetoothLeService.restartBluetooth();
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

    @Override
    public void onAccuracyChanged(Sensor sensor, int accuracy) {

    }

    @Override
    public void onSensorChanged(SensorEvent event) {
	for (int i = 0; i < event.values.length; i++) {
	    //Log.i(TAG, "values[" + i + "]=" + event.values[i]);
	}
	//If already initialized
	if (measurementsManager != null) {
	    measurementsManager.addMotionMeasurements(event.values);
	    if (measurementsManager.significantMotionDetected()) {
		Log.i(TAG, "User is moving.");
		Toast.makeText(this, "Significant motion detected.", Toast.LENGTH_SHORT).show();
	    }
	}
    }

}
