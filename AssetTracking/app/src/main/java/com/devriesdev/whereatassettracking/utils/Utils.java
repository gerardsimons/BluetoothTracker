package com.devriesdev.whereatassettracking.utils;

import java.text.DateFormat;
import java.util.Date;
import java.util.List;
import java.util.Vector;

/**
 * Created by danie_000 on 6/15/2014.
 */
public class Utils {
    public interface Executable {
        public void execute();
        public void postExecute();
    }

    public interface InitiatedListener {
        public void onInitiated();
    }

    public static String getTimestamp() {
        Date now = new Date(System.currentTimeMillis());
        DateFormat dateFormat = DateFormat.getTimeInstance(DateFormat.MEDIUM);
        return dateFormat.format(now);
    }
}
