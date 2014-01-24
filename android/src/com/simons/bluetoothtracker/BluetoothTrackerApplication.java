package com.simons.bluetoothtracker;

import android.app.Application;
import android.content.SharedPreferences;
import android.util.Log;

import com.testflightapp.lib.TestFlight;

public class BluetoothTrackerApplication extends Application {

    public static final String CALIBRATION_LIMIT_KEY = "CALIBRATION_LIMIT";
    public static final String FRAGMENTS_NUMBER_KEY = "FRAGMENTS_NUMBER";
    public static final String MAX_VALUES_SIZE_KEY = "MAX_VALUES_SIZE";
    public static final String BT_REFRESH_RATE_KEY = "BT_REFRESH_RATE";

    public static final int CALIBRATION_DEFAULT = 20;
    public static final int FRAGMENTS_DEFAULT = 12;
    public static final int MAX_VALUES_DEFAULT = 10;
    public static final int BT_REFRESH_RATE_DEFAULT = 100;

    private static final String TAG = "BluetoothtrackerApplication";

    private static final String FLIGHT_APP_TOKEN = "9f44d40a-d87d-4385-90df-8ae60ab8e02a";

    private SharedPreferences preferences;

    public void onCreate() {
        super.onCreate();
        preferences = getSharedPreferences("com.simons.bluetoothtracker", 0);

        TestFlight.takeOff(this, FLIGHT_APP_TOKEN);
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
        } else if (key.equals(MAX_VALUES_SIZE_KEY)) {
            return preferences.getInt(MAX_VALUES_SIZE_KEY,MAX_VALUES_DEFAULT);
        } else if (key.equals(BT_REFRESH_RATE_KEY)){
            Log.d(TAG,"Refresh rate key used.");
            return preferences.getInt(BT_REFRESH_RATE_KEY,BT_REFRESH_RATE_DEFAULT);
        } else {
            Log.e(TAG,"Unrecognized preferences key used.");
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
