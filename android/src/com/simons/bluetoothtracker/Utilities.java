package com.simons.bluetoothtracker;

import java.util.List;

public class Utilities {

    private final String unitSeparator = ", ";

    public static String listToString(List<?> fragments) {
        String s = "[";
        for (int i = 0; i < fragments.size(); i++) {
            Object o = fragments.get(i);
            s += o.toString();
            if (i < fragments.size() - 1) {
                s += ", ";
            }
        }
        return s + "]";
    }

    public static String arrayToString(float[] values) {
        String s = "[";
        for (int i = 0; i < values.length; i++) {
            s += values[i];
            if (i < values.length - 1) {
                s += ", ";
            }
        }
        return s + "]";
    }

    public static String arrayToString(int[] values) {
        String s = "[";
        for (int i = 0; i < values.length; i++) {
            s += values[i];
            if (i < values.length - 1) {
                s += ", ";
            }
        }
        return s + "]";
    }
}
