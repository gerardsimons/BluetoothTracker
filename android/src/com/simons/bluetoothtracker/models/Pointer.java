package com.simons.bluetoothtracker.models;

import android.util.Log;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by gerardsimons on 26/01/14.
 */
public class Pointer {

    private static final int DEFAULT_WIDTH = 90;
    public static final int MIN_WIDTH = 10;

    private static final String TAG = "Pointer";

    private float centerAngle;
    private int width = DEFAULT_WIDTH;
    private float value;

    private List<RSSIMeasurement> containedMeasurements;

    public Pointer(float centerAngle, float value, int width) {
        this.centerAngle = centerAngle;
        this.value = value;
        this.width = width;
        containedMeasurements = new ArrayList<RSSIMeasurement>();
    }
    
    public Pointer(float centerAngle, float value) {
        this.value = value;
        this.centerAngle = centerAngle;
        containedMeasurements = new ArrayList<RSSIMeasurement>();
    }

    public float getCenterAngle() {
        return centerAngle;
    }

    public int getWidth() {
        return width;
    }

    public float getValue() {
        return value;
    }

    public double getStartAngle() {
        float startAngle = (float) (centerAngle - width / 2.0);

        return startAngle;
    }

    public String toString() {
       return "Start angle = " + getStartAngle() + "\n" + " centerAngle = " + centerAngle + "\nwidth = " + width + "\nvalue = " + value;
    }

    public void addMeasurement(RSSIMeasurement rssiMeasurement) {
        containedMeasurements.add(rssiMeasurement);
    }

    public boolean contains(float azimuth) {
        float distanceToCenter = Compass.distanceTo(centerAngle,azimuth);
        Log.d(TAG,"Distance to center = " + distanceToCenter);
        return distanceToCenter < width;
    }

    public void computeValue() {
        value = 0;
        for(RSSIMeasurement rssiMeasurement : containedMeasurements) {
            value += rssiMeasurement.getRSSI();
        }
        value /= containedMeasurements.size();
    }
}
