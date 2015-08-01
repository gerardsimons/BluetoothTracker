package com.simons.bletracker.models.sql;

/**
 * Created by gerard on 31/07/15.
 */
public class GPSMeasurement {

    public final float latitude;
    public final float longitude;
    public final long timestamp;

    public GPSMeasurement(float latitude, float longitude, long timestamp) {
        this.latitude = latitude;
        this.longitude = longitude;
        this.timestamp = timestamp;
    }
}
