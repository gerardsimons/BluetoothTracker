package com.simons.bluetoothtracker;

import android.app.Application;
import android.content.SharedPreferences;
import android.util.Log;

public class BluetoothTrackerApplication extends Application {

    public static final String CALIBRATION_LIMIT_KEY = "CALIBRATION_LIMIT";
    public static final String FRAGMENTS_NUMBER_KEY = "FRAGMENTS_NUMBER";

    public static final int CALIBRATION_DEFAULT = 20;
    public static final int FRAGMENTS_DEFAULT = 12;
    private static final String TAG = BluetoothTrackerApplication.class.getSimpleName();

    private SharedPreferences preferences;

    public void onCreate() {
	super.onCreate();
	preferences = getSharedPreferences("com.simons.bluetoothtracker", 0);
    }

    public int loadCalibrationLimit() {
	int cal = preferences.getInt(CALIBRATION_LIMIT_KEY, 20);
	Log.d(TAG, "Calibration limit loaded = " + cal);
	return cal;
    }

    public int loadFragmentsNumber() {
	int fragments = preferences.getInt(FRAGMENTS_NUMBER_KEY, 12);
	Log.d(TAG, "Fragments number loaded =" + fragments);
	return fragments;
    }

    public void storeIntValue(String key, int value) {
	Log.d(TAG, "Storing " + key + " = " + value);
	preferences.edit().putInt(key, value).commit();
    }

    public int loadIntValue(String key) {
	if (key.equals(CALIBRATION_LIMIT_KEY)) {
	    return preferences.getInt(CALIBRATION_LIMIT_KEY, CALIBRATION_DEFAULT);
	} else if (key.equals(FRAGMENTS_NUMBER_KEY)) {
	    return preferences.getInt(FRAGMENTS_NUMBER_KEY, FRAGMENTS_DEFAULT);
	} else {
	    return preferences.getInt(key, 0);
	}
    }

    public void storeCalibrationLimit(int calibrationLimit) {
	storeIntValue(CALIBRATION_LIMIT_KEY, calibrationLimit);
    }

    public void storeFragmentsNumber(int fragmentsNumber) {
	storeIntValue(FRAGMENTS_NUMBER_KEY, fragmentsNumber);
    }
}
