package com.simons.bluetoothtracker.models;

import android.util.Log;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by gerardsimons on 30/12/13.
 * <p/>
 * Model class for the compass. Contains references to its fragments and the current rotation
 */
public class Compass {

    private static final String TAG = "Compass";
    private List<Fragment> fragments;

    public static final int MIN_RSSI = -130;
    public static final int MAX_RSSI = 0;

    private double azimuth;

    public Compass(int nrOfFragments, int calibrationLimit, int maxValuesSize) {
        fragments = new ArrayList<Fragment>(nrOfFragments);
        float angleDelta = 360F / nrOfFragments;
        float angleStart = angleDelta / 2F;
        for (int i = 0; i < nrOfFragments; i++) {
            fragments.add(new Fragment(calibrationLimit,i,angleStart + angleDelta * i,maxValuesSize));
        }
    }

    /**
     * Sets all fragments to inactive (non-highlighted)
     */
    public void deactivateAllFragments() {
        for (Fragment f : fragments) {
            f.setActive(false);
        }
    }

    public String azimuthValuesToString() {
        String azimuthValuesString = "[";
        for(Fragment fragment : fragments) {
            for(RSSIMeasurement rssiMeasurement : fragment.getRssiMeasurements()) {
                azimuthValuesString += rssiMeasurement.getAzimuth() + " ";
            }
        }
        azimuthValuesString += "]";
        return azimuthValuesString;
    }

    public String rssiValuesToString() {
        String rssiValuesString = "[";
        for(Fragment fragment : fragments) {
            for(RSSIMeasurement rssiMeasurement : fragment.getRssiMeasurements()) {
                rssiValuesString += rssiMeasurement.getRSSI() + " ";
            }
        }
        rssiValuesString += "]";
        return rssiValuesString;
    }

    /**
     * The fragments implement CompassDataSource which is the interface used by the compass view
     * to get its data from. This returns the fragments as these interfaces.
     * @return the fragments as compass data souces
     */
    public CompassDataSource[] getDataSources() {
        CompassDataSource[] dataSources = new CompassDataSource[fragments.size()];
        for(int i = 0 ; i < dataSources.length ; i++) {
            CompassDataSource cds = fragments.get(i);
            dataSources[i] = cds;
        }
        return dataSources;
    }

    /**
     * Checks if all the fragments are calibrated yet
     * @return true if all the fragments are calibrated, false otherwise
     */
    public boolean isCalibrated() {
        for (int i = 0; i < fragments.size(); i++) {
            Fragment fragment = fragments.get(i);
            if (!fragment.isCalibrated()) {
                return false;
            }
        }
        Log.d(TAG, "Compass is completely calibrated.");
        return true;
    }

    public Fragment fragmentForAngle(float azimuth) {
        if (fragments != null && !fragments.isEmpty()) {
            azimuth = 360F - azimuth;
            double bestDistance = Double.MAX_VALUE;
            Fragment closestFragment = null;
            for(Fragment fragment : fragments) {
                double distance = Compass.distanceTo(fragment.getCenterAngle(), azimuth);
//                Log.d(TAG,"Distance fragment #" + fragment.getId() + " has distance " + distance);
                if(distance < bestDistance) {
                    closestFragment = fragment;
                    bestDistance = distance;
                }
            }
//            Log.d(TAG,"Fragment #" + closestFragment.getId() + " has the best distance = " + bestDistance);
            return closestFragment;
        } else {
            return null;
        }
    }

    /**
     * Set the compass azimuth to this value
     * @param newAzimuth the new azimuth to be set
     */
    public void setAzimuth(float newAzimuth) {
        azimuth = newAzimuth;
    }

    /**
     * Return the current azimuth of the compass
     * @return
     */
    public double getAzimuth() {
        return azimuth;
    }

    /**
     *
     * @return the number of fragments that are calibrated
     */
    public int numberOfFragmentsCalibrated() {
        int i = 0;
        for (Fragment fragment : fragments) {
            if (fragment.isCalibrated()) {
                i++;
            }
        }
        return i;
    }

    public List<Fragment> getFragments() {
        return fragments;
    }

    /**
     * Clears the data from all fragments
     */
    public void clearData() {
        for (Fragment f : fragments) {
            f.clearData();
        }
    }

    /**
     * Create an angle within the range of 0,360, -90 should become 270 etc.
     * @param angle
     * @return
     */
    public static float NormalizeAngle(float angle) {
        if(angle < 0) {
            angle += 360F;
        }
        return 360F - angle % 360F;
    }

    /**
     * @return the string representation of this compass. Including fragment toString representations
     */
    public String toString() {
        String s = "CompassController Object with " + numberOfFragmentsCalibrated() + " of "
                + fragments.size() + " fragments calibrated [\n";
        for (Fragment f : fragments) {
            s += f.toString() + "\n";
        }
        s += "]";
        return s;
    }

    /**
     * Exports this compass's values and measurements as a CSV file
     */
    public void export() {
        
    }

    public static float distanceTo(float angle, float otherAngle) {
        double rawDistance = Math.abs(angle - otherAngle);
        double distance = Math.min(rawDistance, 360D - rawDistance);

        return (float)Math.abs(distance);
    }

    public List<RSSIMeasurement> getAllMeasurements() {
        List<RSSIMeasurement> allMeasurements = new ArrayList<RSSIMeasurement>();
        for(Fragment f : fragments) {
            allMeasurements.addAll(f.getRssiMeasurements());
        }
        return allMeasurements;
    }

    public Fragment activateFragmentForAngle(float azimuth) {
        Fragment fragment = fragmentForAngle(azimuth);
        fragment.setActive(true);
        return fragment;
    }
}
