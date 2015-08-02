package com.simons.bletracker.models;

import java.io.UnsupportedEncodingException;

/**
 * Created by gerard on 01/08/15.
 */
public class MacAddress {

    private String address;

    public MacAddress(String address) throws UnsupportedEncodingException {
        if(address.contains(":")) {
            if(address.length() != 17) {
                throw new UnsupportedEncodingException("Beautified MAC address should be of length 17");
            }
            this.address = address.replaceAll(":","");
        }
        else {
            if(address.length() == 12) {
                this.address = address;
            }
        }
    }

    /**
     * Returns the minified address, that is the address without any colon separators
     * @return
     */
    public String getMinifiedAddress() {
        return address;
    }

    /**
     * Returns the beautified address, that is the colon separated mac address where are a colon is placed after every two characters
     * @return the address in beautified colon-separated form
     */
    public String getBeautifiedAddress() {
        String beautfied = "";
        for(int i = 0 ; i < address.length() - 2 ; i += 2) {
            beautfied += address.substring(i,i+2) + ":";
        }
        return beautfied;
    }

    /**
     * Compare this mac address with another mac address to see if they are the same
     * @param otherMac, the other mac address object to compare to, if it is a MacAddress instance
     * @return true when the strings of these minified mac addresses are equal
     */
    @Override
    public boolean equals(Object otherMac) {
        if(otherMac instanceof MacAddress) {
            return ((MacAddress)otherMac).getMinifiedAddress().equals(getMinifiedAddress());
        }
        return false;
    }

    @Override
    public String toString() {
        return getBeautifiedAddress();
    }
}
