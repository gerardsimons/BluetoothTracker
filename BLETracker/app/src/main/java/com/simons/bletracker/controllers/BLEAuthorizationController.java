package com.simons.bletracker.controllers;

import com.simons.bletracker.models.sql.BLETag;

/**
 * Created by Gerard on 22-7-2015.
 *
 * This class is responsible for checking whether a given BLE
 */
public class BLEAuthorizationController {

    private static BLEAuthorizationController Instance;
    private static String[] authorizedBLETags = new String[] {
            "ED:77:96:59:D1:F1", //whereAt T
            "C5:E5:14:59:A0:A7"};


    private BLEAuthorizationController() {

    }

    public static BLEAuthorizationController getInstance() {
        if(Instance == null) {
            Instance = new BLEAuthorizationController();
        }
        return Instance;
    }

    public boolean isAuthorized(BLETag tag) {
        for(String mac : authorizedBLETags) {
            if(mac.equals(tag.getAddress())) {
                return true;
            }
        }
        return false;
    }
}

