package com.devriesdev.whereatassettracking.system;

import android.bluetooth.BluetoothAdapter;
import android.util.Log;

import com.devriesdev.whereatassettracking.utils.Utils;

/**
 * Created by danie_000 on 6/15/2014.
 */
public class BluetoothExecutable implements Utils.Executable {
    private static final String TAG = "BluetoothExecutable";

    private BluetoothAdapter bluetoothAdapter;
    private Callback callback;

    public BluetoothExecutable(Callback callback) {
        this.callback = callback;
    }


    @Override
    public void execute() {
        Log.w(TAG, "Executing bluetooth executable");
        // Fix the Bluetooth
        bluetoothAdapter = BluetoothAdapter.getDefaultAdapter();
        if (bluetoothAdapter == null) {
            // Device does not support Bluetooth
            Log.w(TAG, "no bluetooth adapter found... that sucks");
        }
        while (!bluetoothAdapter.isEnabled()) {
            Log.w(TAG, "bluetooth isn't enabled yet");
            try {
                Log.w(TAG, "enabling bluetooth, going to sleep...");
                bluetoothAdapter.enable();
                Thread.currentThread().sleep(500);
            } catch (InterruptedException e) {
                e.printStackTrace();
            }
        }

        Log.w(TAG, "bluetooth was enabled");


    }

    @Override
    public void postExecute() {
        Log.w(TAG, "bluetooth execuatble is at postExecute");
        callback.call(bluetoothAdapter);
    }
}
