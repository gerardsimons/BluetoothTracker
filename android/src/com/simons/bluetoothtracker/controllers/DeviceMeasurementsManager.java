package com.simons.bluetoothtracker.controllers;

import java.util.ArrayList;
import java.util.List;

import android.util.Log;

public class DeviceMeasurementsManager {

    private static final String TAG = DeviceMeasurementsManager.class.getSimpleName();
    public static final float MOTION_THRESHOLD = 5F;

    //Last time of a measurement
    private long lastNanoTime;

    private ArrayList<Integer> rssiValues;
    private ArrayList<Float> rssiAverages;
    private ArrayList<Float> timeDeltas;
    private ArrayList<Float> rssiDeltas;
    private ArrayList<Float> motionDistances;

    private int measurementRound = 0;

    private static final int rssiAvgFilterSize = 10;
    private static final int motionAvgFilterSize = 10;

    float[] previousMotionValues;

    public DeviceMeasurementsManager() {
        rssiDeltas = new ArrayList<Float>();
        rssiValues = new ArrayList<Integer>();
        timeDeltas = new ArrayList<Float>();
        motionDistances = new ArrayList<Float>();
        rssiAverages = new ArrayList<Float>();

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
        if (rssiAverages.size() >= 2 && measurementRound % rssiAvgFilterSize == 0) {
            rssiAverages = (ArrayList<Float>) AverageListOf(ConvertToListOfFloats(rssiValues), rssiAvgFilterSize);
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
        List<Float> lastDistances = (List<Float>) getLastValues(motionDistances, motionAvgFilterSize);
        float averageMotion = Average(lastDistances, 0, lastDistances.size() - 1);
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

    @SuppressWarnings("unchecked")
    public List<Float> getLastMotionDistances(int amount) {
        return (List<Float>) getLastValues(motionDistances, amount);
    }

    public List<Float> getLastAverageDistances(int amount) {
        List<Float> lastDistances = getLastMotionDistances(amount * motionAvgFilterSize);
        return AverageListOf(lastDistances, motionAvgFilterSize);
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
        return AverageListOf(ConvertToListOfFloats(rssiValues), rssiAvgFilterSize);
    }

    public List<Float> getLatestOfAverageRSSIValues(int amount) {
        return (List<Float>) getLastValues(getAverageOfRSSIValues(), amount);
    }

    public static List<Float> ConvertToListOfFloats(List<Integer> values) {
        List<Float> convertedList = new ArrayList<Float>(values.size());
        for (int i : values) {
            convertedList.add(Float.valueOf(i));
        }
        return convertedList;
    }

    public static List<Float> AverageListOf(List<Float> values, int filterSize) {
        int fitsTimes = (int) Math.floor(values.size() / (float) filterSize);
        //	Log.d(TAG, "fitsTimes = " + fitsTimes);
        List<Float> averages = new ArrayList<Float>(fitsTimes);
        for (int i = 0; i < fitsTimes; i++) {
            float sum = 0;
            //Log.d(TAG, "i=" + i);

            for (int j = filterSize * i; j < filterSize * (i + 1); j++) {
                //		Log.d(TAG, "j=" + j);
                sum += values.get(j);
            }
            //Log.d(TAG, "sum=" + sum);
            averages.add(sum / filterSize);
        }
        return averages;
    }

    public static float Average(List<Float> values) {
        return DeviceMeasurementsManager.Average(values, 0, values.size() - 1);
    }

    public static float Average(List<Float> values, int start, int end) {
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
