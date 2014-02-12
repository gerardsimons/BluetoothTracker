package com.simons.bluetoothtracker;

import com.simons.bluetoothtracker.models.RSSIMeasurement;

import java.util.List;

public class Utilities {

    private final String unitSeparator = ", ";

    public static String listToString(List<?> fragments) {
        String s = "[";
        for (int i = 0; i < fragments.size(); i++) {
            Object o = fragments.get(i);
            s += o.toString();
            if (i < fragments.size() - 1) {
                s += ", ";
            }
        }
        return s + "]";
    }

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

    public static String arrayToString(float[] values) {
        String s = "[";
        for (int i = 0; i < values.length; i++) {
            s += values[i];
            if (i < values.length - 1) {
                s += ", ";
            }
        }
        return s + "]";
    }

    public static String arrayToString(double[] values) {
        String s = "[";
        for (int i = 0; i < values.length; i++) {
            s += values[i];
            if (i < values.length - 1) {
                s += ", ";
            }
        }
        return s + "]";
    }

    public static String arrayToString(int[] values) {
        String s = "[";
        for (int i = 0; i < values.length; i++) {
            s += values[i];
            if (i < values.length - 1) {
                s += ", ";
            }
        }
        return s + "]";
    }
}
