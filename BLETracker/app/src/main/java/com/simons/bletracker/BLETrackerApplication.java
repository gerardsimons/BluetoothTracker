package com.simons.bletracker;

import android.app.Application;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Build;

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

    BLEAuthorizationController bleAuthorizationController;
    StateController stateController;
    ServerAPI serverAPI;

    GPSService gpsService;

    @Override
    public void onCreate() {
        super.onCreate();

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
        return Math.max(Math.min(1,ratio),0);
    }
}
