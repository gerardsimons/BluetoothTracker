package com.simons.bluetoothtracker.controllers;

import android.util.Log;

import com.simons.bluetoothtracker.models.Compass;
import com.simons.bluetoothtracker.models.RSSIMeasurement;

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

    private List<RSSIMeasurement> containedMeasurments;

    public Pointer(float centerAngle, float value, int width) {
        this.centerAngle = 360F - Compass.NormalizeAngle(centerAngle);
        this.value = value;
        this.width = width;
        containedMeasurments = new ArrayList<RSSIMeasurement>();
    }
    
    public Pointer(float centerAngle, float value) {
        this.value = value;
        this.centerAngle = 360F - Compass.NormalizeAngle(centerAngle);
        containedMeasurments = new ArrayList<RSSIMeasurement>();
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

    public void setCenterAngle(float centerAngle) {
        this.centerAngle = centerAngle;
    }

    public String toString() {
       return "Start angle = " + getStartAngle() + "\n" + " centerAngle = " + centerAngle + "\nwidth = " + width + "\nvalue = " + value;
    }

    public void addMeasurement(RSSIMeasurement rssiMeasurement) {
        containedMeasurments.add(rssiMeasurement);
    }

    public boolean contains(float azimuth) {
        float distanceToCenter = Compass.distanceTo(centerAngle,azimuth);
        Log.d(TAG,"Distance to center = " + distanceToCenter);
        return distanceToCenter < width;
    }

    public void computeValue() {
        value = 0;
        for(RSSIMeasurement rssiMeasurement : containedMeasurments) {
            value += rssiMeasurement.getRSSI();
        }
        value /= containedMeasurments.size();
    }
}
