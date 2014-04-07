package com.simons.bluetoothtracker;

import android.app.Application;
import android.content.SharedPreferences;
import android.util.Log;

import com.simons.bluetoothtracker.models.ProductType;
import com.simons.bluetoothtracker.settings.CompassSettings;
import com.simons.bluetoothtracker.settings.UserSettings;

import java.security.InvalidKeyException;
import java.util.HashMap;
import java.util.Map;

public class BluetoothTrackerApplication extends Application {

    public static final String CALIBRATION_LIMIT_KEY = "CALIBRATION_LIMIT";
    public static final String FRAGMENTS_NUMBER_KEY = "FRAGMENTS_NUMBER";
    public static final String MAX_VALUES_SIZE_KEY = "MAX_VALUES_SIZE";
    public static final String BT_REFRESH_RATE_KEY = "BT_REFRESH_RATE";

    //User details
    private static final String EMAIL_KEY = "EMAIL";
    private static final String PASS_KEY = "PASS";
    private static final String REMEMBER_ME_KEY = "REMEMBER_ME";
    private static final String STAY_LOGGED_IN = "STAY_LOGGED_IN";

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

    private final String[] macAddressesAuthorized = new String[]{
            "E3:8E:9A:75:CE:D0", //whereAt La2
            "F8:01:51:4D:1F:96",
            "F0:17:35:EF:3C:0F",
            "E6:F7:72:34:9F:79", //Where@ label
            "F0:44:BA:E7:D0:39",
            "C7:38:09:E4:A9:84",
            "FE:A1:EB:F0:80:E7",
            "C7:9E:37:9A:95:AA"};

    private Map<String,ProductType> macProductTypeMap;

    //Enables additional functionality and shows corresponding UI
    private boolean developerMode = false;

    private SharedPreferences preferences;

    public void onCreate() {
        super.onCreate();
        preferences = getSharedPreferences("com.simons.bluetoothtracker", 0);
        loadMacProductTypeFixture();
    }

    //Fixture data for mapping products to product types, ensuring each product type is used at least once.
    private void loadMacProductTypeFixture() {
        macProductTypeMap = new HashMap<String, ProductType>();
        for(String mac : macAddressesAuthorized) {
            macProductTypeMap.put(mac,ProductType.NextProductType());
        }
    }

    public void storeIntValue(String key, int value) {
//        Log.d(TAG, "Storing " + key + " = " + value);
        if (preferences != null)
            preferences.edit().putInt(key, value).commit();
        else Log.e(TAG, "Preferences not initialized.");
    }

    public void storeStringValue(String key, String value) {
        if (preferences != null)
            preferences.edit().putString(key, value).commit();
        else Log.e(TAG, "Preferences not initialized.");
    }

    public int loadIntValue(String key) throws InvalidKeyException{
        if (key.equals(CALIBRATION_LIMIT_KEY)) {
            return preferences.getInt(key, CALIBRATION_DEFAULT);
        } else if (key.equals(FRAGMENTS_NUMBER_KEY)) {
            return preferences.getInt(key, FRAGMENTS_DEFAULT);
        } else if (key.equals(MAX_VALUES_SIZE_KEY)) {
            return preferences.getInt(key, MAX_VALUES_DEFAULT);
        } else if (key.equals(BT_REFRESH_RATE_KEY)) {
            return preferences.getInt(key, BT_REFRESH_RATE_DEFAULT);
        } else if (key.equals(POINTER_WIDTH_KEY)) {
            return preferences.getInt(key, POINTER_WIDTH_DEFAULT);
        } else throw new InvalidKeyException("Invalid preferences key used");
    }

    public void storeBooleanValue(String key, boolean newValue) {
        Log.d(TAG, "Storing " + key + " = " + newValue);
        preferences.edit().putBoolean(key, newValue).commit();
    }

    private boolean loadBooleanValue(String key) throws InvalidKeyException {
        if (key.equals(SHOW_POINTER_KEY)) {
            return preferences.getBoolean(key, SHOW_POINTER_DEFAULT);
        } else if (key.equals(SHOW_COLORED_COMPASS_KEY)) {
            return preferences.getBoolean(key, SHOW_COLORED_COMPASS_DEFAULT);
        } else if (key.equals(SHOW_DEBUG_TEXT_KEY)) {
            return preferences.getBoolean(key, SHOW_DEBUG_TEXT_DEFAULT);
        } else if (key.equals(STAY_LOGGED_IN)) {
            return preferences.getBoolean(STAY_LOGGED_IN,false);
        } else if (key.equals(REMEMBER_ME_KEY)) {
            return preferences.getBoolean(REMEMBER_ME_KEY,false);
        }
        else throw new InvalidKeyException("Invalid preferences key used");
    }

    private String loadStringValue(String key) throws InvalidKeyException {
        if (key.equals(PASS_KEY)) {
            return preferences.getString(PASS_KEY, "");
        } else if (key.equals(EMAIL_KEY)) {
            return preferences.getString(EMAIL_KEY, "");
        } else throw new InvalidKeyException("Invalid preferences key used");
    }

    public CompassSettings loadCompassSettings() {
        try {
            boolean showColors = loadBooleanValue(SHOW_COLORED_COMPASS_KEY);
            boolean showPointer = loadBooleanValue(SHOW_POINTER_KEY);
            boolean showDebugText = loadBooleanValue(SHOW_DEBUG_TEXT_KEY);

            int nrOfFragments = loadIntValue(FRAGMENTS_NUMBER_KEY);
            int calibrationLimit = loadIntValue(CALIBRATION_LIMIT_KEY);
            int maxValues = loadIntValue(MAX_VALUES_SIZE_KEY);
            int pointerWidth = loadIntValue(POINTER_WIDTH_KEY);

            int refreshRate = loadIntValue(BT_REFRESH_RATE_KEY);

            return new CompassSettings(showColors, showPointer, showDebugText, nrOfFragments, calibrationLimit, maxValues, pointerWidth, refreshRate);
        } catch (InvalidKeyException e) {
            e.printStackTrace();
        }
        return null;
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

    public void storeUserSettings(String email, String pass, boolean rememberMe, boolean stayLoggedIn) {
        Log.i(TAG,"Storing user settings.");
        storeStringValue(EMAIL_KEY, email);
        storeStringValue(PASS_KEY, pass);
        storeBooleanValue(REMEMBER_ME_KEY,rememberMe);
        storeBooleanValue(STAY_LOGGED_IN,stayLoggedIn);
    }

    public boolean macAddressIsAuthorized(String macAddress) {

        for(String authorizedMac : macAddressesAuthorized) {
            if(authorizedMac.equals(macAddress)) {
//                Log.d(TAG,macAddress + " is authorized to be viewed.");
                return true;
            }
        }
//        Log.d(TAG,macAddress + " is NOT authorized to be viewed.");
        return false;
    }

    public UserSettings loadUserSettings() {
        try {
            String email = loadStringValue(EMAIL_KEY);
            String pass = loadStringValue(PASS_KEY);
            boolean rememberMe = loadBooleanValue(REMEMBER_ME_KEY);
            boolean stayLoggedIn = loadBooleanValue(STAY_LOGGED_IN);
            UserSettings userSettings = new UserSettings(email,pass,stayLoggedIn,rememberMe);

            return userSettings;
        }
        catch(InvalidKeyException e) {
            e.printStackTrace();
        }
        return null;
    }

    public ProductType productTypeForMacAddress(String mac) {
        return macProductTypeMap.get(mac);
    }

    public static int IdForProductType(ProductType type) {
        if(type == null) {
            return -1;
        }
        switch(type) {
            case KEYS:
                return R.drawable.key;
            case BRIEFCASE:
                return R.drawable.briefcase;
            case BAG:
                return R.drawable.designer_bag;
            case BIKE:
                return R.drawable.bike;
            case UMBRELLA:
                return R.drawable.umbrella;
            default:
                return -1;
        }
    }
}
