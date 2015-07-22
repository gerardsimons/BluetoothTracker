package com.simons.bletracker.controllers;

import com.simons.bletracker.models.BLETag;

/**
 * Created by Gerard on 22-7-2015.
 *
 * This class is responsible for checking whether a given BLE
 */
public class BLEAuthorizationController {

    private static BLEAuthorizationController Instance;
    private static String[] authorizedBLETags = new String[] {
            "E3:8E:9A:75:CE:D0", //whereAt La2
            "F8:01:51:4D:1F:96",
            "F0:17:35:EF:3C:0F",
            "E6:F7:72:34:9F:79", //Where@ label
            "F0:44:BA:E7:D0:39",
            "C7:38:09:E4:A9:84",
            "FE:A1:EB:F0:80:E7",
            "C7:9E:37:9A:95:AA"};


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
            if(mac == tag.getAddress()) {
                return true;
            }
        }
        return false;
    }
}

