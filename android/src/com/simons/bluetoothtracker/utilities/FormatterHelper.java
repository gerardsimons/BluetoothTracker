package com.simons.bluetoothtracker.utilities;

import java.util.List;

/**
 * Created by gerardsimons on 31/03/14.
 *
 * Some static help functions to format and print arrays of different things
 */
public class FormatterHelper {
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

    public static String arrayToString(double[] values) {
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
}
