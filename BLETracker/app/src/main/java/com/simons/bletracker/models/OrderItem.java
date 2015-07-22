package com.simons.bletracker.models;

/**
 * Created by Gerard on 18-7-2015.
 */
public class OrderItem {

    private int ID;
    private Location sourceLocation;
    private Location targetLocation;
    private User creator;

    public OrderItem(Location sourceLocation, Location targetLocation, User creator) {
        this.sourceLocation = sourceLocation;
        this.targetLocation = targetLocation;
        this.creator = creator;
    }

    public Location getSourceLocation() {
        return sourceLocation;
    }

    public Location getTargetLocation() {
        return targetLocation;
    }

    public User getCreator() {
        return creator;
    }

    public int getID() {
        return ID;
    }
}
