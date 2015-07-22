package com.simons.bletracker.models;

public class BLETag {

    private String name = null;
    private String address = null;
    private Integer latestRSSI = null;

    public BLETag(String name, String address) {
        this.name = name;
        this.address = address;
        this.latestRSSI = null;
    }

    public BLETag(String name, String address, int latestRSSI) {
        this.address = address;
        this.name = name;
        this.latestRSSI = latestRSSI;
    }

    public String getAddress() {
        return address;
    }

    public boolean equals(Object o) {
        if (o instanceof BLETag) {
            BLETag other = (BLETag) o;
            if (other.getAddress().equals(getAddress())) {
                return true;
            }
        }
        return false;
    }

    public String getName() {
        return name;
    }

    public Integer getLatestRSSI() {
        return latestRSSI;
    }

    public String toString() {
        String toString = "MyBlueToothDevice\nName: " + getName() + "\nAddress:" + getAddress() + "\nRSSI Value: " + latestRSSI;
        return toString;
    }

    public void setLatestRSSI(int latestRSSI) {
        this.latestRSSI = latestRSSI;
    }
}
