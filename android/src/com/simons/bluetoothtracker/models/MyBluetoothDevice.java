package com.simons.bluetoothtracker.models;

import android.bluetooth.BluetoothDevice;

import com.simons.bluetoothtracker.Utilities;

import java.util.ArrayList;
import java.util.List;

public class MyBluetoothDevice {

    private BluetoothDevice blueToothDevice;

    private List<Integer> rssiValues;
    // What was the round this devive was last updated/found?
    private int updatedRound;

    public MyBluetoothDevice(BluetoothDevice btd, int rssi) {
        super();
        rssiValues = new ArrayList<Integer>();
        rssiValues.add(rssi);
        blueToothDevice = btd;
    }

    public BluetoothDevice getBluetoothDevice() {
        return blueToothDevice;
    }

    public String getAddress() {
        return blueToothDevice.getAddress();
    }

    public boolean equals(Object o) {
        if (o instanceof MyBluetoothDevice) {
            MyBluetoothDevice other = (MyBluetoothDevice) o;
            if (other.getAddress().equals(getAddress())) {
                return true;
            }
        }
        return false;
    }

    public void setRoundUpdated(int updatedRound) {
        this.updatedRound = updatedRound;
    }

    public int getRoundUpdated() {
        return updatedRound;
    }

    public String getName() {
        return blueToothDevice.getName();
    }

    public int getLatestRSSI() {
        if (rssiValues != null && !rssiValues.isEmpty()) {
            return rssiValues.get(rssiValues.size() - 1);
        } else
            return Integer.MIN_VALUE;
    }

    public String toString() {
        String toString = "MyBlueToothDevice\nName: " + blueToothDevice.getName() + "\nAddress:" + blueToothDevice.getAddress() + "\nRSSI Values:";
        toString += Utilities.listToString(rssiValues);
        toString += "\nTime Deltas:";

        toString += toString += "]";
        return toString;
    }

    public void addRSSI(int rssi) {
        rssiValues.add(rssi);
    }
}
