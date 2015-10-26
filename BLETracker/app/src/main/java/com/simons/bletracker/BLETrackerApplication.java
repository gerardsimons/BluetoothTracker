package com.simons.bletracker;

import android.app.Application;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Build;
import android.util.Log;

import com.simons.bletracker.controllers.BLEAuthorizationController;
import com.simons.bletracker.controllers.BLETracker;
import com.simons.bletracker.controllers.StateController;
import com.simons.bletracker.models.sql.BLETag;
import com.simons.bletracker.remote.ServerAPI;
import com.simons.bletracker.services.GPSService;

/**
 * Created by Gerard on 22-7-2015.
 */
public class BLETrackerApplication extends Application {

    public static final int REQUEST_ENABLE_BT = 1;
    private static final int MIN_RSSI = -100;
    private static final int MAX_RSSI = -30;

    private static final String PREFERENCES_NAME = "com.simons.bletracker";
    private static final String TAG = "BLETrackerApplication";

    private BLEAuthorizationController bleAuthorizationController;
    private StateController stateController;
    private ServerAPI serverAPI;

    private GPSService gpsService;

    private SharedPreferences preferences;

    @Override
    public void onCreate() {
        super.onCreate();

        preferences = this.getSharedPreferences("com.simons.bletracker",0);

        loadConfiguration();

        //IMPORTANT: set context first
        BLETracker.SetContext(getApplicationContext());
        BLETracker tracker = BLETracker.GetInstance();
        tracker.setDeviceId(Build.SERIAL);
        tracker.setInstallId(Installation.id(getApplicationContext()));

        //Create controller structure
        bleAuthorizationController = BLEAuthorizationController.getInstance();
        stateController = StateController.GetInstance();
        serverAPI = ServerAPI.GetInstance();

        Intent intent = new Intent(this,GPSService.class);
        startService(intent);
    }

    public boolean isFirstRun() {
        SharedPreferences settings = getSharedPreferences(PREFERENCES_NAME, 0);
        return settings.getBoolean("FIRST_RUN", true);
    }

    public void setFirstRun(boolean firstRun) {
        SharedPreferences settings = getSharedPreferences(PREFERENCES_NAME, 0);
        SharedPreferences.Editor editor = settings.edit();
        editor.putBoolean("FIRST_RUN", firstRun);
        editor.commit();
    }

    public boolean isAuthorized(BLETag tag) {
        return bleAuthorizationController.isAuthorized(tag);
    }

    //TODO: This should probably go in some utility class
    public static float RelativeSignalStrength(int rssi) {
        float ratio = (rssi - MIN_RSSI) / (float)(MAX_RSSI - MIN_RSSI);
        return Math.max(Math.min(1, ratio), 0);
    }

    /*** SETTINGS LOADING AND STORING ***/
    private void loadConfiguration() {
        Configuration.ARRIVE_DISTANCE = loadArriveDistanceTrigger();
        Configuration.DEPART_DISTANCE = loadDepartDistanceTrigger();
        Configuration.TRACKER_CACHE_SIZE = loadTrackerCacheSize();
        Configuration.GPS_DISPLACEMENT = loadGPSDisplacement();
        Configuration.GPS_UPDATE_INTERVAL = loadGPSUpdateInterval();
        Configuration.GPS_FASTEST_INTERVAL = loadGPSFastestUpdateInterval();

        Log.d(TAG,"Configuration (re)loaded");

        Log.d(TAG,Configuration.ToString());
    }

    private void storeIntSetting(String name, int value) {

    }

    public void storeGPSDisplacement(int displacementMeters) {
        preferences.edit().putInt("gpsDisplacement",displacementMeters).commit();
    }

    public void storeGPSUpdateInterval(int interval) {
        preferences.edit().putInt("gpsUpdateInterval",interval).commit();
    }

    public void storeFastestGPSUpdateInterval(int interval) {
        preferences.edit().putInt("gpsFastesUpdateInterval",interval).commit();
    }

    public void storeTrackerCacheSize(int cacheSize) {
        preferences.edit().putInt("trackerCacheSize",cacheSize).commit();
    }

    public void storeDepartDistanceTrigger(int distanceTriggerMeters) {
        preferences.edit().putInt("departDistanceTrigger",distanceTriggerMeters).commit();
    }

    public void storeArriveDistanceTrigger(int distanceTriggerMeters) {
        preferences.edit().putInt("arriveDistanceTrigger",distanceTriggerMeters).commit();
    }

    public void storeRSSIDiscoveryInterval(int newVal) {
        preferences.edit().putInt("rssiDiscoveryInterval",newVal).commit();
    }

    public int loadGPSDisplacement() {
        return preferences.getInt("gpsDisplacement",Configuration.GPS_DISPLACEMENT);
    }

    public int loadGPSUpdateInterval() {
        return preferences.getInt("gpsUpdateInterval",Configuration.GPS_UPDATE_INTERVAL);
    }

    public int loadGPSFastestUpdateInterval() {
        return preferences.getInt("gpsFastestUpdateInterval",Configuration.GPS_FASTEST_INTERVAL);
    }

    public int loadTrackerCacheSize() {
        return preferences.getInt("trackerCacheSize",Configuration.TRACKER_CACHE_SIZE);
    }

    public int loadDepartDistanceTrigger() {
        return preferences.getInt("departDistanceTrigger",Configuration.DEPART_DISTANCE);
    }

    public int loadArriveDistanceTrigger() {
        return preferences.getInt("arriveDistanceTrigger",Configuration.ARRIVE_DISTANCE);
    }
}
