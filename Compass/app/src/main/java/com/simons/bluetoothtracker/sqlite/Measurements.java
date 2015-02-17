package com.simons.bluetoothtracker.sqlite;

/**
 * Created by gerardsimons on 24/03/14.
 *
 * The SQLite model class for containing all measurements done for calibration
 * May be used as an intermediary wrapper before insertion or after selection for further processing
 */
public class Measurements {

    private long id;
    private int[] rssis; //In range of roughly -180 - -30
    private int[] azimuths; //In range of 0-360
    private int trueAzimuth; //Estimated correct azimuth when pointing to label
    private String userName = "<NO USERNAME>"; //Identifying the user that made the measurements
    private String remarks = "<NO REMARKS>"; //Any additional remarks (possibly about room layout, distance, any difficulties faced)
    private long[] timeStamps; //Each timestamp corresponds to a measurement
    private int fragments; //Number of fragments used for this calibration
    private int calibrationLimit; //The calibration limit used PER fragment

    public int getCalibrationLimit() {
        return calibrationLimit;
    }

    public void setCalibrationLimit(int calibrationLimit) {
        this.calibrationLimit = calibrationLimit;
    }

    public int getFragments() {
        return fragments;
    }

    public void setFragments(int fragments) {
        this.fragments = fragments;
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public int[] getRssis() {
        return rssis;
    }

    public void setRssis(int[] rssis) {
        this.rssis = rssis;
    }

    public int[] getAzimuths() {
        return azimuths;
    }

    public void setAzimuths(int[] azimuths) {
        this.azimuths = azimuths;
    }

    public String getUserName() {
        return userName;
    }

    public void setUserName(String userName) {
        this.userName = userName;
    }

    public String getRemarks() {
        return remarks;
    }

    public void setRemarks(String remarks) {
        this.remarks = remarks;
    }

    public long[] getTimeStamps() {
        return timeStamps;
    }

    public void setTimeStamps(long[] timeStamps) {
        this.timeStamps = timeStamps;
    }

    public Measurements() {

    }

    public int getTrueAzimuth() {
        return trueAzimuth;
    }

    public void setTrueAzimuth(int trueAzimuth) {
        this.trueAzimuth = trueAzimuth;
    }
}
