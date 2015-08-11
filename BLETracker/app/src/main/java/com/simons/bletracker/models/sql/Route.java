package com.simons.bletracker.models.sql;

import java.util.Date;

/**
 * Created by gerard on 23/07/15.
 */
public class Route {

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public Date getStart() {
        return start;
    }

    public void setStart(Date start) {
        this.start = start;
    }

    public Date getFinish() {
        return finish;
    }

    public void setFinish(Date finish) {
        this.finish = finish;
    }

    private int id;
    private Date start;
    private Date finish;

    public Route(int id) {
        this.id = id;
    }
}
