package com.simons.bluetoothtracker;

import java.util.ArrayList;
import java.util.List;

import android.util.Log;

public class DeviceMeasurmentsManager {

    private static final String TAG = DeviceMeasurmentsManager.class.getSimpleName();

    //Last time of a measurement
    private long lastNanoTime;

    private ArrayList<Integer> rssiValues;
    private ArrayList<Float> rssiAverages;
    private ArrayList<Float> timeDeltas;
    private ArrayList<Float> rssiDeltas;

    private int measurementRound = 0;

    private int averageFilterSize = 10;

    public DeviceMeasurmentsManager() {
	rssiDeltas = new ArrayList<Float>();
	rssiValues = new ArrayList<Integer>();
	timeDeltas = new ArrayList<Float>();

	//this.averageFilterSize = averageFilterSize;

	lastNanoTime = System.nanoTime();
	rssiDeltas.add(0F);
    }

    public void addMeasurement(int rssi) {
	//Get the time
	long time = System.nanoTime();
	//Compute elapsed time
	float timeDelta = (time - lastNanoTime) / 1000000F;
	//Store the values
	timeDeltas.add(timeDelta);
	rssiValues.add(rssi);
	//Compute the new rssiAverages, should not have to recompute everything all the time
	rssiAverages = (ArrayList<Float>) averageOf(rssiValues, averageFilterSize);
	//Only update the averages when we have enough averages to compare and if a new average segment has been reached
	if (rssiAverages.size() >= 2 && measurementRound % averageFilterSize == 0) {
	    rssiDeltas.add(rssiAverages.get(rssiAverages.size() - 1) - rssiAverages.get(0));
	}
	measurementRound++;
	lastNanoTime = time;
    }

    @SuppressWarnings("unchecked")
    public List<Integer> getLastRssiValues(int amount) {
	return (List<Integer>) getLastValues(rssiValues, amount);
    }

    @SuppressWarnings("unchecked")
    public List<Float> getLastTimeDeltas(int amount) {
	return (List<Float>) getLastValues(timeDeltas, amount);
    }

    @SuppressWarnings("unchecked")
    public List<Float> getLastRssiDeltas(int amount) {
	return (List<Float>) getLastValues(rssiDeltas, amount);
    }

    private List<?> getLastValues(List<?> values, int amount) {
	amount = Math.min(values.size(), amount);

	if (values == null || values.isEmpty()) {
	    return new ArrayList<Object>(0);
	}
	int start = Math.max(values.size() - amount, 0);
	int end = Math.min(values.size() + start, amount + start);

	return values.subList(start, end);
    }

    public List<Float> getAverageOfRSSIValues() {
	return averageOf(rssiValues, averageFilterSize);
    }

    public List<Float> getLatestOfAverageRSSIValues(int amount) {
	return (List<Float>) getLastValues(getAverageOfRSSIValues(), amount);
    }

    private static List<Float> averageOf(List<Integer> values, int filterSize) {
	int fitsTimes = (int) Math.floor(values.size() / (float) filterSize);
	//	Log.d(TAG, "fitsTimes = " + fitsTimes);
	List<Float> averages = new ArrayList<Float>(fitsTimes);
	for (int i = 0; i < fitsTimes; i++) {
	    int sum = 0;
	    //Log.d(TAG, "i=" + i);
	    for (int j = filterSize * i; j < filterSize * (i + 1); j++) {
		//		Log.d(TAG, "j=" + j);
		sum += values.get(j);
	    }
	    averages.add(sum / (float) filterSize);
	}
	return averages;
    }

    public ArrayList<Integer> getRssiValues() {
	return rssiValues;
    }

    public ArrayList<Float> getTimeDeltas() {
	return timeDeltas;
    }

    public ArrayList<Float> getRssiDeltas() {
	return rssiDeltas;
    }
}
