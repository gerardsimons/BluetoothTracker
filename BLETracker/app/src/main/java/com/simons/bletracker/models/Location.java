package com.simons.bletracker.models;

/**
 * Created by Gerard on 18-7-2015.
 */
public class Location {

    private String name;
    private double latitude;
    private double longitude;

    public Location(String name, double latitude, double longitude) {
        this.name = name;
        this.latitude = latitude;
        this.longitude = longitude;
    }
}
