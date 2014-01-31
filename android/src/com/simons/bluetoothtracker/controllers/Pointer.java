package com.simons.bluetoothtracker.controllers;

import com.simons.bluetoothtracker.models.Compass;

/**
 * Created by gerardsimons on 26/01/14.
 */
public class Pointer {

    private static final int DEFAULT_WIDTH = 90;
    public static final int MIN_WIDTH = 10;

    private float centerAngle;
    private float width = DEFAULT_WIDTH;
    private float value;

    private float directionAlpha = 0.96F;


    public Pointer(float centerAngle, float value, int width) {
        this.centerAngle = 360F - Compass.NormalizeAngle(centerAngle);
        this.value = value;
        this.width = width;
    }
    
    public Pointer(float centerAngle, float value) {
        this.value = value;
        this.centerAngle = 360F - Compass.NormalizeAngle(centerAngle);
    }

    public double getCenterAngle() {
        return centerAngle;
    }

    public double getWidth() {
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

    public void update(int rssi, float azimuth) {
        if(rssi > value) {
            float delta = rssi - value;
            float weight = delta / (Compass.MAX_RSSI - Compass.MIN_RSSI);
            centerAngle = directionAlpha * centerAngle + (1 - directionAlpha) * azimuth;
        }
    }
}
