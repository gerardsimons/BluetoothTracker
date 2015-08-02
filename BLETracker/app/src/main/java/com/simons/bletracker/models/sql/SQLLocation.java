package com.simons.bletracker.models.sql;

/**
 * Created by Gerard on 18-7-2015.
 *
 * Model class for the Location found in the SQL database, the name SQLLocation is used
 * as a disambiguation for Android's Location class
 */
public class SQLLocation {

    private int id;
    private float latitude;
    private float longitude;
    private String street;
    private int streetNumber;
    private String city;
    private String zipCode;

    public SQLLocation(int id) {
        this.id = id;
    }

    public SQLLocation(int id, float latitude, float longitude, String street, int streetNumber, String city, String zipCode) {
        this.id = id;
        this.street = street;
        this.streetNumber = streetNumber;
        this.city = city;
        this.zipCode = zipCode;
        this.latitude = latitude;
        this.longitude = longitude;
    }

    public float getLatitude() {
        return latitude;
    }

    public float getLongitude() {
        return longitude;
    }
}
