package com.simons.bluetoothtracker.models;

/**
 * Created by gerardsimons on 02/01/14.
 */
public interface CompassDataSource {


    /**
     * Returns the primary value this data source should supply to the compassview
     * @return
     */
    public double getValue();

    /**
     * ID of the data source (should be unique)
     * @return
     */
    public int getId();

    /**
     *
     * @return the value that was determined after calibration (fixed)
     */
    public double getCalibrationValue();

    /**
     * Number of values measured and still stored, older values may be dropped
     * @return
     */
    public int getNrOfValuesMeasured();

    /**
     * The calibration limit this datasource has implemented
     * @return
     */
    public int getCalibrationLimit();

    /**
     * Whether or not the fragment should be displayed as highlighted
     * @return
     */
    public boolean highlighted();


}
