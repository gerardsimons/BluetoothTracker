package com.simons.bletracker.controllers;

import com.simons.bletracker.models.MacAddress;
import com.simons.bletracker.models.sql.BLETag;

import java.io.UnsupportedEncodingException;

/**
 * Created by Gerard on 22-7-2015.
 *
 * This class is responsible for checking whether a given BLE
 */
public class BLEAuthorizationController {

    private static BLEAuthorizationController Instance;

    private static MacAddress[] authorizedMacAddresses;



    private BLEAuthorizationController() {
        fillAuthorizedMacAddresses();
    }

    private void fillAuthorizedMacAddresses() {

        authorizedMacAddresses = new MacAddress[2];

        try {
            authorizedMacAddresses[0] = new MacAddress("ED:77:96:59:D1:F1"); //whereAt T
            authorizedMacAddresses[1] = new MacAddress("C5:E5:14:59:A0:A7");
        } catch (UnsupportedEncodingException e) {
            e.printStackTrace();
        }
    }


    public static BLEAuthorizationController getInstance() {
        if(Instance == null) {
            Instance = new BLEAuthorizationController();
        }
        return Instance;
    }

    public boolean isAuthorized(BLETag tag) {
        for(MacAddress mac : authorizedMacAddresses) {
            if(mac.equals(tag.getAddress())) {
                return true;
            }
        }
        return false;
    }
}

