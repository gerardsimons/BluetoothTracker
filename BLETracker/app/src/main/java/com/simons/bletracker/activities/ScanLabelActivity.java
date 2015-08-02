package com.simons.bletracker.activities;

import android.app.Activity;
import android.bluetooth.BluetoothAdapter;
import android.bluetooth.BluetoothManager;
import android.content.BroadcastReceiver;
import android.content.ComponentName;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.ServiceConnection;
import android.os.Bundle;
import android.os.IBinder;
import android.support.v7.app.ActionBarActivity;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;

import com.simons.bletracker.BLETrackerApplication;
import com.simons.bletracker.R;
import com.simons.bletracker.controllers.BLEAuthorizationController;
import com.simons.bletracker.models.MacAddress;
import com.simons.bletracker.models.sql.BLETag;
import com.simons.bletracker.services.BLEDiscoveryService;
import com.simons.bletracker.views.CircularValueIndicator;

import java.io.UnsupportedEncodingException;

public class ScanLabelActivity extends ActionBarActivity {

    private static final String TAG = "ScanlabelActivity";
    private static final int MAX_ROUNDS_REQUIRED = 3;
    private static final int MIN_RSSI = -40;

    public static final String SCAN_RESULT_KEY = "scan_result";
    public static final int REQUEST_SCAN_CODE = 8530;

    private int roundsRequired = 3;

    private BLEAuthorizationController authorizationController;
    private BLEDiscoveryService discoveryService;

    private CircularValueIndicator roundsValueIndicator;

    BroadcastReceiver receiver = new BroadcastReceiver() {

        @Override
        public void onReceive(Context context, Intent intent) {
            if (intent.hasExtra(BLEDiscoveryService.DEVICE_THRESHOLD)) {

                //Get the values from the intent
                String name = intent.getStringExtra(BLEDiscoveryService.DEVICE_NAME);
                String address = intent.getStringExtra(BLEDiscoveryService.DEVICE_ADDRESS);
                int rssi = intent.getIntExtra(BLEDiscoveryService.DEVICE_RSSI, -999);

                BLETag tag = null;
                try {
                    tag = new BLETag(name, new MacAddress(address), rssi);
                } catch (UnsupportedEncodingException e) {
                    e.printStackTrace();
                }

                Log.d(TAG, "New BLE tag : " + tag.toString());

                if (authorizationController.isAuthorized(tag)) {
                    if(tag.getLatestRSSI() >= MIN_RSSI) {
                        roundsRequired--;
                    }
//                    else roundsRequired++;

                    if(roundsRequired > MAX_ROUNDS_REQUIRED) {
                        roundsRequired = MAX_ROUNDS_REQUIRED;
                    }
                    else if(roundsRequired <= 0) {
                        //DONE

                        Intent resultIntent = new Intent();
                        resultIntent.putExtra(SCAN_RESULT_KEY, tag);
                        ScanLabelActivity.this.setResult(Activity.RESULT_OK, resultIntent);
                        finish();
                    }

                    roundsValueIndicator.setRawValue(roundsRequired);
                    roundsValueIndicator.setStrengthValue(roundsRequired / (float)MAX_ROUNDS_REQUIRED);

                    Log.d(TAG,"Rounds still required: " + roundsRequired);
                }
            }
        }
    };

    private final ServiceConnection mServiceConnection = new ServiceConnection() {

        @Override
        public void onServiceConnected(ComponentName componentName, IBinder service) {
            Log.d(TAG, "Connected to discovery service.");
            discoveryService = ((BLEDiscoveryService.LocalBinder) service).getService();
            if (!discoveryService.initialize()) {
                Log.e(TAG, "Unable to initialize Bluetooth");
                finish();
            } else discoveryService.startScanning();
        }

        @Override
        public void onServiceDisconnected(ComponentName componentName) {
            Log.d(TAG, "Disconnected from discovery service.");
            discoveryService = null;

        }
    };

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_scan_label);

        authorizationController = BLEAuthorizationController.getInstance();
        roundsValueIndicator = (CircularValueIndicator)findViewById(R.id.scanStatusText);

    }

    @Override
    public void onPause() {
        super.onPause();
        unregisterReceiver(receiver);
        unbindService(mServiceConnection);
    }

    @Override
    public void onResume() {
        super.onResume();
        final BluetoothManager bluetoothManager =
                (BluetoothManager) getSystemService(Context.BLUETOOTH_SERVICE);
        BluetoothAdapter mBluetoothAdapter = bluetoothManager.getAdapter();

        if (mBluetoothAdapter == null || !mBluetoothAdapter.isEnabled()) {
            Intent enableBtIntent = new Intent(BluetoothAdapter.ACTION_REQUEST_ENABLE);
            startActivityForResult(enableBtIntent, BLETrackerApplication.REQUEST_ENABLE_BT);
        }

        registerReceiver(receiver, new IntentFilter(BLEDiscoveryService.ACTION_NAME));

        Intent serviceIntent = new Intent(this, BLEDiscoveryService.class);
        bindService(serviceIntent, mServiceConnection, BIND_AUTO_CREATE);

        roundsRequired = MAX_ROUNDS_REQUIRED;
        roundsValueIndicator.setRawValue(roundsRequired);
        roundsValueIndicator.setStrengthValue(1);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_scan_label, menu);
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
