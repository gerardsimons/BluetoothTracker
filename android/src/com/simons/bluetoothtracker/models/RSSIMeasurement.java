package com.simons.bluetoothtracker.models;

public class RSSIMeasurement {

    //The measured RSSI
    private float rssi;

    //The azimuth of the measurment
    private float azimuth;

    // According to System.nanoTime
    private long timeStamp;

    //A weight, default to 1, makes average weighted
    private int weight = 1;

    public RSSIMeasurement(float rssi, float azimuth) {
        this.rssi = rssi;
        this.azimuth = azimuth;
        this.timeStamp = System.nanoTime();
    }

    //Constructor without an angle, useful for propagated values
    public RSSIMeasurement(float rssi) {
        this.rssi = rssi;
        this.azimuth = -1;
        this.timeStamp = System.nanoTime();
    }

    //Constructor with weighted value
    public RSSIMeasurement(float rssi, int weight) {
        this.rssi = rssi;
        this.weight = weight;
        this.azimuth = -1;
        this.timeStamp = System.nanoTime();
    }

    public float getRSSI() {
        return rssi;
    }

    public long getTimeStamp() {
        return timeStamp;
    }

    public float getAzimuth() { return azimuth; }

    public String toString() {
        return rssi + "";
    }

    public int getWeight() {
        return weight;
    }
}
