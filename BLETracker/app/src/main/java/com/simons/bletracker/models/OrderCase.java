package com.simons.bletracker.models;

/**
 * Created by gerard on 23/07/15.
 */
public class OrderCase {

    private int ID;
    private Order order;
    private BLETag tag;

    public OrderCase(int ID, Order order) {
        this.ID = ID;
        this.order = order;
        this.tag = null;
    }

    public int getID() {
        return ID;
    }

    public void setID(int ID) {
        this.ID = ID;
    }

    public Order getOrder() {
        return order;
    }

    public void setOrder(Order order) {
        this.order = order;
    }

    public BLETag getTag() {
        return tag;
    }

    public void setTag(BLETag tag) {
        this.tag = tag;
    }
}
