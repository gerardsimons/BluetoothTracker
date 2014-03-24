package com.simons.bluetoothtracker.sqlite;

/**
 * Created by gerardsimons on 24/03/14.
 */

import android.content.ContentValues;
import android.content.Context;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;

public class MeasurementsDataSource {

    // Database fields
    private SQLiteDatabase database;
    private MySQLiteHelper dbHelper;
    private String[] allColumns = { MySQLiteHelper.COLUMN_ID,
            MySQLiteHelper.COLUMN_AZIMUTHS,
            MySQLiteHelper.COLUMN_RSSIS,
            MySQLiteHelper.COLUMN_TIMESTAMPS};

    public MeasurementsDataSource(Context context) {
        dbHelper = new MySQLiteHelper(context);
    }

    public void open() throws SQLException {
        database = dbHelper.getWritableDatabase();
    }

    public void close() {
        dbHelper.close();
    }

    public boolean createMeasurement(int[] azimuths, int[] rssis, long[] timestamps, String user, String remarks) {
        ContentValues values = new ContentValues();

        values.put(MySQLiteHelper.COLUMN_AZIMUTHS, integersToString(azimuths));
        values.put(MySQLiteHelper.COLUMN_RSSIS,integersToString(rssis));
        values.put(MySQLiteHelper.COLUMN_TIMESTAMPS,longsToString(timestamps));
        values.put(MySQLiteHelper.COLUMN_USER,user);
        values.put(MySQLiteHelper.COLUMN_REMARKS,remarks);

        long insertId = database.insert(MySQLiteHelper.TABLE_MEASUREMENTS, null,values);
        return insertId != -1; //-1 is returned when insertion fails.
    }

    public String integersToString(int[] values) {
        String explodedString = "";
        for(int i = 0 ; i < values.length ; i++) {
            explodedString += i;
            if(i != values.length - 1) {
                explodedString += ", ";
            }
        }
        return explodedString;
    }

    public String longsToString(long[] values) {
        String explodedString = "";
        for(int i = 0 ; i < values.length ; i++) {
            explodedString += i;
            if(i != values.length - 1) {
                explodedString += ", ";
            }
        }
        return explodedString;
    }

//    public void deleteComment(Comment comment) {
//        long id = comment.getId();
//        System.out.println("Comment deleted with id: " + id);
//        database.delete(MySQLiteHelper.TABLE_COMMENTS, MySQLiteHelper.COLUMN_ID
//                + " = " + id, null);
//    }
//
//    public List<Comment> getAllComments() {
//        List<Comment> comments = new ArrayList<Comment>();
//
//        Cursor cursor = database.query(MySQLiteHelper.TABLE_COMMENTS,
//                allColumns, null, null, null, null, null);
//
//        cursor.moveToFirst();
//        while (!cursor.isAfterLast()) {
//            Comment comment = cursorToComment(cursor);
//            comments.add(comment);
//            cursor.moveToNext();
//        }
//        // make sure to close the cursor
//        cursor.close();
//        return comments;
//    }

//    private Comment cursorToComment(Cursor cursor) {
//        Comment comment = new Comment();
//        comment.setId(cursor.getLong(0));
//        comment.setComment(cursor.getString(1));
//        return comment;
//    }
}