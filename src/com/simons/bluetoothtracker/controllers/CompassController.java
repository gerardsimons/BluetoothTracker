package com.simons.bluetoothtracker.controllers;

import android.util.Log;

import com.simons.bluetoothtracker.Utilities;
import com.simons.bluetoothtracker.models.Compass;
import com.simons.bluetoothtracker.models.Fragment;
import com.simons.bluetoothtracker.views.CompassView;

import java.util.Collections;
import java.util.Comparator;
import java.util.List;

/**
 * This class is the controller for the CompassView. It takes care of managing
 * the RSSI values and their corresponding measurements angles. Each fragment
 * corresponds to a certain range of angles. When a fragment has enough
 * measurements it is said to be calibrated. When all fragments are calibrated
 * the compass view can be drawn.
 *
 * @author Gerard Simons
 */
public class CompassController {

    private static final String TAG = "CompassController";

    private Compass compass;
    private CompassView compassView;

    private final float alpha = 0.96F;
    private final float threshold = 270F;
    private double lastAngle = Double.NaN;


//	private int checkCalibrationThrottle = 50;
//	private int round = 0;

    private boolean calibrationFinished = false;

    public CompassController(int nrOfFragments, int calibrationLimit, int maxValuesSize, CompassView compassView) {
        this.compass = new Compass(nrOfFragments, calibrationLimit, maxValuesSize);


        this.compassView = compassView;
        compassView.setDataSources(compass.getDataSources());

        compassView.setNumberOfFragments(nrOfFragments);
    }

    public void deactivateAllFragments() {
        compass.deactivateAllFragments();
    }

    public void addData(int rssi, float angle) {
        deactivateAllFragments();
        if (!calibrationFinished) {
            Fragment fragment = compass.fragmentForAngle(angle);
            fragment.setActive(true);

            // Log.d(TAG, "Adding data to compass (rssi,value) = (" + rssi + ","
            // + angle + ")");
            fragment.addValues(rssi, angle);
            if (compass.isCalibrated()) {
                calibrationFinished = true;
                // computePointer(Math.round(fragments.size() / 3F));
                Log.d(TAG, this.toString());
                compassView.setCalibrated();
            }
        } else {

            Fragment fragment = compass.fragmentForAngle(angle);
            fragment.setActive(true);

            double oldValue = fragment.getValue();


            fragment.addValues(rssi, angle);

            double newValue = fragment.getValue();
            double delta = newValue - oldValue;

            Log.d(Fragment.TAG,fragment.toString());

            Log.d(TAG,"Old RSSI Value = " + oldValue);
            Log.d(TAG,"New RSSI Value = " + newValue);
            Log.d(TAG,"Delta RSSI Value = " + delta);

            /* PROPOGATE VALUES TO OTHER FRAGMENTS */

            //Also update other fragments
            List<Fragment> allFragments = compass.getFragments();
            for(Fragment f : allFragments) {
                if(!f.equals(fragment)) {
                    double distance = f.distanceTo(angle);
                    double weight = 1 - distance / 90D;
                    double value = f.getCalibrationValue();

                    Log.d(TAG,"Calibrated RSSI = " + value);
                    Log.d(TAG,"Distance = " + distance);
                    Log.d(TAG,"Weight = " + weight);

                    f.addValues(value + weight * delta,f.getAverageAngle());
                }
                else {
                    Log.d(TAG,f.getId() + " == " + fragment.getId());
                }
            }
        }
    }

    private void sortFragments() {
        if (compass != null) {
            List<Fragment> fragments = compass.getFragments();
            if (fragments != null) {
                Collections.sort(fragments, new Comparator<Fragment>() {
                    public int compare(Fragment s1, Fragment s2) {
                        return (int) Math.round(s1.getValue()
                                - s2.getValue());
                    }
                });
                Log.d(TAG, "Sorted Fragments = \n" + Utilities.listToString(fragments));
            }
        }
    }

    /**
     * Take average angle of the N best fragments weighted by their comparative
     * rssi values.
     */
    private double computePointer(int N) {
        if (compass != null) {
            List<Fragment> fragments = compass.getFragments();
            if (fragments != null && !fragments.isEmpty()) {
                // Sort the fragments
                Log.d(TAG, "Computing pointer.");
                sortFragments();
                double bestRSSI = fragments.get(0).getValue();
                Log.d(TAG, "Best RSSI = " + bestRSSI);
                double angle = 0D;
                double ratioSum = 0D;
                // Average angle.
                for (int i = 0; i < N; i++) {
                    Fragment f = fragments.get(i);
                    double ratio = f.getValue() / bestRSSI;
                    double avgAngle = f.getAverageAngle();
                    angle += avgAngle * ratio;
                    ratioSum += ratio;
                }
                Log.d(TAG, "Angle Sum = " + ratioSum);
                Log.d(TAG, "Ratio Sum = " + ratioSum);
                angle /= ratioSum;
                return angle;
            }
        }
        return Double.NaN;
    }

    public void setRotation(float angle) {
        if(!Double.isNaN(lastAngle)) {
            if(Math.abs(angle - lastAngle) < threshold) {
                angle = (float)(alpha * lastAngle + (1.0 - alpha) * angle);
            }
            compass.setAzimuth(angle);
            compassView.setRotation(angle);
        }
        else {
            compass.setAzimuth(angle);
            compassView.setRotation(angle);
        }
        lastAngle = angle;
    }

    public void clearData() {
        compass.clearData();
    }
}
