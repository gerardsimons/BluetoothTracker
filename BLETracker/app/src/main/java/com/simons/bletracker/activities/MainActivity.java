package com.simons.bletracker.activities;

import android.content.Intent;
import android.os.Bundle;
import android.support.v7.app.ActionBarActivity;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.TextView;

import com.simons.bletracker.R;
import com.simons.bletracker.zxing.IntentIntegrator;
import com.simons.bletracker.zxing.IntentResult;


public class MainActivity extends ActionBarActivity {

    private static final String TAG = MainActivity.class.getSimpleName();

    private enum STATE {
        WAITING_FOR_CASE_SCAN,
        WAITING_FOR_LABEL_SCAN,
        READY_FOR_DEPARTURE,
        EN_ROUTE,
        ARRIVED,
        RETURNED
    }

    private TextView caseValueText;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        caseValueText = (TextView)findViewById(R.id.caseValueText);

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
                Intent intent = new Intent(MainActivity.this,LabelsListActivity.class);
                startActivity(intent);
            }
        });

        //Scan label button starts scanning for BLE labels, requires that case is already scanned
        findViewById(R.id.scanLabelButton).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Log.e(TAG,"Not yet implemented...");
            }
        });
    }

    public void onActivityResult(int requestCode, int resultCode, Intent intent) {
        IntentResult scanResult = IntentIntegrator.parseActivityResult(requestCode, resultCode, intent);
        if (scanResult != null) {
            // handle scan result

            String UPCScanned = scanResult.getContents();
            Log.d(TAG,String.format("Scan code = %s",UPCScanned));

            caseValueText.setText(UPCScanned);
        }
        // else continue with any other code you need in the method

    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.

        //UNCOMMENT TO SHOW MENU
//        getMenuInflater().inflate(R.menu.menu_main, menu);
        return true;
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
