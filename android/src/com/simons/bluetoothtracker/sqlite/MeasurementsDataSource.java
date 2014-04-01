package com.simons.bluetoothtracker.sqlite;

/**
 * Created by gerardsimons on 24/03/14.
 */

import android.content.ContentValues;
import android.content.Context;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.util.Log;

public class MeasurementsDataSource {

    // Database fields
    private SQLiteDatabase database;
    private MySQLiteHelper dbHelper;

    private static final String TAG = "MeasurementsDataSource";

    public MeasurementsDataSource(Context context) {
        dbHelper = new MySQLiteHelper(context);
    }

    public void open() throws SQLException {
        database = dbHelper.getWritableDatabase();
    }

    public void close() {
        Log.i(TAG,"Close measurements database");
        dbHelper.close();
    }

    public boolean insertMeasurements(Measurements measurements) {
        ContentValues values = new ContentValues();

        values.put(MySQLiteHelper.COLUMN_AZIMUTHS, integersToString(measurements.getAzimuths()));
        values.put(MySQLiteHelper.COLUMN_RSSIS,integersToString(measurements.getRssis()));
        values.put(MySQLiteHelper.COLUMN_TIMESTAMPS,longsToString(measurements.getTimeStamps()));
        values.put(MySQLiteHelper.COLUMN_USER,measurements.getUserName());
        values.put(MySQLiteHelper.COLUMN_REMARKS,measurements.getRemarks());
        values.put(MySQLiteHelper.COLUMN_FRAGMENTS,measurements.getFragments());
        values.put(MySQLiteHelper.COLUMN_CALIBRATION_LIMIT,measurements.getCalibrationLimit());
        values.put(MySQLiteHelper.COLUMN_TRUE_AZIMUTH,measurements.getTrueAzimuth());

        long insertId = database.insert(MySQLiteHelper.TABLE_MEASUREMENTS, null,values);
        return insertId != -1; //-1 is returned when insertion fails.
    }

    public String integersToString(int[] values) {
        String explodedString = "";
        for(int i = 0 ; i < values.length ; i++) {
            explodedString += values[i];
            if(i != values.length - 1) {
                explodedString += ", ";
            }
        }
        return explodedString;
    }

    public String longsToString(long[] values) {
        String explodedString = "";
        for(int i = 0 ; i < values.length ; i++) {
            explodedString += values[i];
            if(i != values.length - 1) {
                explodedString += ", ";
            }
        }
        return explodedString;
    }
}