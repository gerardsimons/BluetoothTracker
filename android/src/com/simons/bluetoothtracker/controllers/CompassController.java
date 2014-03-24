package com.simons.bluetoothtracker.controllers;

import android.util.Log;

import com.simons.bluetoothtracker.CompassSettings;
import com.simons.bluetoothtracker.Utilities;
import com.simons.bluetoothtracker.models.Compass;
import com.simons.bluetoothtracker.models.Fragment;
import com.simons.bluetoothtracker.models.Pointer;
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

    //Value should be atleast this much more as the pointer in order to move it.
    private final int rssiTreshold = 2;
    private final int significantReadingsRequired = 10;
    private List<RSSIMeasurement> significantReadings;
    private boolean lastMeasurementWasInPointer = false;

    private CompassSettings compassSettings;

    //How far should measurements be propagated? on a 0-180 interval, the fragment containing the
    //measurement will always update regardless of this value.
    private float propagationDistance = 90F;


//	private int checkCalibrationThrottle = 50;
//	private int round = 0;

    private boolean calibrationFinished = false;

    public CompassController(CompassSettings settings, CompassView compassView) {
        this.compass = new Compass(settings.nrOfFragments, settings.calibrationLimit, settings.maxValuesPerFragment);
        this.compassSettings = settings;
        significantReadings = new ArrayList<RSSIMeasurement>();

        compassView.setDrawColoredCompass(settings.showColors);
        compassView.setDrawPointer(settings.showPointer);
        compassView.setDrawDebugText(settings.showDebugText);

        compassView.setDataSources(compass.getDataSources());
        compassView.setNumberOfFragments(settings.nrOfFragments);

        this.compassView = compassView;
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
//        azimuth = Compass.NormalizeAngle(azimuth);
        RSSIMeasurement rssiMeasurement = new RSSIMeasurement(rssi,azimuth);

        deactivateAllFragments();

        Fragment activeFragment = compass.activateFragmentForAngle(azimuth);

//        Log.d(TAG, "Adding data to compass (rssi,value) = (" + rssi + "," + azimuth + ")");



        if (!calibrationFinished) {
            activeFragment.addValues(rssiMeasurement);
            if (compass.isCalibrated()) {
                calibrationFinished = true;

                Log.d(TAG, this.toString());

                List<RSSIMeasurement> allMeasurements = compass.getAllMeasurements();

                compassView.setDataSources(compass.getDataSources());

                if(compassSettings.showPointer) {
                    pointer = computePointer(allMeasurements);
                    compassView.setPointer(pointer);
                }
                compassView.setCalibrated();

                Log.i(TAG,"Calibration complete.");
            }
        } else if(compassSettings.showPointer){
            activeFragment.addValues(rssiMeasurement);
            updatePointer(rssiMeasurement);
        } else if(compassSettings.showColors) {
            propagateValues(activeFragment,rssi, azimuth);
        }
        compassView.invalidate();
    }

    /**
     * Update (calibrated) fragments according to latest values.
     * @param receivingFragment The fragment that contains the measurement
     * @param RSSI the latest read RSSI value
     * @param azimuth the latest received compass bearings azimuth
     */
    private void propagateValues(Fragment receivingFragment, int RSSI, float azimuth) {
            //Also update other fragments
        Log.d(TAG,"#####        PROPAGATING VALUES          #####");
        Log.d(TAG,"Azimuth = " + azimuth);
        List<Fragment> allFragments = compass.getFragments();
        for(Fragment f : allFragments) {
            float distance = compass.distanceTo(f.getCenterAngle(), azimuth);
            float weight = 1 - distance / propagationDistance;
            weight = Math.max(weight, 0);
            float value = f.getValue();
            float delta = RSSI - f.getValue();

            Log.d(TAG, "Fragment ID = " + f.getId());
            Log.d(TAG, "Center angle = " + f.getCenterAngle());
            Log.d(TAG, "Last RSSI Value = " + value);
            Log.d(TAG, "Delta = " + delta);
            Log.d(TAG, "Distance = " + distance);
            Log.d(TAG, "Weight = " + weight);

            if (weight > 0) {
                f.addValues(new RSSIMeasurement((value + weight * delta)));
            } else if (f.equals(receivingFragment)) { //Always update the fragment that was active
                f.addValues(new RSSIMeasurement((value + delta)));
            } else {
                Log.d(TAG, "Fragment has weight 0 for this RSSI measurement.");
            }
        }
//        compass.printRSSIValues();
//        compass.printAngles();
    }

    private void updatePointer(RSSIMeasurement rssiMeasurement) {
        if(pointer != null) {
            if(pointer.contains(rssiMeasurement.getAzimuth())) {
                if(!lastMeasurementWasInPointer) {
                    significantReadings.clear();
                    lastMeasurementWasInPointer = true;
                }
                pointer.addMeasurement(rssiMeasurement);
                pointer.computeValue();
                significantReadings.add(rssiMeasurement);
                if(significantReadings.size() == significantReadingsRequired) { //Update pointer
                    pointer = computePointer(significantReadings);
                    compassView.setPointer(pointer);
                    significantReadings.clear();
                }
            }
            else {
                if(lastMeasurementWasInPointer) {
                    significantReadings.clear();
                    lastMeasurementWasInPointer = false;
                }
                int rssiDelta = Math.round(rssiMeasurement.getRSSI() - pointer.getValue());
                Log.d(TAG,"RSSI Delta = " + rssiDelta);
                if(rssiDelta >= rssiTreshold) {
                    significantReadings.add(rssiMeasurement);
                    if(significantReadings.size() == significantReadingsRequired) { //Update pointer
                        Log.d(TAG,"Creating new pointer.");
                        pointer = computePointer(significantReadings);
//                        pointer = computePointer(compass.getAllMeasurements());
                        compassView.setPointer(pointer);
                        significantReadings.clear();
                    }
                }
            }
        }
        else {
            Log.e(TAG,"Pointer is null.");
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
     *
     * rssi values.
     */
    public Pointer computePointer(List<RSSIMeasurement> rssiMeasurements) {
        if (rssiMeasurements != null) {
                Log.d(TAG, "Computing pointer.");
                long startTime = System.nanoTime();

                Double maxRSSI = null;
                Double minRSSI = null;

                //The size of this list should be nr of fragments * max size values, although this could
                //vary from fragment to fragment it is a decent estimation
//                List<RSSIMeasurement> rssiMeasurements = new ArrayList<RSSIMeasurement>(fragments.get(0).getMaxSizeValues() * fragments.size());

                //Determine max and min
                for(RSSIMeasurement measurement : rssiMeasurements) {
                    double rssi = measurement.getRSSI();
                    if(maxRSSI == null || rssi > maxRSSI) {
                        maxRSSI = rssi;
                    }
                    else if (minRSSI == null || rssi < minRSSI){
                        minRSSI = rssi;
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
                    double weight = 1D;

                    if(deltaRSSI != 0)
                        weight = (rssiValue - minRSSI) / deltaRSSI;

                    weight = weightFunction(weight);

                    weights[i] = weight;
                    sumWeights += weight;
                }

                float averageRSSI = 0;
                float sumSquares = 0;

                //Compute weighted average unit vector and weighted standard deviation
                for(int i = 0 ; i < rssiMeasurements.size() ; i++) {
                    double azimuth = Math.toRadians(rssiMeasurements.get(i).getAzimuth());
                    float rssi = rssiMeasurements.get(i).getRSSI();
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
//                averageAngle = Compass.NormalizeAngle(averageAngle);
                Log.d(TAG,"Average Angle (degrees & normalized) = " + averageAngle);
                Log.d(TAG,"Variance RSSI = " + varianceRSSI );
                Log.d(TAG,"Pointer Width = " + pointerWidth );
                Log.d(TAG,"averageRSSI = " + averageRSSI);

//                float pointerCenterAngle = Compass.NormalizeAngle(averageAngle);
//                float pointerCenterAngle = averageAngle;
//                Log.d(TAG,"pointerCenterAngle = " + pointerCenterAngle);

                Pointer newPointer = new Pointer(averageAngle,averageRSSI);

                //Find the measurements within the pointer
                List<RSSIMeasurement> allMeasurements = new ArrayList<RSSIMeasurement>();
                for(RSSIMeasurement rssiMeasurement : rssiMeasurements) {
                    if(newPointer.contains(rssiMeasurement.getAzimuth())) {
                        newPointer.addMeasurement(rssiMeasurement);
                    }
                }

                long timePassed = System.nanoTime() - startTime;
                Log.d(TAG,"Computing pointer finished in " + timePassed / 1000000000D + " seconds.");

                return newPointer;
            }

        return null;
    }

    public double weightFunction(double weight) {
//        return weight * weight;
        return weight;
    }

    public void setRotation(float azimuth) {
//        azimuth = -azimuth;
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

    public void setCompassViewSettings(CompassSettings compassViewSettings) {
        compassView.setDrawDebugText(compassViewSettings.showDebugText);
        compassView.setDrawPointer(compassViewSettings.showPointer);
        compassView.setDrawColoredCompass(compassViewSettings.showColors);
    }
}
