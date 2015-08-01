package com.simons.bletracker.activities;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.support.v7.app.ActionBarActivity;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.TextView;

import com.simons.bletracker.BLETrackerApplication;
import com.simons.bletracker.R;
import com.simons.bletracker.controllers.BLETracker;
import com.simons.bletracker.models.sql.BLETag;
import com.simons.bletracker.remote.ServerAPI;
import com.simons.bletracker.zxing.IntentIntegrator;
import com.simons.bletracker.zxing.IntentResult;

import org.json.JSONObject;


public class MainActivity extends ActionBarActivity {

    private static final String TAG = MainActivity.class.getSimpleName();

    private TextView caseValueText;
    private TextView bleValueText;

    private BLETracker bleTracker;
    private BLETrackerApplication application;
    private ServerAPI serverAPI;

    private String caseScan = "PLACEHOLDER";

    private BLETag scannedTag;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        caseValueText = (TextView)findViewById(R.id.caseValueText);
        bleValueText = (TextView)findViewById(R.id.bleTagText);

        application = (BLETrackerApplication)getApplication();
        serverAPI = ServerAPI.GetInstance();
        bleTracker = BLETracker.GetInstance();

        if(application.isFirstRun()) {
            //Register device as ble controller
            serverAPI.registerBLEController(bleTracker.getDeviceId(), bleTracker.getInstallId(), new ServerAPI.ServerRequestListener() {
                @Override
                public void onRequestFailed() {
                    errorAlert(MainActivity.this,"RegisterError","Unable to register this device as a BLE controller");
                }

                @Override
                public void onRequestCompleted(JSONObject response) {
                    Log.d(TAG,"Succesfully registered this device as a BLE_Tracker!");
                    application.setFirstRun(false);
                }
            });
        }
        else {
            Log.d(TAG,"Device already registered");
        }

        //Set button listeners

        //Scan case button starts the ZXing scanner app
        findViewById(R.id.scanCaseButton).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                IntentIntegrator.initiateScan(MainActivity.this);
            }
        });

        //Labels button starts LabelListActivity displaying all accesible labels and their states
        findViewById(R.id.labelsButton).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(MainActivity.this, LabelsListActivity.class);
                startActivity(intent);
            }
        });

        //Scan label button starts scanning for BLE labels, requires that case is already scanned
        findViewById(R.id.scanLabelButton).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (caseScan != null) {
                    Intent intent = new Intent(MainActivity.this, ScanLabelActivity.class);
                    startActivityForResult(intent, ScanLabelActivity.REQUEST_SCAN_CODE);
                } else {
                    Log.d(TAG, "Case needs to be scanned first!");
                }
            }
        });

        findViewById(R.id.testButton).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Log.d(TAG,"Simulating a departure event.");


            }
        });
        //Authenticate at server
//        remoteLogin();
    }

    private void errorAlert(Context context, String title, String message) {

        if(title == null) {
            title = "Error";
        }
        if(message == null) {
            message = "An unknown error occurred.";
        }

        AlertDialog alertDialog = new AlertDialog.Builder(context).create();
        alertDialog.setTitle(title);
        alertDialog.setMessage(message);
        alertDialog.setButton(AlertDialog.BUTTON_NEUTRAL, "OK",
                new DialogInterface.OnClickListener() {
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.dismiss();
                    }
                });
        alertDialog.show();
    }

    public void onActivityResult(int requestCode, int resultCode, Intent intent) {
        IntentResult barcodeScanResult = IntentIntegrator.parseActivityResult(requestCode, resultCode, intent);
        if (barcodeScanResult != null) {
            // handle scan result

            caseScan = barcodeScanResult.getContents();
            Log.d(TAG, String.format("Scan code = %s", caseScan));

            caseValueText.setText(caseScan);
        }
        else if(requestCode == ScanLabelActivity.REQUEST_SCAN_CODE && resultCode == Activity.RESULT_OK) {

            Intent i = getIntent();
            scannedTag = i.getParcelableExtra("name_of_extra");

            if(scannedTag != null) {
                Log.d(TAG, "BLETag succesfully scanned : " + scannedTag);
                bleValueText.setText(scannedTag.getAddress());

                //Get the order ID
                bleTracker.newOrderCaseScanned(caseScan,scannedTag.getAddress());
            }

            //Now we have both the case scanned and the label, these two are now considered linked together.

            //Update this at the server

            //Possibly continue scanning more labels and cases or check for departure of truck

            //Initialize a route service which detects departures, not sure if this should actually be a service
        }
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.

        //UNCOMMENT TO SHOW MENU
//        getMenuInflater().inflate(R.menu.menu_main, menu);
        return true;
    }

    @Override
    public void onResume() {
        super.onResume();

        caseValueText.setText(caseScan);

        if(scannedTag != null)
            bleValueText.setText(scannedTag.getAddress());
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();

        //noinspection SimplifiableIfStatement
        if (id == R.id.action_settings) {
            return true;
        }

        return super.onOptionsItemSelected(item);
    }
}
