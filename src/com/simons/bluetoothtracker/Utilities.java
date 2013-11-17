package com.simons.bluetoothtracker;

import java.util.ArrayList;

public class Utilities {

    private final String unitSeparator = ", ";

    public static String arrayListToString(ArrayList<?> values) {
	String s = "[";
	for (int i = 0; i < values.size(); i++) {
	    Object o = values.get(i);
	    s += o.toString();
	    if (i < values.size() - 1) {
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
