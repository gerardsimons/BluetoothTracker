package com.simons.bletracker.models.sql;

/**
 * Created by gerard on 31/07/15.
 */
public class RSSIMeasurement {

    public final int rssi;
    public final long timestamp;

    public final BLETag tag;

    public RSSIMeasurement(int rssi, long timestamp, BLETag tag) {
        this.rssi = rssi;
        this.timestamp = timestamp;
        this.tag = tag;
    }

    public String getMacAddress() {
        return tag.getAddress();
    }
}
