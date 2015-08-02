package com.simons.bletracker.services;

import android.app.Service;
import android.bluetooth.BluetoothAdapter;
import android.bluetooth.BluetoothDevice;
import android.bluetooth.BluetoothManager;
import android.content.Context;
import android.content.Intent;
import android.os.Binder;
import android.os.Handler;
import android.os.IBinder;
import android.util.Log;

public class BLEDiscoveryService extends Service {

    private static final String TAG = BLEDiscoveryService.class.getSimpleName();

    private BluetoothAdapter mBluetoothAdapter;
    private boolean mScanning;
    private Handler mHandler;
    private BluetoothManager btManager;

    public static final String DEVICE_NAME = "DEVICE_NAME";
    public static final String DEVICE_ADDRESS = "DEVICE_ADDRESS";
    public static final String DEVICE_RSSI = "DEVICE_RSSI";
    public static final String DEVICE_THRESHOLD = "DEVICE_THRESHOLD";

    public static final String ACTION_NAME = "com.simons.bletracker.discovery";

    private static final int RSSI_THRESHOLD = -80;

    //Scanning interval
    private static final long SCAN_PERIOD = 3000;

    private final IBinder mBinder = new LocalBinder();

    public class LocalBinder extends Binder {
        public BLEDiscoveryService getService() {
            return BLEDiscoveryService.this;
        }
    }

    @Override
    public boolean onUnbind(Intent intent) {
        // After using a given device, you should make sure that BluetoothGatt.close() is called
        // such that resources are cleaned up properly.  In this particular example, close() is
        // invoked when the UI is disconnected from the Service.
        return super.onUnbind(intent);
    }

    @Override
    public void onDestroy() {
        stopScanning();
    }

    public void startScanning() {
        scannerThread.resumeScanning();
    }

    public void stopScanning() {
        scannerThread.pauseScanning();
    }

    public IBinder onBind(Intent intent) {
        return mBinder;
    }

    /**
     * Initializes a reference to the local Bluetooth adapter.
     *
     * @return Return true if the initialization is successful.
     */
    public boolean initialize() {
        // For API level 18 and above, get a reference to BluetoothAdapter through
        // BluetoothManager.
        if (btManager == null) {
            btManager = (BluetoothManager) getSystemService(Context.BLUETOOTH_SERVICE);
            if (btManager == null) {
                Log.e(TAG, "Unable to initialize BluetoothManager.");
                return false;
            }
        }
        mBluetoothAdapter = btManager.getAdapter();
        if (mBluetoothAdapter == null) {
            Log.e(TAG, "Unable to obtain a BluetoothAdapter.");
            return false;
        }
        Log.d(TAG,"BluetoothDiscoveryService succesfully initialised.");
        return true;
    }

    private BluetoothDiscoverer scannerThread = new BluetoothDiscoverer();

    class BluetoothDiscoverer extends Thread {
        private volatile boolean running = true;

        public BluetoothDiscoverer() {
            super();
        }

        public void pauseScanning() {
            if(running) {
                running = false;
                mBluetoothAdapter.stopLeScan(mLeScanCallback);
            }
        }

        public void resumeScanning() {
            synchronized (this) {
                Log.d(TAG, "Starting discovery.");
                running = true;
                start();
            }
        }

        @Override
        public void run() {
            try {
                while (true) {
                    if (running) {
                        Log.d(TAG,"Starting scan...");
                        mBluetoothAdapter.startLeScan(mLeScanCallback);
                        sleep(SCAN_PERIOD);
                        mBluetoothAdapter.stopLeScan(mLeScanCallback);
                    }
                    else sleep(SCAN_PERIOD);
                }
            } catch (InterruptedException e) {
                e.printStackTrace();
            }
        }
    };

    // Device scan callback.
    private BluetoothAdapter.LeScanCallback mLeScanCallback = new BluetoothAdapter.LeScanCallback() {
        @Override
        public void onLeScan(final BluetoothDevice device, final int rssi, byte[] scanRecord) {
            Log.d(TAG,"Device discovered " + device.getName());

            Intent intent = new Intent();
            intent.setAction(ACTION_NAME);

            intent.putExtra(DEVICE_NAME, device.getName());
            intent.putExtra(DEVICE_ADDRESS, device.getAddress());
            intent.putExtra(DEVICE_RSSI, rssi);

            if(rssi < RSSI_THRESHOLD) {
                intent.putExtra(DEVICE_THRESHOLD,true);
            }
            intent.putExtra(DEVICE_THRESHOLD,false);

            sendBroadcast(intent);
        }
    };
}
