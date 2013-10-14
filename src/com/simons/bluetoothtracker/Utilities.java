package com.simons.bluetoothtracker;

import java.util.ArrayList;

public class Utilities {

    public static String arrayListToString(ArrayList<?> objects) {
	String toString = "[";
	for (Object o : objects) {
	    toString += o.toString() + "  ";
	}
	return toString;
    }
}
