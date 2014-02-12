package com.simons.bluetoothtracker.models;

public class RSSIMeasurement {

    //The measured RSSI
    private int rssi;

    //The azimuth of the measurment
    private float azimuth;

    // According to System.nanoTime
    private long timeStamp;

    public RSSIMeasurement(int rssi, float azimuth) {
        this.rssi = rssi;
        this.azimuth = azimuth;
        this.timeStamp = System.nanoTime();
    }

    public int getRSSI() {
        return rssi;
    }

    public long getTimeStamp() {
        return timeStamp;
    }

    public float getAzimuth() { return azimuth; }

    public String toString() {
        return rssi + "";
    }
}
