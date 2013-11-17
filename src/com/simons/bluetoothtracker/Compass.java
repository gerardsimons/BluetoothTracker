package com.simons.bluetoothtracker;

import java.util.ArrayList;
import java.util.List;

import android.util.Log;

import com.simons.bluetoothtracker.views.CompassView;

/**
 * This class is the controller for the CompassView. It takes care of managing
 * the RSSI values and their corresponding measurements angles. Each fragment
 * corresponds to a certain range of angles. When a fragment has enough
 * measurements it is said to be calibrated. When all fragments are calibrated
 * the compass view can be drawn.
 * 
 * @author Gerard Simons
 * 
 */
public class Compass {

    private static final String TAG = null;
    private CompassView compassView;
    private List<Fragment> fragments;

    private int checkCalibrationThrottle = 50;
    private int round = 0;

    private boolean calibrationFinished = false;

    public Compass(CompassView compassView, int nrOfFragments, int calibrationLimit) {
	fragments = new ArrayList<Fragment>(nrOfFragments);

	compassView.setNumberOfFragments(nrOfFragments);
	this.compassView = compassView;
	for (int i = 0; i < nrOfFragments; i++) {
	    fragments.add(new Fragment(calibrationLimit));
	}

	compassView.setData(fragments);
    }

    public void setAllFragmentsInactive() {
	for (Fragment f : fragments) {
	    f.setActive(false);
	}
    }

    public void addData(int rssi, float angle) {
	setAllFragmentsInactive();
	if (!calibrationFinished) {
	    Fragment fragment = fragmentForAngle(angle);
	    fragment.setActive(true);
	    Log.d(TAG, "Adding data to compass (rssi,value) = (" + rssi + "," + angle + ")");
	    fragment.addValues(rssi, angle);
	    if (isCalibrated()) {
		Log.d(TAG, "OMG ITS CALIBRATED!");
		calibrationFinished = true;
		Log.d(TAG, this.toString());
		round++;
		compassView.setCalibrated();
	    } else if (round % checkCalibrationThrottle == 0) {
		Log.d(TAG, "fragments calibrated = " + fragmentsCalibrated() + " / " + fragments.size());
		round = 0;
	    }
	}
    }

    public String toString() {
	String s = "Compass Object with " + fragmentsCalibrated() + " of " + fragments.size() + " fragments calibrated [\n";
	for (Fragment f : fragments) {
	    s += f.toString() + "\n";
	}
	s += "]";
	return s;
    }

    private int fragmentIndexForAngle(float angle) {
	angle += 90F;
	angle = angle % 360F;
	angle = 360F - angle;
	return (int) Math.round(angle / 360F * (fragments.size() - 1));
    }

    private Fragment fragmentForAngle(float angle) {
	if (fragments != null && !fragments.isEmpty()) {
	    int index = fragmentIndexForAngle(angle);
	    return fragments.get(index);
	} else
	    return null;
    }

    public void setRotation(float angle) {
	compassView.setRotation(angle);
    }

    public int fragmentsCalibrated() {
	int i = 0;
	for (Fragment fragment : fragments) {
	    if (fragment.isCalibrated()) {
		i++;
	    }
	}
	return i;
    }

    public boolean isCalibrated() {
	for (int i = 0; i < fragments.size(); i++) {
	    Fragment fragment = fragments.get(i);
	    if (!fragment.isCalibrated()) {
		Log.d(TAG, "FRAGMENT #" + i + " IS NOT CALIBRATED");
		return false;
	    } else
		Log.d(TAG, "FRAGMENT #" + i + " IS CALIBRATED");
	}
	Log.d(TAG, "Compass is calibrated.");
	return true;
    }

    public void clearData() {
	for (Fragment fragment : fragments) {
	    fragment.clearData();
	}
    }
}
