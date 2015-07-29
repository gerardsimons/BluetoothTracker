package com.simons.bletracker.models;

/**
 * Created by Gerard on 18-7-2015.
 */
public class Order {

    private int ID;
    private Customer customer;

    public Order(int ID,Customer customer) {
        this.ID = ID;
        this.customer = customer;
    }

    public int getID() {
        return ID;
    }

    public Customer getCustomer() {
        return customer;
    }
}
