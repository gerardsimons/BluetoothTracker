package com.simons.bletracker.models.sql;

/**
 * Created by gerard on 29/07/15.
 */
public class Customer {

    private int ID;
    private String name;
    private SQLLocation location;

    public Customer(int ID) {
        this.ID = ID;
    }

    public Customer(int ID, String name, SQLLocation location) {
        this.ID = ID;
        this.name = name;
        this.location = location;
    }

    public SQLLocation getLocation() {
        return location;
    }
}
