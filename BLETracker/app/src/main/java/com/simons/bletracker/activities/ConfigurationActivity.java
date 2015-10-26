package com.simons.bletracker.activities;

import android.app.AlarmManager;
import android.app.AlertDialog;
import android.app.PendingIntent;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.support.v7.app.ActionBarActivity;
import android.text.Editable;
import android.text.TextWatcher;
import android.widget.EditText;

import com.simons.bletracker.BLETrackerApplication;
import com.simons.bletracker.R;

public class ConfigurationActivity extends ActionBarActivity implements TextWatcher {

    private boolean configChanged = false;
    private BLETrackerApplication application;

    private EditText gpsDisplacmentEditText;
    private EditText gpsFastestIntervalEditText;
    private EditText gpsUpdateIntervalEditText;
    private EditText trackerCacheSizeEditText;
    private EditText departDistanceTriggerEditText;
    private EditText arriveDistanceTriggerEditText;
    private EditText rssiDiscoveryIntervalEditText;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_configuration);
        setupUI();
        application = (BLETrackerApplication)getApplication();
    }

    private void setupUI() {
        gpsDisplacmentEditText = (EditText) findViewById(R.id.gpsDisplacementEditText);
        gpsDisplacmentEditText.addTextChangedListener(this);

        gpsFastestIntervalEditText = (EditText) findViewById(R.id.gpsFastestIntervalEditText);
        gpsFastestIntervalEditText.addTextChangedListener(this);

        gpsUpdateIntervalEditText = (EditText) findViewById(R.id.gpsUpdateIntervalEditText);
        gpsUpdateIntervalEditText.addTextChangedListener(this);

        departDistanceTriggerEditText = (EditText) findViewById(R.id.departDistanceTriggerEditText);
        departDistanceTriggerEditText.addTextChangedListener(this);

        arriveDistanceTriggerEditText = (EditText) findViewById(R.id.arriveDistanceTriggerEditText);
        arriveDistanceTriggerEditText.addTextChangedListener(this);

        trackerCacheSizeEditText = (EditText) findViewById(R.id.trackerCacheSizeEditText);
        trackerCacheSizeEditText.addTextChangedListener(this);

        rssiDiscoveryIntervalEditText = (EditText) findViewById(R.id.rssiDiscoveryIntervalEditText);
        rssiDiscoveryIntervalEditText.addTextChangedListener(this);
    }

    @Override
    public void onBackPressed() {

        if(configChanged) {
            new AlertDialog.Builder(this)
                    .setTitle("Restart App?")
                    .setMessage("A restart is needed for the changed to take effect, do you want to restart now?")
                    .setNegativeButton(android.R.string.no, null)
                    .setPositiveButton(android.R.string.yes, new AlertDialog.OnClickListener() {
                        public void onClick(DialogInterface arg0, int arg1) {
                            Intent mStartActivity = new Intent(ConfigurationActivity.this, MainActivity.class);
                            int mPendingIntentId = 123456;
                            PendingIntent mPendingIntent = PendingIntent.getActivity(ConfigurationActivity.this, mPendingIntentId, mStartActivity, PendingIntent.FLAG_CANCEL_CURRENT);
                            AlarmManager mgr = (AlarmManager) ConfigurationActivity.this.getSystemService(Context.ALARM_SERVICE);
                            mgr.set(AlarmManager.RTC, System.currentTimeMillis() + 100, mPendingIntent);
                            System.exit(0);
                        }
                    }).create().show();
        }
    }

    @Override
    public void beforeTextChanged(CharSequence charSequence, int i, int i1, int i2) {

    }

    @Override
    public void onTextChanged(CharSequence charSequence, int i, int i1, int i2) {

    }

    //TODO: Only store those values that actually changed
    @Override
    public void afterTextChanged(Editable editable) {
        storeChanges();
    }

    private int getIntFromEditText(EditText et) {
        return Integer.parseInt(et.getText().toString());
    }

    private void storeChanges() {
        if(application != null) {
            application.storeGPSDisplacement(getIntFromEditText(gpsDisplacmentEditText));
            application.storeFastestGPSUpdateInterval(getIntFromEditText(gpsFastestIntervalEditText));
            application.storeGPSUpdateInterval(getIntFromEditText(gpsUpdateIntervalEditText));
            application.storeDepartDistanceTrigger(getIntFromEditText(departDistanceTriggerEditText));
            application.storeArriveDistanceTrigger(getIntFromEditText(arriveDistanceTriggerEditText));
            application.storeRSSIDiscoveryInterval(getIntFromEditText(rssiDiscoveryIntervalEditText));
            application.storeTrackerCacheSize(getIntFromEditText(trackerCacheSizeEditText));

            configChanged = true;
        }
    }
}
