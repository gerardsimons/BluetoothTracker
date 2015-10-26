package com.simons.bletracker.controllers;

import com.simons.bletracker.Configuration;
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

        authorizedMacAddresses = new MacAddress[Configuration.BLE_TAG_MAC_ADDRESSES.length];

        //Convert mac addresses to proper mac adress objects
        try {
            for(int i = 0 ; i < Configuration.BLE_TAG_MAC_ADDRESSES.length ; ++i) {
                authorizedMacAddresses[i] = new MacAddress(Configuration.BLE_TAG_MAC_ADDRESSES[i]);
            }
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

