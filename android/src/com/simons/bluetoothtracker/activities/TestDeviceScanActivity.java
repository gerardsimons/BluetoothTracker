package com.simons.bluetoothtracker.activities;

import android.app.ListActivity;
import android.os.Bundle;

import com.simons.bluetoothtracker.R;
import com.simons.bluetoothtracker.controllers.BleDevicesAdapter;

public class TestDeviceScanActivity extends ListActivity {

    private BleDevicesAdapter mLeDevicesAdapter;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.device_scan_test);
    }

    public void onResume() {
        super.onResume();
        mLeDevicesAdapter = new BleDevicesAdapter(this);
        setListAdapter(mLeDevicesAdapter);


        //Populate listadapter with random data
        int devices = 10;
        for(int i = 0 ; i < devices ; i++) {
            int rssi = (int)Math.round(Math.random() * -100);
            rssi = (int)((float)i / devices * -130F);
            rssi -= 30;

            mLeDevicesAdapter.addDevice("rssi = " + rssi, "address " + i,rssi);
        }
    }

}
