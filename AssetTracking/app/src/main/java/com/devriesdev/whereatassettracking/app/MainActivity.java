package com.devriesdev.whereatassettracking.app;

import android.app.Activity;
import android.bluetooth.BluetoothAdapter;
import android.bluetooth.BluetoothDevice;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.os.Bundle;
import android.os.PowerManager;
import android.util.Log;
import android.view.View;
import android.view.WindowManager;
import android.widget.Button;
import android.widget.ListView;
import android.widget.TextView;

import com.devriesdev.whereatassettracking.utils.BluetoothExecutable;
import com.devriesdev.whereatassettracking.utils.Callback;
import com.devriesdev.whereatassettracking.utils.Initializer;
import com.devriesdev.whereatassettracking.utils.Utils;
import com.devriesdev.whereatassettracking.utils.WifiExecutable;
import com.loopj.android.http.AsyncHttpClient;
import com.loopj.android.http.TextHttpResponseHandler;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Iterator;

public class MainActivity extends Activity {
    private static BluetoothAdapter bluetoothAdapter;

    private static DeviceArrayAdapter deviceArrayAdapter;

    private static final String baseURL = "http://demo.whereatcloud.com/yesdelft/";
    private static final AsyncHttpClient client = new AsyncHttpClient();

    private ArrayList<Object[]> labels;
    private ArrayList<String> macFilter;

    private static final String TAG = "MainActivity";

    public static final String DEVICE_NAME = "DEVICE_NAME";
    public static final String DEVICE_MAC = "DEVICE_MAC";
    public static final String DEVICE_RSSI = "DEVICE_RSSI";

    public static final String ACTION_LEFOUND = "ACTION_LEFOUND";

    public static int unitID;

    public static PowerManager.WakeLock wakeLock;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        getWindow().addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON);

        // Set version text
        TextView versionText = (TextView) findViewById(R.id.versionText);
        versionText.append(String.valueOf(this.getResources().getInteger(R.integer.version)));

        // Set WAKE LOCK
        PowerManager powerManager = (PowerManager) getSystemService(POWER_SERVICE);
        wakeLock = powerManager.newWakeLock(PowerManager.PARTIAL_WAKE_LOCK, "WakeLockTag");
        wakeLock.acquire();

        // Initialize the listview
        ListView listView = (ListView) findViewById(R.id.listView);

        // Initialize Device Array and Adapter for the list
        ArrayList<Object[]> devices = new ArrayList<Object[]>();
        deviceArrayAdapter = new DeviceArrayAdapter(this, R.layout.rowlayout, devices);
        listView.setAdapter(deviceArrayAdapter);

        Button bReset = (Button) findViewById(R.id.bReset);
        bReset.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                deviceArrayAdapter.reset();
            }
        });

        Button bToService = (Button) findViewById(R.id.bToService);
        final Context context = this;
        bToService.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent service = new Intent(context, MainService.class);
                startService(service);

                finish();
            }
        });

        // Create new instance of Initializer
        final Initializer init = new Initializer();

        // Initialize WiFi and server connection
        new Initializer().execute(new WifiExecutable(this, client, baseURL, new Callback() {
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
                                init.done();
                                init.isInitiated();
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
                    registerReceiver(broadcastReceiver, filter);

                    if (init.isDone()) {
                        Log.w(TAG, "bluetooth exec found init is done, starting scan");
                        bluetoothAdapter.startLeScan(leScanCallback);
                    } else {
                        Log.w(TAG, "bluetooth exec found init isn't done yet, registering listener");
                        init.addListener(new Utils.InitiatedListener() {
                            @Override
                            public void onInitiated() {
                                Log.w(TAG, "bluetooth received call that wifi is initiated, starting scan");
                                // Start Bluetooth discovery
                                bluetoothAdapter.startLeScan(leScanCallback);
                            }
                        });
                    }
                } else {
                    throw new IllegalArgumentException("BluetoothExecutable requires a callback with an instance of BluetoothAdapter");
                }
            }
        }));
    }

    @Override
    protected void onPause() {
        super.onPause();
    }

    @Override
    protected void onResume() {
        super.onResume();
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        // Stop LE scan
        bluetoothAdapter.stopLeScan(leScanCallback);

        // Unregister broadcastReceiver
        unregisterReceiver(broadcastReceiver);
    }

    private final BluetoothAdapter.LeScanCallback leScanCallback = new BluetoothAdapter.LeScanCallback() {
        @Override
        public void onLeScan(BluetoothDevice device, final int rssi, byte[] bytes) {
            Intent intent = new Intent();
            intent.setAction(ACTION_LEFOUND);

            intent.putExtra(DEVICE_NAME, device.getName());
            intent.putExtra(DEVICE_MAC, device.getAddress());
            intent.putExtra(DEVICE_RSSI, rssi);

            sendBroadcast(intent);
        }
    };

    private final BroadcastReceiver broadcastReceiver = new BroadcastReceiver() {
        public void onReceive(Context context, Intent intent) {
            // Get the intents action
            String action = intent.getAction();

            // Handle the LEFOUND intent
            if (ACTION_LEFOUND.equals(action)) {
                String macAddress = intent.getStringExtra(DEVICE_MAC);
                Log.w(TAG, macAddress);

                int position = macFilter.indexOf(macAddress);
                if (position != -1) {
                    // Obtain device info
                    String name = intent.getStringExtra(DEVICE_NAME);
                    int rssi = intent.getIntExtra(DEVICE_RSSI, 0);

                    Object[] label = labels.get(position);
                    String s = "unitid=" + String.valueOf(unitID) + "&labelids[]=" + label[0] + "&signals[]=" + String.valueOf(rssi);
                    client.get(baseURL + "reportsignal.php?" + s, new TextHttpResponseHandler() {});

                    // Process the device
                    deviceArrayAdapter.processDevice(macAddress, name, rssi);
                }
            }
        }
    };
}
