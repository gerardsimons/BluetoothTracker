package com.simons.bletracker.activities;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.view.WindowManager;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;

import com.simons.bletracker.BLETrackerApplication;
import com.simons.bletracker.R;
import com.simons.bletracker.controllers.Action;
import com.simons.bletracker.controllers.BLETracker;
import com.simons.bletracker.controllers.State;
import com.simons.bletracker.controllers.StateController;
import com.simons.bletracker.controllers.Transition;
import com.simons.bletracker.models.MacAddress;
import com.simons.bletracker.models.sql.BLETag;
import com.simons.bletracker.remote.ServerAPI;
import com.simons.bletracker.zxing.IntentIntegrator;
import com.simons.bletracker.zxing.IntentResult;

import org.json.JSONObject;

import java.io.UnsupportedEncodingException;

public class MainActivity extends Activity {

    private static final String TAG = MainActivity.class.getSimpleName();

    private TextView caseValueText;
    private TextView bleValueText;
    private TextView stateValueText;

    private BLETracker bleTracker;
    private BLETrackerApplication application;
    private StateController stateController;
    private ServerAPI serverAPI;

    private String caseScan = "PLACEHOLDER";
    private BLETag scannedTag;

    int ordersScanned = 0;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        caseValueText = (TextView) findViewById(R.id.caseValueText);
        bleValueText = (TextView) findViewById(R.id.bleTagText);
        stateValueText = (TextView) findViewById(R.id.currentStateTxt);

        application = (BLETrackerApplication) getApplication();
        serverAPI = ServerAPI.GetInstance();
        bleTracker = BLETracker.GetInstance();

        //Set the state text to current state and also register as listener so it keeps getting updated
        stateController = StateController.GetInstance();
        stateValueText.setText(stateController.getState().toString());
        stateController.registerListener(new StateController.OnStateChangedListener() {
            @Override
            public void OnStateTransitioned(final Transition transition) {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        stateValueText.setText(transition.toState.toString());
                    }
                });
            }
        });

        if (application.isFirstRun()) {
            //Register device as ble controller
            serverAPI.registerBLETracker(bleTracker.getDeviceId(), bleTracker.getInstallId(), new ServerAPI.ServerRequestListener() {
                @Override
                public void onRequestFailed(String response) {
                    errorAlert(MainActivity.this, "RegisterError", "Unable to register this device as a BLE controller MESSAGE = " + response);
                }

                @Override
                public void onRequestCompleted(JSONObject response) {
                    Log.d(TAG, "Succesfully registered this device as a BLE_Tracker!");
                    application.setFirstRun(false);
                }
            });
        } else {
            Log.d(TAG, "Device already registered");
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

        //Maps button transits to map activity
        findViewById(R.id.mapButton).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(MainActivity.this,MapActivity.class);
                startActivity(intent);
            }
        });

        //#9 and #10
        final String[] testTagMacs = {"FC:91:57:FA:03:65","F0:1A:97:79:F5:1E"};

        //Eudokia plein
        final String[] testCustomerCodes = {"1678111111003003300020","1678111111003103310021"};

                //Rotterdam Centraal
//        "1678127860003003300020"

        //*** THESE ARE SPECIAL DEBUGGING / DEVELOPMENT BUTTONS **/
        final Button orderCaseBttn = (Button) findViewById(R.id.testOrderCaseButton);
        orderCaseBttn.setText("OrderCase #" + (ordersScanned + 1));
        orderCaseBttn.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                if (ordersScanned < testCustomerCodes.length) {
                    Log.d(TAG, "Simulating a ordercase scan event.");
                    try {
                        bleTracker.newOrderCaseScanned(testCustomerCodes[ordersScanned], new MacAddress("ED:77:96:59:D1:F1"));
                    } catch (UnsupportedEncodingException e) {
                        e.printStackTrace();
                    }
                    ordersScanned++;

                    if (ordersScanned < testCustomerCodes.length) {
                        orderCaseBttn.setText("OrderCase #" + (ordersScanned + 1));
                    }
                } else
                    Toast.makeText(MainActivity.this, "All test cases scanned", Toast.LENGTH_SHORT);
            }
        });

        findViewById(R.id.flushDataButton).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Log.d(TAG, "Manual flushing of data.");
                bleTracker.flushTrackingData();
            }
        });

        findViewById(R.id.testDepartButton).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                if(stateController.getState() == State.READY_FOR_DEPARTURE) {
                    Log.d(TAG, "Manual depart.");
                    stateController.doAction(Action.DEPART);
                }
                else Toast.makeText(MainActivity.this,"Not yet ready to depart",Toast.LENGTH_SHORT);
            }
        });

        //EUDOKIA
        final float latitude = 51.932822000F;
        final float longitude = 4.47081300F;

        findViewById(R.id.testGPSButton).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Toast.makeText(MainActivity.this,"Not yet implemented",Toast.LENGTH_SHORT);

//                LocationService._mockLocation(latitude, longitude);
            }
        });

        findViewById(R.id.exitButton).setOnClickListener(new View.OnClickListener() {
            public void onClick(View view) {
                System.exit(0);
            }
        });

        getWindow().addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON);
    }

    private void errorAlert(Context context, String title, String message) {

        if (title == null) {
            title = "Error";
        }
        if (message == null) {
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
        } else if (requestCode == ScanLabelActivity.REQUEST_SCAN_CODE && resultCode == Activity.RESULT_OK) {
            scannedTag = (BLETag) intent.getParcelableExtra(ScanLabelActivity.SCAN_RESULT_KEY);

            if (scannedTag != null) {
                Log.d(TAG, "BLETag succesfully scanned : " + scannedTag);
                bleValueText.setText(scannedTag.getAddress().toString());

                //Get the order ID
                bleTracker.newOrderCaseScanned(caseScan, scannedTag.getAddress());
            }
        }
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.

        //UNCOMMENT TO SHOW MENU
        getMenuInflater().inflate(R.menu.menu_main, menu);
        return true;
    }

    @Override
    public void onPause() {
        super.onPause();
    }

    @Override
    public void onResume() {
        super.onResume();

        BLETracker.SetContext(this);

        caseValueText.setText(caseScan);

        if (scannedTag != null)
            bleValueText.setText(scannedTag.getAddress().toString());
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();

        //noinspection SimplifiableIfStatement
        if (id == R.id.action_settings) {
            Intent i = new Intent(MainActivity.this,ConfigurationActivity.class);
            startActivity(i);
        }

        return super.onOptionsItemSelected(item);
    }
}
