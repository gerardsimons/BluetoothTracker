package com.simons.bluetoothtracker.sqlite;

/**
 * Created by gerardsimons on 24/03/14.
 */
import android.content.Context;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;
import android.util.Log;

public class MySQLiteHelper extends SQLiteOpenHelper {

    public static final String TABLE_MEASUREMENTS = "measurements";

    public static final String COLUMN_ID = "_id";
    public static final String COLUMN_RSSIS = "rssis";
    public static final String COLUMN_AZIMUTHS = "azimuths";
    public static final String COLUMN_TIMESTAMPS = "timestamps";
    public static final String COLUMN_USER = "user";
    public static final String COLUMN_REMARKS = "remarks";
    public static final String COLUMN_FRAGMENTS = "fragments";
    public static final String COLUMN_CALIBRATION_LIMIT = "calibration_limit";
    public static final String COLUMN_TRUE_AZIMUTH = "true_azimuth";
    public static final String COLUMN_CREATION_DATE = "creation_date";

    private static final String DATABASE_NAME = "measurements.db";
    private static final int DATABASE_VERSION = 1;

    // Database creation sql statement
    private static final String DATABASE_CREATE = "create table "
            + TABLE_MEASUREMENTS + "(" + COLUMN_ID
            + " integer primary key autoincrement, " + COLUMN_RSSIS
            + " text not null, " + COLUMN_AZIMUTHS
            + " text not null, " + COLUMN_TIMESTAMPS
            + " text not null, " + COLUMN_USER
            + " text not null, " + COLUMN_REMARKS
            + " text not null, " + COLUMN_FRAGMENTS
            + " integer not null, " + COLUMN_CALIBRATION_LIMIT
            + " integer not null, " + COLUMN_TRUE_AZIMUTH
            + " integer not null, " + COLUMN_CREATION_DATE
            + " timestamp default current_timestamp);";

    public MySQLiteHelper(Context context) {
        super(context, DATABASE_NAME, null, DATABASE_VERSION);
    }

    @Override
    public void onCreate(SQLiteDatabase database) {
        database.execSQL(DATABASE_CREATE);
    }

    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
        Log.w(MySQLiteHelper.class.getName(),
                "Upgrading database from version " + oldVersion + " to "
                        + newVersion + ", which will destroy all old data");
        db.execSQL("DROP TABLE IF EXISTS " + TABLE_MEASUREMENTS);
        onCreate(db);
    }

}
