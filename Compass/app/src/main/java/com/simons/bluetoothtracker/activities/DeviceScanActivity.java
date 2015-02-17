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
import android.app.ListActivity;
import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.BroadcastReceiver;
import android.content.ComponentName;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.ServiceConnection;
import android.content.pm.PackageManager;
import android.os.Bundle;
import android.os.IBinder;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.ListView;
import android.widget.Toast;

import com.simons.bluetoothtracker.BluetoothTrackerApplication;
import com.simons.bluetoothtracker.R;
import com.simons.bluetoothtracker.controllers.BleDevicesAdapter;
import com.simons.bluetoothtracker.models.MyBluetoothDevice;
import com.simons.bluetoothtracker.services.BluetoothLeDiscoveryService;

/**
 * Activity for scanning and displaying available Bluetooth LE devices.
 */
public class DeviceScanActivity extends ListActivity {

    private static final String TAG = "DeviceScanActivity";

    private BleDevicesAdapter mLeDevicesAdapter;
    private BluetoothTrackerApplication application;

    private static final int REQUEST_ENABLE_BT = 1;

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

                if(application.macAddressIsAuthorized(address)) {
                    mLeDevicesAdapter.addDevice(name,address,rssi,application.productTypeForMacAddress(address));
                    mLeDevicesAdapter.notifyDataSetChanged();
                }
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
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        Log.d(TAG,"DeviceScanActivity created.");

        application = (BluetoothTrackerApplication) getApplication();

        setContentView(R.layout.device_scan);

        Intent serviceIntent = new Intent(this,BluetoothLeDiscoveryService.class);
        bindService(serviceIntent,mServiceConnection,BIND_AUTO_CREATE);

        // Use this check to determine whether BLE is supported on the device.  Then you can
        // selectively disable BLE-related features.
        if (!getPackageManager().hasSystemFeature(PackageManager.FEATURE_BLUETOOTH_LE)) {
            Toast.makeText(this, R.string.ble_not_supported, Toast.LENGTH_SHORT).show();
            finish();
        }
    }

    public void createNotification() {

        Log.d(TAG,"Creating notification.");
        // Prepare intent which is triggered if the
        // notification is selected
        Intent intent = new Intent(this, DeviceScanActivity.class);
        PendingIntent pIntent = PendingIntent.getActivity(this, 0, intent, 0);

        // Build notification
        // Actions are just fake
        Notification noti = new Notification.Builder(this)
                .setContentTitle("New mail from " + "test@gmail.com")
                .setContentText("Subject").setSmallIcon(R.drawable.ic_launcher)
                .setContentIntent(pIntent)
                .addAction(R.drawable.ic_launcher, "And more", pIntent).build();
        NotificationManager notificationManager = (NotificationManager) getSystemService(NOTIFICATION_SERVICE);
        // hide the notification after its selected
        noti.flags |= Notification.FLAG_AUTO_CANCEL;

        notificationManager.notify(0, noti);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.main, menu);
        if (!mScanning) {
            menu.findItem(R.id.menu_stop).setVisible(false);
            menu.findItem(R.id.menu_scan).setVisible(true);
            menu.findItem(R.id.menu_refresh).setActionView(null);
        } else {
            menu.findItem(R.id.menu_stop).setVisible(true);
            menu.findItem(R.id.menu_scan).setVisible(false);
            menu.findItem(R.id.menu_refresh).setActionView(R.layout.actionbar_indeterminate_progress);
        }
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()) {
            case R.id.menu_scan:
                mLeDevicesAdapter.clear();
                discoveryService.startScanning();
                break;
            case R.id.menu_stop:
                discoveryService.stopScanning();
                break;
            case R.id.menu_settings:
                Intent intent = new Intent(this,SettingsActivity.class);
                startActivity(intent);
                break;
        }
        return true;
    }

    @Override
    protected void onResume() {
        super.onResume();

        registerReceiver(receiver,new IntentFilter(BluetoothLeDiscoveryService.ACTION_NAME));

        // Initializes list view adapter.
        mLeDevicesAdapter = new BleDevicesAdapter(this);
        setListAdapter(mLeDevicesAdapter);

    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        // User chose not to enable Bluetooth.
        if (requestCode == REQUEST_ENABLE_BT && resultCode == Activity.RESULT_CANCELED) {
            finish();
            return;
        }
        super.onActivityResult(requestCode, resultCode, data);
    }

    @Override
    protected void onPause() {
        super.onPause();

        createNotification();
        mLeDevicesAdapter.clear();

        unregisterReceiver(receiver);
    }

    @Override
    protected void onListItemClick(ListView l, View v, int position, long id) {
        final MyBluetoothDevice device = mLeDevicesAdapter.getDevice(position);
        if (device == null)
            return;
        final Intent intent = new Intent(this, CompassActivity.class);
        intent.putExtra(CompassActivity.EXTRAS_DEVICE_NAME, device.getName());
        intent.putExtra(CompassActivity.EXTRAS_DEVICE_ADDRESS, device.getAddress());
        if (mScanning) {
            mScanning = false;
        }
        discoveryService.stopScanning();
        startActivity(intent);
    }


}