package com.simons.gerard.testapp;

/**
 * Created by Gerard on 9-2-2015.
 */
import android.content.Context;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;
import android.util.Log;

public class SQLiteHelper extends SQLiteOpenHelper {

    public static final String TABLE_MEASUREMENTS = "measurements";

    public static final String COLUMN_ID = "id";
    public static final String COLUMN_PATH_ID = "id";
    public static final String COLUMN_LONGITUDE = "longitude";
    public static final String COLUMN_LATITUDE = "latitude";
    public static final String COLUMN_ACCURACY = "accuracy";

    private static final String DATABASE_NAME = "temps.db";
    private static final int DATABASE_VERSION = 1;

    // Database creation sql statement
    private static final String DATABASE_CREATE = "create table "
            + TABLE_MEASUREMENTS + "(" + COLUMN_ID
            + " integer primary key autoincrement, " + COLUMN_PATH_ID
            + " integer not null" + COLUMN_LONGITUDE
            + " real not null, " + COLUMN_LATITUDE
            + " real not null, " + COLUMN_ACCURACY
            + " real not null);";

    public SQLiteHelper(Context context) {
        super(context, DATABASE_NAME, null, DATABASE_VERSION);
    }

    @Override
    public void onCreate(SQLiteDatabase database) {
        database.execSQL(DATABASE_CREATE);
    }

    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
        Log.w(SQLiteHelper.class.getName(),
                "Upgrading database from version " + oldVersion + " to "
                        + newVersion + ", which will destroy all old data");
        db.execSQL("DROP TABLE IF EXISTS " + TABLE_MEASUREMENTS);
        onCreate(db);
    }


}
