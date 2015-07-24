package com.simons.bletracker;

import android.app.Application;

import com.simons.bletracker.controllers.BLEAuthorizationController;
import com.simons.bletracker.models.BLETag;

/**
 * Created by Gerard on 22-7-2015.
 */
public class BLETrackerApplication extends Application {

    private enum State {
        WAITING_FOR_CASE_SCAN,
        WAITING_FOR_LABEL_SCAN,
        READY_FOR_DEPARTURE,
        EN_ROUTE,
        ARRIVED,
        RETURNED
    }

    private State state = State.WAITING_FOR_CASE_SCAN;

    public static final int REQUEST_ENABLE_BT = 1;
    private static final int MIN_RSSI = -100;
    private static final int MAX_RSSI = -30;

    BLEAuthorizationController bleAuthorizationController;

    @Override
    public void onCreate() {
        super.onCreate();

        //Create controller structure
        bleAuthorizationController = BLEAuthorizationController.getInstance();
    }

    public boolean isAuthorized(BLETag tag) {
        return bleAuthorizationController.isAuthorized(tag);
    }

    public boolean onCaseScanned(String caseCode) {
        //Cache the caseCode


        return true;
    }

    public boolean onTagScanned(BLETag tag) {
        //Link this tag with the previously cached case code

        //Create the route

        return true;
    }

    //TODO: This should probably go in some utility class
    public static float RelativeSignalStrength(int rssi) {
        float ratio = (rssi - MIN_RSSI) / (float)(MAX_RSSI - MIN_RSSI);
        return Math.max(Math.min(1,ratio),0);
    }
}
