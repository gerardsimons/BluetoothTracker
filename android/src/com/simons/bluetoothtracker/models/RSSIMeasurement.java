package com.simons.bluetoothtracker.models;

public class RSSIMeasurement {

    //The measured RSSI
    private int rssi;

    //The azimuth of the measurment
    private double azimuth;

    // According to System.nanoTime
    private long time;

    public RSSIMeasurement(int rssi, double azimuth) {
        this.rssi = rssi;
        this.azimuth = azimuth;
        this.time = System.nanoTime();
    }

    public int getRSSI() {
        return rssi;
    }

    public long getTime() {
        return time;
    }

    public double getAzimuth() { return azimuth; }

    public String toString() {
        return rssi + "";
    }
}
