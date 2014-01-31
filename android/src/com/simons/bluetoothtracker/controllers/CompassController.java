package com.simons.bluetoothtracker.controllers;

import android.util.Log;

import com.simons.bluetoothtracker.Utilities;
import com.simons.bluetoothtracker.models.Compass;
import com.simons.bluetoothtracker.models.Fragment;
import com.simons.bluetoothtracker.models.RSSIMeasurement;
import com.simons.bluetoothtracker.views.CompassView;

import java.util.ArrayList;
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

    private float alpha = 0.96F;
    private final float threshold = 270F;
    private double lastAngle = Double.NaN;

    private Pointer pointer;

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

    public void addData(int[] rssiValues, float[] azimuthValues) {
        if(rssiValues.length != azimuthValues.length) {
            Log.e(TAG,"rssiValues length should match azimuthValues length");
            return;
        }
        for(int i = 0 ; i < rssiValues.length ; i++) {
            addData(rssiValues[i],azimuthValues[i]);
        }
    }

    public void addData(int rssi, float azimuth) {
        azimuth = Compass.NormalizeAngle(azimuth);
        deactivateAllFragments();
        if (!calibrationFinished) {
            Fragment fragment = compass.fragmentForAngle(azimuth);
            fragment.setActive(true);

            Log.d(TAG, "Adding data to compass (rssi,value) = (" + rssi + "," + azimuth + ")");
            fragment.addValues(rssi, azimuth);
            if (compass.isCalibrated()) {
                calibrationFinished = true;

                Log.d(TAG, this.toString());

                Pointer pointer = computePointer();

                compassView.setDataSources(compass.getDataSources());
                compassView.setPointer(pointer);
                compassView.setCalibrated();
            }
        } else {
            Fragment fragment = compass.fragmentForAngle(azimuth);
            fragment.setActive(true);

            //TODO: Update the pointer direction and color wise
            if(pointer != null) {
                pointer.update(rssi,azimuth);
            }


//            double oldValue = fragment.getLastRssiValue();
//
//            fragment.addValues(rssi, angle);
//
//            double newValue = fragment.getValue();
//            double delta = rssi - oldValue;
//
//            Log.d(TAG,"Receiving fragment #" + fragment.getId());
//            Log.d(Fragment.TAG,fragment.toString());
//            Log.d(TAG,"Old RSSI Value = " + oldValue);
//            Log.d(TAG,"New RSSI Value = " + newValue);
//            Log.d(TAG,"Delta RSSI Value = " + delta);
//
//            /* PROPAGATE VALUES TO OTHER FRAGMENTS */
//
//            //Also update other fragments
//            List<Fragment> allFragments = compass.getFragments();
//            for(Fragment f : allFragments) {
//                if(!f.equals(fragment)) {
//                    double distance = f.distanceTo(fragment.getCenterAngle());
//                    double weight = 1 - distance / 90D;
//                    double value = f.getLastRssiValue();
//                    Log.d(TAG,"Fragment ID = " + f.getId());
//                    Log.d(TAG,"Last RSSI Value = " + value);
//                    Log.d(TAG,"Distance = " + distance);
//                    Log.d(TAG,"Weight = " + weight);
//
//
//                    f.addValues(f.getLastRssiValue() + delta,f.getAverageAngle());
//                }
//            }
//            compass.printRSSIValues();
//            compass.printAngles();
        }
        compassView.invalidate();
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
     *
     * rssi values.
     */
    public Pointer computePointer() {
        if (compass != null) {
            List<Fragment> fragments = compass.getFragments();
            if (fragments != null && !fragments.isEmpty()) {
                Log.d(TAG, "Computing pointer.");
                long startTime = System.nanoTime();

                Double maxRSSI = null;
                Double minRSSI = null;

                //The size of this list should be nr of fragments * max size values, although this could
                //vary from fragment to fragment it is a decent estimation
                List<RSSIMeasurement> rssiMeasurements = new ArrayList<RSSIMeasurement>(fragments.get(0).getMaxSizeValues() * fragments.size());

                for(Fragment f : fragments) {
                    for(RSSIMeasurement measurement : f.getRssiMeasurements()) {
                        double rssi = measurement.getRSSI();
                        if(maxRSSI == null || rssi > maxRSSI) {
                            maxRSSI = rssi;
                        }
                        else if (minRSSI == null || rssi < minRSSI){
                            minRSSI = rssi;
                        }
                        rssiMeasurements.add(measurement);
                    }
                }

                double deltaRSSI = maxRSSI - minRSSI;

                Log.d(TAG, "Max RSSI = " + maxRSSI);
                Log.d(TAG, "Min RSSI = " + minRSSI);
                Log.d(TAG, "Delta RSSI = " + deltaRSSI);

                double[] weights = new double[rssiMeasurements.size()];
                double[] xDir = new double[rssiMeasurements.size()];
                double[] yDir = new double[rssiMeasurements.size()];
                float sumWeights = 0;

                //Compute weights
                for(int i = 0 ; i < rssiMeasurements.size() ; i++) {
                    double rssiValue = rssiMeasurements.get(i).getRSSI();
                    double weight = (rssiValue - minRSSI) / deltaRSSI;

                    weight = weightFunction(weight);

                    weights[i] = weight;
                    sumWeights += weight;
                }

                float averageRSSI = 0;
                float sumSquares = 0;

                //Compute average unit vector and weighted standard deviation
                for(int i = 0 ; i < rssiMeasurements.size() ; i++) {
                    double azimuth = Math.toRadians(rssiMeasurements.get(i).getAzimuth());
                    int rssi = rssiMeasurements.get(i).getRSSI();
                    double weight = weights[i];

                    float delta = rssi - averageRSSI;

                    sumSquares += weights[i] * delta * delta;

                    averageRSSI += weight * rssi;

                    xDir[i] = Math.cos(azimuth) * weight;
                    yDir[i] = Math.sin(azimuth) * weight;
                }
                averageRSSI /= sumWeights;

                //TODO : Variance calculation not working!
                Log.d(TAG,"Sum Squares = " + sumSquares );

                sumSquares = (float) Math.sqrt(sumSquares / sumWeights);
                float varianceRSSI = (float) Math.sqrt(sumSquares);

                //From simulations run using MATLAB I found that a truly random distribution (the least informative one)
                //gave a standard deviation of around 20.
                int pointerWidth = Math.max(Pointer.MIN_WIDTH, Math.round(varianceRSSI / 20 * 360));

                Log.d(TAG,"Pointer Width = " + pointerWidth );
                Log.d(TAG,"rssis = " + compass.rssiValuesToString());
                Log.d(TAG,"azimuths = " + compass.azimuthValuesToString());
                Log.d(TAG,"weights = " + Utilities.arrayToString(xDir));
                Log.d(TAG,"xDir = " + Utilities.arrayToString(xDir));
                Log.d(TAG,"yDir = " + Utilities.arrayToString(yDir));
                float avgAngleRadians = (float) (Math.atan2(Utilities.mean(yDir),Utilities.mean(xDir)));
                Log.d(TAG,"Average Angle (radians) = " + avgAngleRadians);
                float averageAngle = (float) Math.toDegrees(avgAngleRadians);
                Log.d(TAG,"Average Angle (degrees) = " + averageAngle);
                averageAngle = Compass.NormalizeAngle(averageAngle);
                Log.d(TAG,"Average Angle (degrees & normalized) = " + averageAngle);
                Log.d(TAG,"Variance RSSI = " + varianceRSSI );
                Log.d(TAG,"Pointer Width = " + pointerWidth );
                Log.d(TAG,"averageRSSI = " + averageRSSI);

                //Check whether this or the mirrored version is best.
                for(RSSIMeasurement measurement : rssiMeasurements) {

                }

//                float pointerCenterAngle = Compass.NormalizeAngle(averageAngle);
//                float pointerCenterAngle = averageAngle;
//                Log.d(TAG,"pointerCenterAngle = " + pointerCenterAngle);

                long timePassed = System.nanoTime() - startTime;
                Log.d(TAG,"Computing pointer finished in " + timePassed / 1000000000D + " seconds.");

                return new Pointer(averageAngle,averageRSSI);
            }
        }
        return null;
    }

    //Some magical function that gives more attention to good values by transforming the weights correctly.
    public double weightFunction(double weight) {
//        return weight * weight;
        return weight;
    }

    public void setRotation(float azimuth) {
        deactivateAllFragments();
        //I think below is wrong for the azimuth
        azimuth = Compass.NormalizeAngle(azimuth);
        if(!Double.isNaN(lastAngle)) {
            if(Math.abs(azimuth - lastAngle) < threshold) {
                azimuth = (float)(alpha * lastAngle + (1.0 - alpha) * azimuth);
            }
            compass.fragmentForAngle(azimuth).setActive(true);
            compass.setAzimuth(azimuth);
            compassView.setRotation(azimuth);
        }
        else {
            compass.setAzimuth(azimuth);
            compassView.setRotation(azimuth);
        }
        lastAngle = azimuth;
    }

    public void clearData() {
        compass.clearData();
    }

    public void setFilterAlpha(float newAlpha) {
        if(newAlpha <= 1F && newAlpha >= 0F) {
            alpha = newAlpha;
        }
        else {
            Log.e(TAG,"Invalid alpha coefficient.");
        }
    }

    public void exportCompassData() {

    }
}
