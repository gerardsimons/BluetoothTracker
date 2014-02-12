package com.simons.bluetoothtracker.models;

import com.simons.bluetoothtracker.Utilities;

import java.util.ArrayList;
import java.util.List;

public class Fragment implements CompassDataSource {

    public static final String TAG = "Fragment";

    private int maxSizeValues;

    private List<RSSIMeasurement> rssiMeasurements;

    private int id;

    private float centerAngle;

    private boolean active;

    private int calibrationLimit;

    private boolean calibrated = false;
    private float calibrationValue;

    private float averageAngle = Float.NaN;

    public Fragment(int calibrationLimit, int id, float centerAngle, int maxSizeValues) {
        //Make sure it can store at least as many values as it needs for calibration.
        if(maxSizeValues < calibrationLimit) {
            maxSizeValues += (calibrationLimit - maxSizeValues);
        }
        this.maxSizeValues = maxSizeValues;
        this.centerAngle = centerAngle;
        this.calibrationLimit = calibrationLimit;
        this.id = id;
        rssiMeasurements = new ArrayList<RSSIMeasurement>();
    }

    /**
     * Gets the value that represents this fragment, this could be average or
     * something else, though default is average. Future implementation could be
     * weighted averages
     *
     * @return double the value, double is used to ensure sufficient accuracy
     * allowing for multiple types of calculations.
     */
    public double getValue() {
        if(isCalibrated()) {
            return averageRSSI();
        }
        else return Double.NaN;
    }

    @Override
    public double getCalibrationValue() {
        return calibrationValue;
    }


//    private float weightedTimeAverageRSSI() {
//        float value = 0;
//        Long oldestTime = null;
//        Long newestTime = timeStamps.get(timeStamps.size() - 1);
//
//        long tInterval = 0L;
//
//        int N = rssiValues.size();
//
//        for(int i = 0 ; i < rssiValues.size() ; i++) {
//            Double rssiValue = rssiValues.get(i);
//            if(rssiValue != null) {
//                if(oldestTime == null) {
//                    oldestTime = timeStamps.get(i);
//                    tInterval = oldestTime - newestTime;
//                }
//                else {
//                    value += (timeStamps.get(i) - oldestTime) / tInterval;
//                }
//            }
//        }
//        return value;
//    }

    @Override
    public int getNrOfValuesMeasured() {
        return calibrationLimit - rssiMeasurements.size();
    }

    public int getCalibrationLimit() {
        return calibrationLimit;
    }

    @Override
    public boolean highlighted() {
        return active;
    }

    public String toString() {
        String s = "";
        if (isCalibrated()) {
            s = "Calibrated ";
        } else
            s = "Uncalibrated ";
        s += "fragment with representative value = " + getValue()
                + " Values= ";
        s += Utilities.listToString(rssiMeasurements) + "\n";
        return s;
    }

    private double averageRSSI() {
        if (!rssiMeasurements.isEmpty()) {
            int sum = 0;
            for (RSSIMeasurement measurement : rssiMeasurements) {
                sum += measurement.getRSSI();
            }
            return sum / (double) rssiMeasurements.size();
        } else
            return Double.NaN;
    }

    public void addValues(RSSIMeasurement rssiMeasurement) {
        rssiMeasurements.add(rssiMeasurement);

//        Log.d(TAG, "Fragment adding values (rssi,angle): " + rssi + ", " + angle);
//        Log.d(TAG, "Values size : " + rssiValues.size());

        if(rssiMeasurements.size() > maxSizeValues) {
            rssiMeasurements.remove(0);
        }
    }

    public boolean isCalibrated() {
        // Log.d(TAG, "rssiValues.size() = " + rssiValues.size());
        // Log.d(TAG, "calibrationLimit = " + calibrationLimit);
        if(rssiMeasurements.size() >= calibrationLimit) {
            calibrated = true;
        }
        return calibrated;
    }

    public void setActive(boolean newActive) {
        active = newActive;
    }

    public void clearData() {
        rssiMeasurements.clear();
    }

    public boolean equals(Object o) {
        if(o instanceof Fragment) {
            Fragment f = (Fragment) o;
            return f.getId() == id;
        }
        else return false;
    }

    public int getMaxSizeValues() {
        return maxSizeValues;
    }

    public float getCenterAngle() {
        return centerAngle;
    }

    public int getId() {
        return id;
    }

    public double getAverageAngle() {
        double avgAngle = 0D;
        if (Double.isNaN(averageAngle)) {
            for (RSSIMeasurement rssiMeasurement : rssiMeasurements) {
                avgAngle += rssiMeasurement.getAzimuth();
            }
        } else {
            return averageAngle;
        }
        return avgAngle / rssiMeasurements.size();
    }

    public List<RSSIMeasurement> getRssiMeasurements() {
        return rssiMeasurements;
    }
}
