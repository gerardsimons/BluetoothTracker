package com.simons.bletracker.models;

/**
 * Created by Gerard on 18-7-2015.
 */
public class User {

    private String name;
    private int ID;

    public User(int ID, String name) {
        this.ID = ID;
        this.name = name;
    }

    public int getID() {
        return ID;
    }

    public String getName() {
        return name;
    }

    public String toString() {
        return String.format("User ID = %d name = %s ",ID,name);
    }
}
