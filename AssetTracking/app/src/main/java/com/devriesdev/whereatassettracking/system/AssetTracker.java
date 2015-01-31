package com.devriesdev.whereatassettracking.system;

import android.bluetooth.BluetoothAdapter;
import android.bluetooth.BluetoothDevice;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.os.Handler;
import android.os.Message;
import android.util.Log;

import com.devriesdev.whereatassettracking.utils.Utils;
import com.loopj.android.http.AsyncHttpClient;
import com.loopj.android.http.TextHttpResponseHandler;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Iterator;
import java.util.Timer;
import java.util.TimerTask;

/**
 * Created by danie_000 on 7/18/2014.
 */
public class AssetTracker {

    private static final String TAG = "AssetTracker";

    private final Context context;
    private final String ACTION_UICALLBACK;

    private static BluetoothAdapter bluetoothAdapter;

    //private static final String baseURL = "http://192.168.1.101:8080/whereat/demo/yesdelft/";
    private static final String baseURL = "http://demo.whereatcloud.com/yesdelft/";
    private static final AsyncHttpClient client = new AsyncHttpClient();

    private static ArrayList<Object[]> labels;
    private static ArrayList<String> macFilter;


    private static final String DEVICE = "DEVICE";
    private static final String RSSI = "RSSI";
    private static final String DEVICE_NAME = "DEVICE_NAME";
    private static final String DEVICE_MAC = "DEVICE_MAC";
    private static final String DEVICE_RSSI = "DEVICE_RSSI";

    private static final String ACTION_LEFOUND = "ACTION_LEFOUND";

    private static int unitID;

    private Handler updateHandler;
    private static final long updateInterval = 3600000;

    private static Locator locator;
    private static boolean googlePlayServicesAvailable = false;

    public AssetTracker(final Context context, final String UICallbackAction, Locator loc) {
        this.context = context;
        ACTION_UICALLBACK = UICallbackAction;

        locator = loc;
    }

    public void start() {
        final Initializer initializer = new Initializer();

        // Initialize WiFi and server connection
        new Initializer().execute(new WifiExecutable(context, client, baseURL, new Callback() {
            @Override
            public void call(Object... params) {
                if (params.length == 1) {
                    unitID = (Integer) params[0];

                    labels = new ArrayList<Object[]>();
                    macFilter = new ArrayList<String>();
                    Log.w(TAG, "going to fetch labels from server");
                    client.get(baseURL + "getlabels.php", new TextHttpResponseHandler() {
                        @Override
                        public void onSuccess(int statusCode, org.apache.http.Header[] headers, String responseBody) {
                            Log.w(TAG, "Got a resposne from the server: " + responseBody);
                            try {
                                // Process the labels
                                JSONObject response = new JSONObject(responseBody);
                                Iterator iterator = response.keys();
                                Log.w(TAG, "going to iterate over labels");
                                while (iterator.hasNext()) {
                                    String key = (String) iterator.next();
                                    String mac = response.getString(key).toUpperCase();
                                    Object[] label = {key, mac, 0};
                                    labels.add(label);
                                    macFilter.add(mac);
                                }

                                Log.w(TAG, "wifi is done, wifi is isInitiated!");
                                initializer.done();
                                initializer.isInitiated();
                            } catch (JSONException e) {
                                e.printStackTrace();
                            }
                        }
                    });
                } else {
                    throw new IllegalArgumentException("WifiExecutable requires a callback with a Integer representing the unitID of the device");
                }
            }
        }));

        // Initialize Bluetooth
        new Initializer().execute(new BluetoothExecutable(new Callback() {
            @Override
            public void call(Object... params) {
                if (params.length == 1) {
                    bluetoothAdapter = (BluetoothAdapter) params[0];

                    Log.w(TAG, "registering bluetooth broadcastReceiver stuff");
                    IntentFilter filter = new IntentFilter(ACTION_LEFOUND);
                    context.registerReceiver(leFoundReceiver, filter);

                    if (initializer.isDone()) {
                        startScan();
                    } else {
                        Log.w(TAG, "bluetooth exec found init isn't done yet, registering listener");
                        initializer.addListener(new Utils.InitiatedListener() {
                            @Override
                            public void onInitiated() {
                                startScan();
                            }
                        });
                    }
                } else {
                    throw new IllegalArgumentException("BluetoothExecutable requires a callback with an instance of BluetoothAdapter");
                }
            }
        }));
    }

    public void stop() {
        // Stop LE scan
        bluetoothAdapter.stopLeScan(leScanCallback);

        // Unregister broadcastReceivers
        context.unregisterReceiver(leFoundReceiver);
    }

    private void startScan() {
        Log.w(TAG, "bluetooth exec found init is done, starting scan");

        // Initialize the UpdateHandler
        updateHandler = new Handler(updateCheck);
        updateHandler.sendEmptyMessageDelayed(1, updateInterval);

        // Notify the UI
        notifyUI("Scanning...");

        // Start scanning
        bluetoothAdapter.startLeScan(leScanCallback);

        Log.w(TAG, "After turning LE Scan on");
        // Create scheduled task to see if app is still running
        final Timer runTestTimer = new Timer();

        final TimerTask timerTask = new TimerTask() {
            @Override
            public void run() {
                Log.w(TAG, "Still alive...");
            }
        };

        runTestTimer.scheduleAtFixedRate(timerTask, 1000, 1000);

    }

    private final BluetoothAdapter.LeScanCallback leScanCallback = new BluetoothAdapter.LeScanCallback() {
        @Override
        public void onLeScan(BluetoothDevice device, final int rssi, byte[] bytes) {
            Log.w(TAG, "Found something!!!");

            Intent intent = new Intent();
            intent.setAction(ACTION_LEFOUND);
            intent.putExtra(DEVICE_MAC, device.getAddress());
            intent.putExtra(DEVICE_NAME, device.getName());
            intent.putExtra(DEVICE_RSSI, rssi);

            context.sendBroadcast(intent);
        }
    };

    private final BroadcastReceiver leFoundReceiver = new BroadcastReceiver() {
        public void onReceive(Context context, Intent intent) {
            // Handle the LEFOUND intent
            String macAddress = intent.getStringExtra(DEVICE_MAC);
            int rssi = intent.getIntExtra(DEVICE_RSSI, -1);
            String name = intent.getStringExtra(DEVICE_NAME);


            int position = macFilter.indexOf(macAddress);
            if (position != -1) {

                // Get location of unit
                /*Location location = locator.getLastLocation();
                String lat = Double.toString(location.getLatitude());
                String lon = Double.toString(location.getLongitude());
                String acc = Float.toString(location.getAccuracy());*/
                String lat = "";
                String lon = "";
                String acc = "";

                // Report signal
                Object[] label = labels.get(position);
                String s = "unitid=" + String.valueOf(unitID) +
                        "&labelids[]=" + label[0] +
                        "&signals[]=" + String.valueOf(rssi) +
                        "&lat=" + lat +
                        "&lon=" + lon +
                        "&acc=" + acc;
                client.get(baseURL + "reportsignal.php?" + s, new TextHttpResponseHandler() {});

                // Notify UI
                if (!name.isEmpty()) {
                    notifyUI(name + ", RSSI: " + rssi);
                    Log.w(TAG, name + ", RSSI: " + rssi);
                } else {
                    notifyUI(macAddress + ", RSSI: " + rssi);
                    Log.w(TAG, macAddress + ", RSSI: " + rssi);
                }
            }
        }
    };

    private final Handler.Callback updateCheck = new Handler.Callback() {
        @Override
        public boolean handleMessage(Message message) {
            (new WifiExecutable(context, client, baseURL, null)).postExecute();
            updateHandler.sendEmptyMessageDelayed(1, updateInterval);
            return false;
        }
    };

    private void notifyUI(String message) {
        Log.w(TAG, "Notified UI: " + message);

        Intent intent = new Intent();
        intent.setAction(ACTION_UICALLBACK)
                .putExtra("MESSAGE", message)
                .putExtra("TIMESTAMP", System.currentTimeMillis());
        context.sendBroadcast(intent);
    }

}
