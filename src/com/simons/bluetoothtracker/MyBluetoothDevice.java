package com.simons.bluetoothtracker;

import java.util.ArrayList;

import android.bluetooth.BluetoothDevice;

public class MyBluetoothDevice {

    private BluetoothDevice blueToothDevice;

    private ArrayList<RSSIValue> rssiValues;
    // What was the round this devive was last updated/found?
    private int updatedRound;

    public MyBluetoothDevice(BluetoothDevice btd, int rssi) {
	super();
	rssiValues = new ArrayList<RSSIValue>();
	rssiValues.add(new RSSIValue(rssi));
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
	    return rssiValues.get(rssiValues.size() - 1).getValue();
	} else
	    return Integer.MIN_VALUE;
    }

    public ArrayList<Float> getTimeDeltas() {
	ArrayList<Float> timeDeltas = new ArrayList<Float>();
	for (int i = 0; i < rssiValues.size() - 1; i++) {

	    long timeOne = rssiValues.get(i).getTime();
	    long timeTwo = rssiValues.get(i + 1).getTime();

	    timeDeltas.add((timeTwo - timeOne) / 1000000F);
	}
	return timeDeltas;
    }

    public String toString() {
	String toString = "MyBlueToothDevice\nName: " + blueToothDevice.getName() + "\nAddress:" + blueToothDevice.getAddress() + "\nRSSI Values:";
	toString += Utilities.arrayListToString(rssiValues);
	toString += "\nTime Deltas:";
	toString += Utilities.arrayListToString(getTimeDeltas());
	toString += toString += "]";
	return toString;
    }

    public void addRSSI(int rssi) {
	rssiValues.add(new RSSIValue(rssi));
    }
}
