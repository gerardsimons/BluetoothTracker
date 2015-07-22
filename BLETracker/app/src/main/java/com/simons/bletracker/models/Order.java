package com.simons.bletracker.models;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by Gerard on 18-7-2015.
 */
public class Order {

    private User creator;
    private User assignedWorker;
    private ArrayList<OrderItem> orders;

    public Order() {

    }

    public User getCreator() {
        return creator;
    }

    public User getAssignedWorker() {
        return assignedWorker;
    }

    public int size() {
        return orders.size();
    }

    public List<OrderItem> getOrders() {
        return orders;
    }

    public OrderItem getOrder(int i) {
        return orders.get(i);
    }
}
