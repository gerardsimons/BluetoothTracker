package com.simons.bluetoothtracker.models;

import android.util.Log;

import com.simons.bluetoothtracker.CompassDataSource;
import com.simons.bluetoothtracker.Fragment;

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

    private double azimuth;

    public Compass(int nrOfFragments, int calibrationLimit, int maxValuesSize) {
        fragments = new ArrayList<Fragment>(nrOfFragments);
        double angleDelta = 360D / nrOfFragments;
        double angleStart = angleDelta / 2D;
        for (int i = 0; i < nrOfFragments; i++) {
            fragments.add(new Fragment(calibrationLimit,i,angleStart + angleDelta * i,maxValuesSize));
        }
    }

    public void deactivateAllFragments() {
        for (Fragment f : fragments) {
            f.setActive(false);
        }
    }

    public CompassDataSource[] getDataSources() {
        CompassDataSource[] dataSources = new CompassDataSource[fragments.size()];
        for(int i = 0 ; i < dataSources.length ; i++) {
            CompassDataSource cds = fragments.get(i);
            dataSources[i] = cds;
        }
        return dataSources;
    }

    public boolean isCalibrated() {
        for (int i = 0; i < fragments.size(); i++) {
            Fragment fragment = fragments.get(i);
            if (!fragment.isCalibrated()) {
//                Log.d(TAG, "FRAGMENT #" + i + " IS NOT CALIBRATED");
                return false;
            } else {
//                Log.d(TAG, "FRAGMENT #" + i + " IS CALIBRATED");
            }
        }
        Log.d(TAG, "Compass is completely calibrated.");
        return true;
    }

    private int fragmentIndexForAngle(float angle) {
        angle += 90F;
        angle = angle % 360F;
        angle = 360F - angle;
        return Math.round(angle / 360F * (fragments.size() - 1));
    }

    public Fragment fragmentForAngle(float angle) {
        if (fragments != null && !fragments.isEmpty()) {
            int index = fragmentIndexForAngle(angle);
            return fragments.get(index);
        } else
            return null;
    }

    public void setAzimuth(float newAzimuth) {
        azimuth = newAzimuth;
    }

    public double getAzimuth() {
        return azimuth;
    }

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

    public void clearData() {
        for (Fragment f : fragments) {
            f.clearData();
        }
    }

    public String toString() {
        String s = "CompassController Object with " + numberOfFragmentsCalibrated() + " of "
                + fragments.size() + " fragments calibrated [\n";
        for (Fragment f : fragments) {
            s += f.toString() + "\n";
        }
        s += "]";
        return s;
    }
}
