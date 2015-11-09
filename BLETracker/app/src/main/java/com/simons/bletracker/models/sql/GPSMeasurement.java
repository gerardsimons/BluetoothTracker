package com.simons.bletracker.models.sql;

import java.util.Date;

/**
 * Created by gerard on 31/07/15.
 */
public class GPSMeasurement {

    public final float latitude;
    public final float longitude;
    public final Date timestamp;

    public GPSMeasurement(float latitude, float longitude, Date timestamp) {
        this.latitude = latitude;
        this.longitude = longitude;
        this.timestamp = timestamp;
    }
}
