package com.simons.bluetoothtracker.utilities;

import com.simons.bluetoothtracker.models.RSSIMeasurement;

import java.util.List;

/**
 * Created by gerardsimons on 31/03/14.
 *
 * Does some additional math functions such as mean
 */
public class MathHelper {

    public static double mean(double[] values) {
        double sum = 0;
        for(double value : values) {
            sum += value;
        }
        return sum / values.length;
    }

    public static float mean(float[] values) {
        float sum = 0;
        for(float value : values) {
            sum += value;
        }
        return sum / values.length;
    }

    public static RSSIMeasurement mean(List<RSSIMeasurement> measurements) {
        RSSIMeasurement meanMeasurement = null;
        float avgRSSI = 0;
        float avgAzimuth = 0;
        for(int i = 0 ; i < measurements.size() ; i++) {
            RSSIMeasurement measurement = measurements.get(i);
            avgRSSI += measurement.getRSSI();
            avgAzimuth += measurement.getAzimuth();
        }
        return meanMeasurement;
    }
}
