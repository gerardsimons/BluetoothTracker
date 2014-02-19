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

package com.simons.bluetoothtracker.controllers;

import android.app.Service;
import android.bluetooth.BluetoothAdapter;
import android.bluetooth.BluetoothDevice;
import android.bluetooth.BluetoothGatt;
import android.bluetooth.BluetoothGattCallback;
import android.bluetooth.BluetoothManager;
import android.bluetooth.BluetoothProfile;
import android.content.Context;
import android.content.Intent;
import android.os.Binder;
import android.os.IBinder;
import android.util.Log;

/**
 * Service for managing connection and data communication with a GATT server
 * hosted on a given Bluetooth LE device.
 */
public class BluetoothLeService extends Service {
    private final static String TAG = BluetoothLeService.class.getSimpleName();

    private BluetoothManager mBluetoothManager;
    private BluetoothAdapter mBluetoothAdapter;
    private String mBluetoothDeviceAddress;
    private BluetoothGatt mBluetoothGatt;
    private int mConnectionState = STATE_DISCONNECTED;

    private static final int STATE_DISCONNECTED = 0;
    private static final int STATE_CONNECTING = 1;
    private static final int STATE_CONNECTED = 2;

    public final static String ACTION_GATT_CONNECTED = "com.simons.bluetoothtracker.ACTION_GATT_CONNECTED";
    public final static String ACTION_GATT_DISCONNECTED = "com.simons.bluetoothtracker.ACTION_GATT_DISCONNECTED";
    public final static String ACTION_RSSI_VALUE_READ = "com.simons.bluetoothtracker.ACTION_RSSI_VALUE_READ";

    public static final String RSSI_VALUE_KEY = "RSSI_VALUE_KEY";

    private int refreshRate;
    private RSSIReader rssiReader;

    // Implements callback methods for GATT events that the app cares about.  For example,
    // connection change and services discovered.
    private final BluetoothGattCallback mGattCallback = new BluetoothGattCallback() {
        @Override
        public void onConnectionStateChange(BluetoothGatt gatt, int status, int newState) {
            String intentAction;
            if (newState == BluetoothProfile.STATE_CONNECTED) {
                intentAction = ACTION_GATT_CONNECTED;
                mConnectionState = STATE_CONNECTED;
                broadcastUpdate(intentAction);
                Log.i(TAG, "Connected to GATT server.");

                startReading(refreshRate);

                //Log.i(TAG, "Attempting to read RSSI:" + mBluetoothGatt.readRemoteRssi());
            } else if (newState == BluetoothProfile.STATE_DISCONNECTED) {
                intentAction = ACTION_GATT_DISCONNECTED;
                mConnectionState = STATE_DISCONNECTED;
                Log.i(TAG, "Disconnected from GATT server.");
                rssiReader.stopReading();
                mBluetoothGatt.connect();
                broadcastUpdate(intentAction);
            }
        }

        @Override
        public void onReadRemoteRssi(BluetoothGatt gatt, int rssi, int status) {
            broadcastUpdate(ACTION_RSSI_VALUE_READ, rssi);
        }

    };

    private void broadcastUpdate(final String action) {
        final Intent intent = new Intent(action);
        //Log.i(TAG, "Broadcast Update : " + action);
        sendBroadcast(intent);
    }

    private void broadcastUpdate(final String action, int rssiExtra) {
        final Intent intent = new Intent(action);
        intent.putExtra(RSSI_VALUE_KEY, rssiExtra);
        //Log.i(TAG, "Broadcast Update RSSI: " + rssiExtra);
        sendBroadcast(intent);
    }

    public class LocalBinder extends Binder {
        public BluetoothLeService getService() {
            return BluetoothLeService.this;
        }
    }

    @Override
    public IBinder onBind(Intent intent) {
        return mBinder;
    }

    public void restartBluetooth() {
        mBluetoothAdapter.disable();
        mBluetoothAdapter.enable();
    }

    @Override
    public boolean onUnbind(Intent intent) {
        // After using a given device, you should make sure that BluetoothGatt.close() is called
        // such that resources are cleaned up properly.  In this particular example, close() is
        // invoked when the UI is disconnected from the Service.
        close();
        return super.onUnbind(intent);
    }

    private final IBinder mBinder = new LocalBinder();

    /**
     * Initializes a reference to the local Bluetooth adapter.
     *
     * @return Return true if the initialization is successful.
     */
    public boolean initialize() {
        // For API level 18 and above, get a reference to BluetoothAdapter through
        // BluetoothManager.
        if (mBluetoothManager == null) {
            mBluetoothManager = (BluetoothManager) getSystemService(Context.BLUETOOTH_SERVICE);
            if (mBluetoothManager == null) {
                Log.e(TAG, "Unable to initialize BluetoothManager.");
                return false;
            }
        }
        mBluetoothAdapter = mBluetoothManager.getAdapter();
        if (mBluetoothAdapter == null) {
            Log.e(TAG, "Unable to obtain a BluetoothAdapter.");
            return false;
        }
        return true;
    }

    /**
     * Connects to the GATT server hosted on the Bluetooth LE device.
     *
     * @param address The device address of the destination device.
     * @return Return true if the connection is initiated successfully. The
     * connection result is reported asynchronously through the
     * {@code BluetoothGattCallback#onConnectionStateChange(android.bluetooth.BluetoothGatt, int, int)}
     * callback.
     */
    public boolean connect(final String address, int refreshRate) {
        Log.d(TAG,"Trying to connect to " + address);
        this.refreshRate = refreshRate;
        if (mBluetoothAdapter == null || address == null) {
            Log.w(TAG, "BluetoothAdapter not initialized or unspecified address.");
            return false;
        }

//        Previously connected device.  Try to reconnect.
        if (mBluetoothDeviceAddress != null && address.equals(mBluetoothDeviceAddress) && mBluetoothGatt != null) {
            Log.d(TAG, "Trying to use an existing mBluetoothGatt for connection.");
            if (mBluetoothGatt.connect()) {
                mConnectionState = STATE_CONNECTING;
                return true;
            } else {
                return false;
            }
        }

        final BluetoothDevice device = mBluetoothAdapter.getRemoteDevice(address);
        if (device == null) {
            Log.w(TAG, "Device not found.  Unable to connect.");
            return false;
        }
        // We want to directly connect to the device, so we are setting the autoConnect
        // parameter to false.
        mBluetoothGatt = device.connectGatt(this, true, mGattCallback);
        Log.d(TAG, "Trying to create a new connection.");
        mBluetoothDeviceAddress = address;
        mConnectionState = STATE_CONNECTING;
        return true;
    }

    public void startReading(int refreshRate) {
        Log.d(TAG,"Start RSSI reading using refresh rate = " + refreshRate);
        rssiReader = new RSSIReader(mBluetoothGatt, refreshRate);
        if (!rssiReader.isReading()) {
            rssiReader.startReading();
        }
    }

    public void stopReading() {
        if (rssiReader != null && rssiReader.isReading()) {
            rssiReader.stopReading();
        }
    }

    /**
     * Disconnects an existing connection or cancel a pending connection. The
     * disconnection result is reported asynchronously through the
     * {@code BluetoothGattCallback#onConnectionStateChange(android.bluetooth.BluetoothGatt, int, int)}
     * callback.
     */
    public void disconnect() {
        if (mBluetoothAdapter == null || mBluetoothGatt == null) {
            Log.w(TAG, "BluetoothAdapter not initialized");
            return;
        }
        stopReading();
        mBluetoothGatt.disconnect();
    }

    /**
     * After using a given BLE device, the app must call this method to ensure
     * resources are released properly.
     */
    public void close() {
        if (mBluetoothGatt == null) {
            return;
        }
        mBluetoothGatt.close();
        //Stop the RSSI Reader
        rssiReader = null;
        mBluetoothGatt = null;
    }

    private static class RSSIReader extends Thread {

        private volatile boolean isReading = false;
        private int refreshRate;
        private BluetoothGatt mBluetoothGatt;

        public RSSIReader(BluetoothGatt mBluetoothGatt, int refreshRate) {
            this.refreshRate = refreshRate;
            this.mBluetoothGatt = mBluetoothGatt;
        }

        @Override
        public void run() {
            while (true) {
                synchronized (this) {
                    try {
                        sleep(refreshRate);
                        if (!isReading) {
                            Log.i(TAG, "Stopping RSSI Reading.");
                            break;
                        } else {
                            //Log.i(TAG, "Initiating RSSI Read.");
                            if (mBluetoothGatt != null)
                                mBluetoothGatt.readRemoteRssi();
                            else
                                break;
                        }
                    } catch (InterruptedException e) {
                        e.printStackTrace();
                    }
                }
            }
        }

        public boolean isReading() {
            return isReading;
        }

        public void startReading() {
            synchronized (this) {
                Log.d(TAG, "Starting RSSI reading.");
                isReading = true;
                start();
            }
        }

        public void stopReading() {
            isReading = false;
        }
    }

    ;
}