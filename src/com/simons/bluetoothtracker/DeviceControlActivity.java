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
import android.os.Bundle;
import android.os.IBinder;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import com.jjoe64.graphview.GraphView;
import com.jjoe64.graphview.GraphView.GraphViewData;
import com.jjoe64.graphview.GraphViewSeries;
import com.jjoe64.graphview.LineGraphView;
import com.simons.bluetoothtracker.views.CompassView;

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

    private CompassView compassView;

    //    private GraphViewSeries motionSeries;
    //    private GraphViewSeries thresholdSeries;
    //    private LineGraphView graphView;

    private BluetoothLeService mBluetoothLeService;

    private SensorManager mSensorManager;

    private Sensor motionSensor;
    private Sensor magneticSensor;

    private float[] magnetic = new float[3];
    private float[] motion = new float[3];

    private float azimuth = 0f;

    //Low pass filter coefficient
    private final float alpha = 0.97f;

    private DeviceMeasurmentsManager measurementsManager;

    private boolean mConnected = false;

    //The rate in milliseconds we want to measure, this is used to keep all types of measurments roughly synchronized (RSSI, motion,...)
    private static final int MEASUREMENTS_RATE = 1000;

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
	    mBluetoothLeService.connect(mDeviceAddress);

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
		//mBluetoothLeService.startReading(MEASUREMENTS_RATE);
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
	setContentView(R.layout.device_control);

	final Intent intent = getIntent();
	mDeviceName = intent.getStringExtra(EXTRAS_DEVICE_NAME);
	mDeviceAddress = intent.getStringExtra(EXTRAS_DEVICE_ADDRESS);

	// Sets up UI references.
	((TextView) findViewById(R.id.device_address)).setText(mDeviceAddress);

	mConnectionState = (TextView) findViewById(R.id.connection_state);

	rssiValuesTextView = (TextView) findViewById(R.id.rssi_values);
	timeDeltasTextView = (TextView) findViewById(R.id.timeDeltaValues);
	rssiDeltasTextView = (TextView) findViewById(R.id.deltaRssiValues);
	compassView = (CompassView) findViewById(R.id.compassView);

	getActionBar().setTitle(mDeviceName);
	getActionBar().setDisplayHomeAsUpEnabled(true);
	Intent gattServiceIntent = new Intent(this, BluetoothLeService.class);
	bindService(gattServiceIntent, mServiceConnection, BIND_AUTO_CREATE);

	measurementsManager = new DeviceMeasurmentsManager();

	mSensorManager = (SensorManager) getSystemService(Context.SENSOR_SERVICE);
	if (mSensorManager.getDefaultSensor(Sensor.TYPE_ACCELEROMETER) != null) {
	    // Success! There's a magnetometer.
	    motionSensor = mSensorManager.getDefaultSensor(Sensor.TYPE_ACCELEROMETER);
	    magneticSensor = mSensorManager.getDefaultSensor(Sensor.TYPE_MAGNETIC_FIELD);
	} else {
	    // Failure! No magnetometer.
	    Log.e(TAG, "The device has no accelerometer.");
	    finish();
	}

	//Create graph
	//	LinearLayout graphContainer = (LinearLayout) findViewById(R.id.motionGraphContainer);
	//
	//	motionSeries = new GraphViewSeries(new GraphViewData[] {});
	//	thresholdSeries = new GraphViewSeries(new GraphViewData[] {});
	//
	//	graphView = new LineGraphView(this, "Motion Values");
	//	graphView.setDrawBackground(true);
	//	graphView.setManualYAxisBounds(10, 0);
	//	//graphView.setScalable(true);
	//	graphView.setScrollable(true);
	//	graphView.setViewPort(0, 100);
	//	graphView.setVerticalLabels(null);
	//
	//	graphView.addSeries(motionSeries);
	//	graphView.addSeries(thresholdSeries);
	//
	//	graphContainer.addView(graphView);
    }

    private void updateUI() {

	if (measurementsManager != null) {

	    String rssiValuesText = new String();
	    String timeDeltasText = new String();
	    String rssiDeltasText = new String();
	    String separator = " ";

	    //How many values do we want to show
	    int amount = 20;
	    int graphAmount = 100;

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
	    timeDeltasTextView.setText(timeDeltasText);
	    rssiDeltasTextView.setText(rssiDeltasText);

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
	mSensorManager.registerListener(this, motionSensor, MEASUREMENTS_RATE);
	mSensorManager.registerListener(this, magneticSensor, MEASUREMENTS_RATE);
    }

    @Override
    protected void onPause() {
	super.onPause();
	mBluetoothLeService.stopReading();
	unregisterReceiver(mGattUpdateReceiver);
	mSensorManager.unregisterListener(this, motionSensor);
	mSensorManager.unregisterListener(this, magneticSensor);
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

    private void adjustCompass() {
	compassView.setRotation(azimuth);
    }

    @Override
    public void onSensorChanged(SensorEvent event) {
	//for (int i = 0; i < event.values.length; i++) {
	//	Log.i(TAG, "values[" + i + "]=" + event.values[i]);
	//}
	//If already initialized
	if (measurementsManager != null) {
	    if (event.sensor.getType() == Sensor.TYPE_MAGNETIC_FIELD) {
		//		Log.d(TAG, "Magnetic Field Sensor Values : ");
		//		for (float f : event.values) {
		//		    Log.d(TAG, f + "");
		//		}
		magnetic[0] = alpha * magnetic[0] + (1 - alpha) * event.values[0];
		magnetic[1] = alpha * magnetic[1] + (1 - alpha) * event.values[1];
		magnetic[2] = alpha * magnetic[2] + (1 - alpha) * event.values[2];
	    } else if (event.sensor.getType() == Sensor.TYPE_ACCELEROMETER) {

		//		Log.d(TAG, "Accelerometer Values : ");
		//		for (float f : event.values) {
		//		    Log.d(TAG, f + "");
		//		}
		motion[0] = alpha * motion[0] + (1 - alpha) * event.values[0];
		motion[1] = alpha * motion[1] + (1 - alpha) * event.values[1];
		motion[2] = alpha * motion[2] + (1 - alpha) * event.values[2];
	    }
	    float R[] = new float[9];
	    float I[] = new float[9];
	    boolean success = SensorManager.getRotationMatrix(R, I, motion, magnetic);
	    Log.d(TAG, "getRotationMatrix returns " + success);
	    if (success) {

		float orientation[] = new float[3];
		SensorManager.getOrientation(R, orientation);
		// Log.d(TAG, "azimuth (rad): " + azimuth);
		azimuth = (float) Math.toDegrees(orientation[0]); // orientation
		azimuth = (azimuth + 360) % 360;
		Log.d(TAG, "azimuth (deg): " + azimuth);

		//Adjust the compassview
		adjustCompass();
	    }
	}
    }
}
