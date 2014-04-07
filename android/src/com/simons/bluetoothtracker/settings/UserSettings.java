package com.simons.bluetoothtracker.settings;

/**
 * Created by gerardsimons on 03/04/14.
 */
public class UserSettings {

    public String email;
    public String pass;
    public boolean stayLoggedIn;
    public boolean rememberMe;

    public UserSettings(String email, String pass,boolean stayLoggedIn, boolean rememberMe) {
        this.email = email;
        this.pass = pass;
        this.stayLoggedIn = stayLoggedIn;
        this.rememberMe = rememberMe;
    }
}
