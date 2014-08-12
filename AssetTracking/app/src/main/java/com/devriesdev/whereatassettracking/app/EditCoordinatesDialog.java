package com.devriesdev.whereatassettracking.app;

import android.app.ActionBar;
import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.View;
import android.view.Window;
import android.view.WindowManager;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;

/**
 * Created by danie_000 on 7/17/2014.
 */
public class EditCoordinatesDialog extends Activity {

    private String requestAction;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        requestWindowFeature(Window.FEATURE_NO_TITLE);
        setContentView(R.layout.dialog_location);

        Bundle intentExtras = getIntent().getExtras();
        if (!intentExtras.isEmpty())
        {
            requestAction = intentExtras.getString("REQUEST_ACTION");

            findViewById(R.id.dialog_cancel).setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View view) {
                    finish();
                }
            });

            findViewById(R.id.dialog_apply).setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View view) {
                    String lat, lon;
                    lat = ((EditText) findViewById(R.id.dialog_lat))
                            .getText()
                            .toString();
                    lon = ((EditText) findViewById(R.id.dialog_lon))
                            .getText()
                            .toString();

                    if (!lat.isEmpty() && !lon.isEmpty()) {
                        returnResult(
                                Double.valueOf(lat),
                                Double.valueOf(lon)
                        );
                    } else {
                        finish();
                    }
                }
            });

            findViewById(R.id.dialog_apply).setEnabled(false);
            ((EditText) findViewById(R.id.dialog_lat))
                    .addTextChangedListener(new EnableApplyListener());
            ((EditText) findViewById(R.id.dialog_lon))
                    .addTextChangedListener(new EnableApplyListener());
        } else {
            finish();
        }
    }

    private void returnResult(double lat, double lon) {
        Intent intent = new Intent(requestAction);
        intent.putExtra("LATITUDE", lat);
        intent.putExtra("LONGITUDE", lon);
        sendBroadcast(intent);

        finish();
    }

    private class EnableApplyListener implements TextWatcher {
        @Override
        public void beforeTextChanged(CharSequence charSequence, int i, int i2, int i3) {

        }

        @Override
        public void onTextChanged(CharSequence charSequence, int i, int i2, int i3) {

        }

        @Override
        public void afterTextChanged(Editable editable) {
            String lat, lon;
            lat = ((TextView) findViewById(R.id.dialog_lat)).getText().toString();
            lon = ((TextView) findViewById(R.id.dialog_lon)).getText().toString();

            if (lat.isEmpty() || lon.isEmpty()) {
                findViewById(R.id.dialog_apply).setEnabled(false);
            } else {
                findViewById(R.id.dialog_apply).setEnabled(true);
            }
        }
    }

}
