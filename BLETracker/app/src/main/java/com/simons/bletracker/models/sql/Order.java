package com.simons.bletracker.models.sql;

import com.simons.bletracker.remote.ServerAPI;

import org.json.JSONException;
import org.json.JSONObject;

import java.text.ParseException;
import java.util.Date;

/**
 * Created by Gerard on 18-7-2015.
 */
public class Order {

    private int ID;
    private Customer customer;
    private Date created;

    public Order(int ID,Customer customer) {
        this.ID = ID;
        this.customer = customer;
    }

    public Order(JSONObject orderJson) throws JSONException, ParseException {
        ID = orderJson.getInt("ID");
        created = ServerAPI.ServerDateTimeFormat.parse(orderJson.getString("Created"));
    }

    public int getID() {
        return ID;
    }

    public Customer getCustomer() {
        return customer;
    }

    public void setCustomer(Customer newCustomer) {
        customer = newCustomer;
    }
}
