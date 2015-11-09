package com.simons.bletracker.models.sql;

import com.simons.bletracker.models.MacAddress;

import java.util.Date;

/**
 * Created by gerard on 31/07/15.
 */
public class RSSIMeasurement {

    public final int rssi;
    public final Date timestamp;

    public final BLETag tag;

    public RSSIMeasurement(int rssi, Date timestamp, BLETag tag) {
        this.rssi = rssi;
        this.timestamp = timestamp;
        this.tag = tag;
    }

    public MacAddress getMacAddress() {
        return tag.getAddress();
    }
}
