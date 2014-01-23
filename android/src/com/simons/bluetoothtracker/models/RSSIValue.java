package com.simons.bluetoothtracker.models;

public class RSSIValue {

    private int value;

    // According to System.nanoTime
    private long time;

    public RSSIValue(int value) {
        this.value = value;
        this.time = System.nanoTime();
    }

    public int getValue() {
        return value;
    }

    public long getTime() {
        return time;
    }

    public String toString() {
        return value + "";
    }
}
