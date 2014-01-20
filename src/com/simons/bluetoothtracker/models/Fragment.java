package com.simons.bluetoothtracker.models;

import com.simons.bluetoothtracker.Utilities;

import java.util.ArrayList;
import java.util.List;

public class Fragment implements CompassDataSource {

    public static final String TAG = "Fragment";

    private int maxSizeValues;

    private List<Double> rssiValues;
    private List<Double> angles;
    private List<Long> timeStamps;

    private int id;

    private double centerAngle;

    private boolean active;

    private int calibrationLimit;

    private boolean calibrated = false;
    private double calibrationValue;

    private double averageAngle = Double.NaN;

    public Fragment(int calibrationLimit, int id, double centerAngle, int maxSizeValues) {
        //Make sure it can store at least as many values as it needs for calibration.
        if(maxSizeValues < calibrationLimit) {
            maxSizeValues += (calibrationLimit - maxSizeValues);
        }
        this.maxSizeValues = maxSizeValues;
        this.centerAngle = centerAngle;
        this.calibrationLimit = calibrationLimit;
        rssiValues = new ArrayList<Double>();
        angles = new ArrayList<Double>();
        this.id = id;
        timeStamps = new ArrayList<Long>();
    }

    /**
     * Gets the value that represents this fragment, this could be average or
     * something else, though default is average. Future implementation could be
     * weighted averages
     *
     * @return double the value, double is used to ensure sufficient accuracy
     * allowing for multiple types of calculations
     */
    public double getValue() {
        if(true || !isCalibrated()) {
            return averageRSSI();
        }
        else return weightedTimeAverageRSSI();
    }

    @Override
    public double getCalibrationValue() {
        return calibrationValue;
    }


    private float weightedTimeAverageRSSI() {
        float value = 0;
        Long oldestTime = null;
        Long newestTime = timeStamps.get(timeStamps.size() - 1);

        long tInterval = 0L;

        int N = rssiValues.size();

        for(int i = 0 ; i < rssiValues.size() ; i++) {
            Double rssiValue = rssiValues.get(i);
            if(rssiValue != null) {
                if(oldestTime == null) {
                    oldestTime = timeStamps.get(i);
                    tInterval = oldestTime - newestTime;
                }
                else {
                    value += (timeStamps.get(i) - oldestTime) / tInterval;
                }
            }
        }
        return value;
    }

    @Override
    public int getNrOfValuesMeasured() {
        return calibrationLimit - rssiValues.size();
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
        s += Utilities.listToString(rssiValues) + "\n";
        return s;
    }

    private double averageRSSI() {
        if (!rssiValues.isEmpty()) {
            int sum = 0;
            for (double i : rssiValues) {
                sum += i;
            }
            return sum / (double) rssiValues.size();
        } else
            return Double.NaN;
    }

    public void addValues(double rssi, double angle) {
        rssiValues.add(rssi);
        angles.add(angle);
        timeStamps.add(System.nanoTime());

//        Log.d(TAG, "Fragment adding values (rssi,angle): " + rssi + ", " + angle);
//        Log.d(TAG, "Values size : " + rssiValues.size());

        if(angles.size() > maxSizeValues) {

            angles.remove(0);
            rssiValues.remove(0);
        }
    }

    public boolean isCalibrated() {
        // Log.d(TAG, "rssiValues.size() = " + rssiValues.size());
        // Log.d(TAG, "calibrationLimit = " + calibrationLimit);
        if(calibrated || rssiValues.size() >= calibrationLimit) {
            if(!calibrated) //First time it is calibrated
            {
                calibrationValue = getValue();
            }
            calibrated = true;
        }
        return calibrated;
    }

    public void setActive(boolean newActive) {
        active = newActive;
    }

    public double distanceTo(double angle) {
        double rawDistance = rawDistanceTo(angle);
        double distance = Math.min(rawDistance, 360D - rawDistance);

        return Math.abs(distance);
    }

    public double rawDistanceTo(double angle) {
        return Math.abs(centerAngle - angle);
    }

    public void clearData() {
        rssiValues.clear();
        angles.clear();
    }

    public boolean equals(Object o) {
        if(o instanceof Fragment) {
            Fragment f = (Fragment) o;
            return f.getId() == id;
        }
        else return false;
    }

    public double getCenterAngle() {
        return centerAngle;
    }

    public int getId() {
        return id;
    }

    public double getAverageAngle() {
        double avgAngle = 0D;
        if (Double.isNaN(averageAngle)) {
            for (double angle : angles) {
                avgAngle += angle;
            }
        } else {
            return averageAngle;
        }
        return avgAngle / angles.size();
    }

    public double getLastRssiValue() {
        if(rssiValues != null && !rssiValues.isEmpty())
            return rssiValues.get(rssiValues.size() - 1);
        else return Double.NaN;
    }
}
