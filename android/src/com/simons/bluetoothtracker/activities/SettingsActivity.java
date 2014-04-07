package com.simons.bluetoothtracker.activities;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.text.InputType;
import android.view.View;
import android.widget.Button;
import android.widget.CompoundButton;
import android.widget.EditText;
import android.widget.Switch;

import com.simons.bluetoothtracker.BluetoothTrackerApplication;
import com.simons.bluetoothtracker.settings.CompassSettings;
import com.simons.bluetoothtracker.R;

import java.security.InvalidKeyException;

/**
 * Created by gerardsimons on 12/02/14.
 */
public class SettingsActivity extends Activity implements View.OnClickListener, CompoundButton.OnCheckedChangeListener {

    private BluetoothTrackerApplication application;
    private SharedPreferences preferences;

    private CompassSettings compassSettings;

    public static final int SETTINGS_REQUEST_CODE = 1;
    public static final String INT_VALUES_CHANGED_KEY = "INT_VALUES_CHANGED";

    private boolean intValuesChanged = false;

    private int resultCode = RESULT_CANCELED;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.settings);

        application = (BluetoothTrackerApplication) getApplication();

        compassSettings = application.loadCompassSettings();

        Button calibrationBttn = (Button) findViewById(R.id.calibrationLimitSettings);
        calibrationBttn.setOnClickListener(this);

        Button fragmentsBttn = (Button) findViewById(R.id.fragmentsNumberSettings);
        fragmentsBttn.setOnClickListener(this);

        Button valuesSizeBttn = (Button) findViewById(R.id.maxValuesSizeSettings);
        valuesSizeBttn.setOnClickListener(this);

        Button btRefreshRateBttn = (Button) findViewById(R.id.btRefreshRateSettings);
        btRefreshRateBttn.setOnClickListener(this);

        Button pointerWidthBttn = (Button) findViewById(R.id.pointerWidthSettings);
        pointerWidthBttn.setOnClickListener(this);

        Switch showPointerSwitch = (Switch) findViewById(R.id.showPointerSwitch);
        showPointerSwitch.setChecked(compassSettings.showPointer);
        showPointerSwitch.setOnCheckedChangeListener(this);

        Switch showColorsSwitch = (Switch) findViewById(R.id.showColorsSwitch);
        showColorsSwitch.setChecked(compassSettings.showColors);
        showColorsSwitch.setOnCheckedChangeListener(this);

        Switch showDebugTextSwitch = (Switch) findViewById(R.id.showDebugTextSwtich);
        showDebugTextSwitch.setChecked(compassSettings.showDebugText);
        showDebugTextSwitch.setOnCheckedChangeListener(this);
    }

    @Override
    public void onBackPressed() {
        Intent intent = new Intent();
        intent.putExtra(INT_VALUES_CHANGED_KEY,intValuesChanged);
        setResult(resultCode, intent);
        finish();
    }

    private void dialogToStoreValue(final String key) {
        AlertDialog.Builder alert = new AlertDialog.Builder(SettingsActivity.this);

        alert.setTitle(key);
        alert.setMessage("New Value");

        int value = 0;
        try {
            value = application.loadIntValue(key);
        } catch (InvalidKeyException e) {
            e.printStackTrace();
        }

        // Set an EditText view to get user input
        final EditText input = new EditText(SettingsActivity.this);
        input.setInputType(InputType.TYPE_CLASS_PHONE);
        input.setText(value + "");
        alert.setView(input);

        alert.setPositiveButton("Ok", new DialogInterface.OnClickListener() {
            public void onClick(DialogInterface dialog, int whichButton) {
                String value = input.getText().toString();
                intValuesChanged = true;
                application.storeIntValue(key, Integer.parseInt(value));
            }
        });

        alert.setNegativeButton("Cancel", new DialogInterface.OnClickListener() {
            public void onClick(DialogInterface dialog, int whichButton) {
                // Canceled.
            }
        });

        alert.show();
        // see http://androidsnippets.com/prompt-user-input-with-an-alertdialog
    }

    @Override
    public void onCheckedChanged(CompoundButton compoundButton, boolean b) {
        switch(compoundButton.getId()) {
            case R.id.showColorsSwitch:
                application.storeBooleanValue(BluetoothTrackerApplication.SHOW_COLORED_COMPASS_KEY,b);
                break;
            case R.id.showDebugTextSwtich:
                application.storeBooleanValue(BluetoothTrackerApplication.SHOW_DEBUG_TEXT_KEY,b);
                break;
            case R.id.showPointerSwitch:
                application.storeBooleanValue(BluetoothTrackerApplication.SHOW_POINTER_KEY,b);
                break;
        }
        resultCode = RESULT_OK;
    }

    @Override
    public void onClick(View view) {
        switch(view.getId()) {
            case R.id.fragmentsNumberSettings:
                dialogToStoreValue(BluetoothTrackerApplication.FRAGMENTS_NUMBER_KEY);
                break;
            case R.id.maxValuesSizeSettings:
                dialogToStoreValue(BluetoothTrackerApplication.MAX_VALUES_SIZE_KEY);
                break;
            case R.id.btRefreshRateSettings:
                dialogToStoreValue(BluetoothTrackerApplication.BT_REFRESH_RATE_KEY);
                break;
            case R.id.pointerWidthSettings:
                dialogToStoreValue(BluetoothTrackerApplication.POINTER_WIDTH_KEY);
                break;
            case R.id.calibrationLimitSettings:
                dialogToStoreValue(BluetoothTrackerApplication.CALIBRATION_LIMIT_KEY);
                break;
        }
        resultCode = RESULT_OK;
    }
}
