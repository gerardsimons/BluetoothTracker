package com.devriesdev.whereatassettracking.app;

import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.app.Service;
import android.app.TaskStackBuilder;
import android.bluetooth.BluetoothAdapter;
import android.bluetooth.BluetoothDevice;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.os.*;
import android.os.Process;
import android.support.v4.app.NotificationCompat;
import android.util.Log;
import android.view.View;
import android.view.WindowManager;
import android.widget.Button;
import android.widget.ListView;
import android.widget.Toast;

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
import java.util.List;
import java.util.Stack;

/**
 * Created by danie_000 on 7/11/2014.
 */
public class MainService extends Service {

    private static BluetoothAdapter bluetoothAdapter;

    private static final String baseURL = "http://demo.whereatcloud.com/yesdelft/";
    private static final AsyncHttpClient client = new AsyncHttpClient();

    private ArrayList<Object[]> labels;
    private ArrayList<String> macFilter;

    private static final String TAG = "MainActivity";

    public static final String DEVICE_NAME = "DEVICE_NAME";
    public static final String DEVICE_MAC = "DEVICE_MAC";
    public static final String DEVICE_RSSI = "DEVICE_RSSI";

    public static final String ACTION_LEFOUND = "ACTION_LEFOUND";
    public static final String ACTION_EXITSERVICE = "ACTION_EXITSERVICE";

    public static int unitID;

    public static PowerManager.WakeLock wakeLock;

    private Looper serviceLooper;
    private ServiceHandler serviceHandler;

    private Context context;
    private static final int notificationID = 0;
    private NotificationCompat.Builder builder;
    private NotificationManager notificationManager;
    private ArrayList<String> notificationLines = new ArrayList<String>();

    private Handler updateHandler;

    private final class ServiceHandler extends Handler {
        public ServiceHandler(Looper looper) {
            super(looper);
        }

        @Override
        public void handleMessage(Message msg) {
            final PowerManager powerManager = (PowerManager) context.getSystemService(Context.POWER_SERVICE);
            long now = SystemClock.uptimeMillis();
            powerManager.userActivity(now, false);

            registerReceiver(new BroadcastReceiver() {
                @Override
                public void onReceive(Context context, Intent intent) {
                    if (intent.getAction().equals(ACTION_EXITSERVICE)) {
                        // First unregister this BroadcastReceiver!!
                        unregisterReceiver(this);

                        // Stop LE scan
                        bluetoothAdapter.stopLeScan(leScanCallback);

                        // Unregister broadcastReceiver
                        unregisterReceiver(broadcastReceiver);

                        // Close the notification
                        notificationManager.cancel(notificationID);

                        // Stop the service
                        stopSelf();
                    }
                }
            }, new IntentFilter(ACTION_EXITSERVICE));

            start(msg);
        }
    }

    @Override
    public void onCreate() {
        context = this;

        HandlerThread thread = new HandlerThread("ServiceStartArguments",
                android.os.Process.THREAD_PRIORITY_FOREGROUND);
        thread.start();

        serviceLooper = thread.getLooper();
        serviceHandler = new ServiceHandler(serviceLooper);
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startID) {
        Toast.makeText(this, "wACT service starting", Toast.LENGTH_SHORT).show();

        Message msg = serviceHandler.obtainMessage();
        msg.arg1 = startID;
        serviceHandler.sendMessage(msg);

        return START_STICKY;
    }

    @Override
    public IBinder onBind(Intent intent) {
        return null;
    }

    @Override
    public void onDestroy() {
        Toast.makeText(this, "wACT service shutdown", Toast.LENGTH_SHORT).show();
    }

    private void start(Message msg) {
        Intent intent = new Intent();
        intent.setAction(ACTION_EXITSERVICE);

        // Initialize the notification builder
        builder = new NotificationCompat.Builder(context)
                .setSmallIcon(R.drawable.ic_launcher)
                .setContentTitle(
                        getResources().getString(R.string.app_name) +
                        " V" + String.valueOf(getResources().getInteger(R.integer.version))
                )
                .setContentText("Starting up...")
                .setAutoCancel(true)
                .setStyle(addLine(Utils.getTimestamp() + ": Starting up..."))
                .addAction(R.drawable.abc_ic_clear, "close",
                        PendingIntent.getBroadcast(
                                context,
                                0,
                                intent,
                                PendingIntent.FLAG_UPDATE_CURRENT
                        )
                );

        // Issue the notification
        notificationManager = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);
        notificationManager.notify(notificationID, builder.build());

        // Set WAKE LOCK
        PowerManager powerManager = (PowerManager) getSystemService(POWER_SERVICE);
        wakeLock = powerManager.newWakeLock(PowerManager.PARTIAL_WAKE_LOCK, "WakeLockTag");
        wakeLock.acquire();

        // Create new instance of Initializer
        final Initializer init = new Initializer();

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
                        startScan();
                    } else {
                        Log.w(TAG, "bluetooth exec found init isn't done yet, registering listener");
                        init.addListener(new Utils.InitiatedListener() {
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

    private void startScan() {
        Log.w(TAG, "bluetooth exec found init is done, starting scan");

        updateHandler = new Handler(updateCheck);
        updateHandler.sendEmptyMessageDelayed(1, 3600000);

        // Update the notification
        builder.setContentText("Scanning...")
               .setStyle(addLine(Utils.getTimestamp() + ": Scanning..."))
               .setWhen(System.currentTimeMillis());
        notificationManager.notify(notificationID, builder.build());

        // Start LE scan
        bluetoothAdapter.startLeScan(leScanCallback);
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
                    client.get(baseURL + "reportsignal.php?" + s, new TextHttpResponseHandler() {
                    });

                    // Update the notification
                    builder.setContentText(": " + name + ", RSSI: " + rssi)
                           .setStyle(addLine(Utils.getTimestamp() +
                                           ": " + name + ", RSSI: " + rssi)
                           )
                           .setWhen(System.currentTimeMillis());
                    notificationManager.notify(notificationID, builder.build());
                }
            }
        }
    };

    private NotificationCompat.InboxStyle addLine(String str) {
        if (notificationLines.isEmpty()) {
            notificationLines.add(str);
        }
        else {
            int length = notificationLines.size();
            notificationLines.add(length, str);
            if (length == 7) {
                notificationLines.remove(0);
            }
        }

        NotificationCompat.InboxStyle out = new NotificationCompat.InboxStyle();
        for (String s : notificationLines) {
            out.addLine(s);
        }
        return out;
    }

    private final Handler.Callback updateCheck = new Handler.Callback() {
        @Override
        public boolean handleMessage(Message message) {
            (new WifiExecutable(context, client, baseURL, null)).postExecute();
            updateHandler.sendEmptyMessageDelayed(1, 3600000);
            return false;
        }
    };
}
