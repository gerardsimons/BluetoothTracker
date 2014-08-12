package com.devriesdev.whereatassettracking.utils;

import java.text.DateFormat;

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

    public static String getTimeString(long timeStamp) {
        DateFormat dateFormat = DateFormat.getTimeInstance(DateFormat.MEDIUM);
        return dateFormat.format(timeStamp);
    }
}
