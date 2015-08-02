package com.simons.bletracker.models.sql;

/**
 * Created by Gerard on 18-7-2015.
 */
public class OrderItem {

    private int ID;
    private SQLLocation sourceSQLLocation;
    private SQLLocation targetSQLLocation;
    private User creator;

    public OrderItem(SQLLocation sourceSQLLocation, SQLLocation targetSQLLocation, User creator) {
        this.sourceSQLLocation = sourceSQLLocation;
        this.targetSQLLocation = targetSQLLocation;
        this.creator = creator;
    }

    public SQLLocation getSourceSQLLocation() {
        return sourceSQLLocation;
    }

    public SQLLocation getTargetSQLLocation() {
        return targetSQLLocation;
    }

    public User getCreator() {
        return creator;
    }

    public int getID() {
        return ID;
    }
}
