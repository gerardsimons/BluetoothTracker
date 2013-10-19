package com.simons.bluetoothtracker;

import java.util.ArrayList;
import java.util.List;

import android.util.Log;

public class DeviceMeasurmentsManager {

    private static final String TAG = DeviceMeasurmentsManager.class.getSimpleName();
    private static final float MOTION_THRESHOLD = 5F;

    //Last time of a measurement
    private long lastNanoTime;

    private ArrayList<Integer> rssiValues;
    private ArrayList<Float> rssiAverages;
    private ArrayList<Float> timeDeltas;
    private ArrayList<Float> rssiDeltas;
    private ArrayList<Float> motionDistances;
    private float distanceThreshold = 1.0f;

    private int measurementRound = 0;

    private static final int averageFilterSizeRssi = 10;
    private static final int averageFilterSizeMotion = 10;

    float[] previousMotionValues;

    public DeviceMeasurmentsManager() {
	rssiDeltas = new ArrayList<Float>();
	rssiValues = new ArrayList<Integer>();
	timeDeltas = new ArrayList<Float>();
	motionDistances = new ArrayList<Float>();

	lastNanoTime = System.nanoTime();
	rssiDeltas.add(0F);
    }

    public void addRssiMeasurement(int rssi) {
	//Get the time
	long time = System.nanoTime();
	//Compute elapsed time
	float timeDelta = (time - lastNanoTime) / 1000000F;
	//Store the values
	timeDeltas.add(timeDelta);
	rssiValues.add(rssi);
	//Only update the averages when we have enough averages to compare and if a new average segment has been reached
	if (rssiAverages.size() >= 2 && measurementRound % averageFilterSizeRssi == 0) {
	    rssiAverages = (ArrayList<Float>) averageListOf(rssiValues, averageFilterSizeRssi);
	    rssiDeltas.add(rssiAverages.get(rssiAverages.size() - 1) - rssiAverages.get(0));
	}
	measurementRound++;
	lastNanoTime = time;
    }

    public void addMotionMeasurements(float[] motionValues) {

	if (previousMotionValues != null) {
	    //Subtract
	    float[] deltaMotion = new float[3];
	    for (int i = 0; i < motionValues.length; i++) {
		deltaMotion[i] = Math.abs(motionValues[i] - previousMotionValues[i]);
	    }
	    float distance = (float) Math.sqrt(deltaMotion[0] * deltaMotion[0] + deltaMotion[1] * deltaMotion[1] + deltaMotion[2] * deltaMotion[2]);
	    Log.d(TAG, "deltaMotionValues=[" + deltaMotion[0] + "," + deltaMotion[1] + "," + deltaMotion[2]);
	    Log.d(TAG, "motionValues=[" + motionValues[0] + "," + motionValues[1] + "," + motionValues[2]);
	    Log.d(TAG, "previousMotionValues=[" + previousMotionValues[0] + "," + previousMotionValues[1] + "," + previousMotionValues[2]);
	    Log.d(TAG, "Distance measured=" + distance);
	    motionDistances.add(distance);
	}
	previousMotionValues = motionValues.clone();

    }

    public boolean significantMotionDetected() {
	List<Float> lastDistances = (List<Float>) getLastValues(motionDistances, averageFilterSizeMotion);
	float averageMotion = average(lastDistances, 0, lastDistances.size() - 1);
	Log.d(TAG, "averageMotion=" + averageMotion);
	return averageMotion >= MOTION_THRESHOLD;
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
	return averageListOf(rssiValues, averageFilterSizeRssi);
    }

    public List<Float> getLatestOfAverageRSSIValues(int amount) {
	return (List<Float>) getLastValues(getAverageOfRSSIValues(), amount);
    }

    public static List<Float> averageListOf(List<Integer> values, int filterSize) {
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

    public static float average(List<Float> values) {
	return DeviceMeasurmentsManager.average(values, 0, values.size() - 1);
    }

    public static float average(List<Float> values, int start, int end) {
	float sum = 0f;
	if (values.size() - 1 >= end && start < end) {
	    for (float value : values) {
		sum += value;
	    }
	} else {
	    Log.e(TAG, "Invalid start end values for average!");
	    return Float.NaN;
	}
	Log.d(TAG, "sum=" + sum);
	return sum / (end - start);
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
