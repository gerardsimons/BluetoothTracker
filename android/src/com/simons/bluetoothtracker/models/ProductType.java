package com.simons.bluetoothtracker.models;

import android.util.Log;

import java.util.Arrays;
import java.util.Collections;
import java.util.List;
import java.util.Random;

/**
 * Created by gerardsimons on 26/02/14.
 */
public enum ProductType {
    KEYS,BRIEFCASE,BAG,BIKE,UMBRELLA;

    private static final String TAG = "ProductType";

    private static final List<ProductType> VALUES =
            Collections.unmodifiableList(Arrays.asList(values()));
    private static final int SIZE = VALUES.size();
    private static final Random RANDOM = new Random();

    private static int COUNTER = 0;

    public static ProductType RandomProductType()  {
        return VALUES.get(RANDOM.nextInt(SIZE));
    }

    public static ProductType NextProductType() {
        ProductType type = VALUES.get(COUNTER);
        Log.d(TAG, "type = " + type);
        COUNTER++;
        if(COUNTER >= VALUES.size()) COUNTER = 0; //Reset counter if it goes out of bound
        return type;
    }
}
