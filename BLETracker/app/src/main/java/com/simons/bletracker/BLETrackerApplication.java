package com.simons.bletracker;

import android.app.AlertDialog;
import android.app.Application;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Build;
import android.util.Log;

import com.simons.bletracker.controllers.BLEAuthorizationController;
import com.simons.bletracker.controllers.StateController;
import com.simons.bletracker.models.BLETag;
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

        //Create controller structure
        bleAuthorizationController = BLEAuthorizationController.getInstance();
        stateController = StateController.GetInstance();
        serverAPI = ServerAPI.GetInstance();


    }

    private void errorAlert(Context context, String title, String message) {

        if(title == null) {
            title = "Error";
        }
        if(message == null) {
            message = "AN UNKNOWN ERROR OCCURRED";
        }

        AlertDialog alertDialog = new AlertDialog.Builder(context).create();
        alertDialog.setTitle(title);
        alertDialog.setMessage(message);
        alertDialog.setButton(AlertDialog.BUTTON_NEUTRAL, "OK",
                new DialogInterface.OnClickListener() {
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.dismiss();
                    }
                });
        alertDialog.show();
    }

    public void checkRegistered(Context context) {

        SharedPreferences settings = getSharedPreferences(PREFERENCES_NAME, 0);
        boolean firstRun= settings.getBoolean("FIRST_RUN", true);
        if (firstRun) {
            //Register device as ble controller
            if(serverAPI.registerBLEController(Build.SERIAL, Installation.id(getApplicationContext()))) {
                SharedPreferences.Editor editor = settings.edit();
                editor.putBoolean("FIRST_RUN", false);
                editor.commit();
            }
            else {
                errorAlert(context,"RegisterError","Unable to register this device as a BLE controller");
            }
        }
        else {
            Log.d(TAG,"Device already registered");
        }
    }

    public boolean isAuthorized(BLETag tag) {
        return bleAuthorizationController.isAuthorized(tag);
    }

    public boolean onCaseScanned(String caseCode) {
        //Cache the caseCode
        StateController.State newState = stateController.doAction(StateController.Action.CASE_SCANNED);

        if(newState == StateController.State.WAITING_FOR_LABEL_SCAN) {
            return true;
        }

        return false;
    }

    public boolean onTagScanned(BLETag tag) {
        //Link this tag with the previously cached case code
        StateController.State newState = stateController.doAction(StateController.Action.LABEL_SCANNED);

        //Create the route

        //Start GPS service
        if(!GPSService.Running) {
            Intent gpsServiceIntent = new Intent(this,GPSService.class);
            startService(gpsServiceIntent);
        }

        return true;
    }

    //TODO: This should probably go in some utility class
    public static float RelativeSignalStrength(int rssi) {
        float ratio = (rssi - MIN_RSSI) / (float)(MAX_RSSI - MIN_RSSI);
        return Math.max(Math.min(1,ratio),0);
    }
}
