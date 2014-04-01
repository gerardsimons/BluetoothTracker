package com.simons.bluetoothtracker;

import android.app.Application;
import android.content.SharedPreferences;
import android.util.Log;

public class BluetoothTrackerApplication extends Application {

    public static final String CALIBRATION_LIMIT_KEY = "CALIBRATION_LIMIT";
    public static final String FRAGMENTS_NUMBER_KEY = "FRAGMENTS_NUMBER";
    public static final String MAX_VALUES_SIZE_KEY = "MAX_VALUES_SIZE";
    public static final String BT_REFRESH_RATE_KEY = "BT_REFRESH_RATE";

    //CompassView Settings
    public static final String SHOW_POINTER_KEY = "SHOW_POINTER";
    public static final String POINTER_WIDTH_KEY = "POINTER_WIDTH";
    public static final String SHOW_COLORED_COMPASS_KEY = "SHOW_COLORED_COMPASS";
    public static final String SHOW_DEBUG_TEXT_KEY = "SHOW_DEBUG_TEXT";

    public static final int CALIBRATION_DEFAULT = 20;
    public static final int FRAGMENTS_DEFAULT = 12;
    public static final int MAX_VALUES_DEFAULT = 10;
    public static final int BT_REFRESH_RATE_DEFAULT = 100;

    public static final boolean SHOW_POINTER_DEFAULT = false;
    public static final int POINTER_WIDTH_DEFAULT = 90;
    public static final boolean SHOW_COLORED_COMPASS_DEFAULT = true;
    public static final boolean SHOW_DEBUG_TEXT_DEFAULT = false;

    private static final String TAG = "BluetoothtrackerApplication";

    //Enables additional functionality and shows corresponding UI
    private boolean developerMode = true;

    private SharedPreferences preferences;

    public void onCreate() {
        super.onCreate();
        preferences = getSharedPreferences("com.simons.bluetoothtracker", 0);
    }

    public void storeIntValue(String key, int value) {
//        Log.d(TAG, "Storing " + key + " = " + value);
        preferences.edit().putInt(key, value).commit();
    }

    public int loadIntValue(String key) {
        if (key.equals(CALIBRATION_LIMIT_KEY)) {
            return preferences.getInt(key, CALIBRATION_DEFAULT);
        } else if (key.equals(FRAGMENTS_NUMBER_KEY)) {
            return preferences.getInt(key, FRAGMENTS_DEFAULT);
        } else if (key.equals(MAX_VALUES_SIZE_KEY)) {
            return preferences.getInt(key,MAX_VALUES_DEFAULT);
        } else if (key.equals(BT_REFRESH_RATE_KEY)){
            return preferences.getInt(key,BT_REFRESH_RATE_DEFAULT);
        } else if (key.equals(POINTER_WIDTH_KEY)) {
            return preferences.getInt(key,POINTER_WIDTH_DEFAULT);
        } else {
            Log.e(TAG,"Unrecognized preferences key used.");
            return preferences.getInt(key, 0);
        }
    }

    public void storeBooleanValue(String key, boolean newValue) {
        Log.d(TAG, "Storing " + key + " = " + newValue);
        preferences.edit().putBoolean(key, newValue).commit();
    }

    private boolean loadBooleanValue(String key) {
        if (key.equals(SHOW_POINTER_KEY)) {
            return preferences.getBoolean(key,SHOW_POINTER_DEFAULT);
        } else if (key.equals(SHOW_COLORED_COMPASS_KEY)) {
            return preferences.getBoolean(key,SHOW_COLORED_COMPASS_DEFAULT);
        } else if (key.equals(SHOW_DEBUG_TEXT_KEY)) {
            return preferences.getBoolean(key,SHOW_DEBUG_TEXT_DEFAULT);
        } else {
            Log.e(TAG,"Unrecognized preferences key used.");
            return preferences.getBoolean(key,false);
        }
    }

    public CompassSettings loadCompassSettings() {
        boolean showColors = loadBooleanValue(SHOW_COLORED_COMPASS_KEY);
        boolean showPointer = loadBooleanValue(SHOW_POINTER_KEY);
        boolean showDebugText = loadBooleanValue(SHOW_DEBUG_TEXT_KEY);

        int nrOfFragments = loadIntValue(FRAGMENTS_NUMBER_KEY);
        int calibrationLimit = loadIntValue(CALIBRATION_LIMIT_KEY);
        int maxValues = loadIntValue(MAX_VALUES_SIZE_KEY);
        int pointerWidth = loadIntValue(POINTER_WIDTH_KEY);

        return new CompassSettings(showColors,showPointer,showDebugText,nrOfFragments,calibrationLimit,maxValues,pointerWidth);
    }

    public boolean getDeveloperMode() {
        return developerMode;
    }

    public void storeCalibrationLimit(int calibrationLimit) {
        storeIntValue(CALIBRATION_LIMIT_KEY, calibrationLimit);
    }

    public void storeFragmentsNumber(int fragmentsNumber) {
        storeIntValue(FRAGMENTS_NUMBER_KEY, fragmentsNumber);
    }
}
