package com.devriesdev.whereatassettracking.app;

import android.app.NotificationManager;
import android.app.PendingIntent;
import android.app.Service;
import android.app.TaskStackBuilder;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.SharedPreferences;
import android.location.Location;
import android.os.Bundle;
import android.os.Handler;
import android.os.HandlerThread;
import android.os.IBinder;
import android.os.Looper;
import android.os.Message;
import android.os.PowerManager;
import android.os.SystemClock;
import android.support.v4.app.NotificationCompat;
import android.widget.Toast;

import com.devriesdev.whereatassettracking.system.AssetTracker;
import com.devriesdev.whereatassettracking.system.LatLng;
import com.devriesdev.whereatassettracking.system.Locator;
import com.devriesdev.whereatassettracking.utils.Utils;

import java.util.ArrayList;

//import com.google.android.gms.common.ConnectionResult;
//import com.google.android.gms.common.GooglePlayServicesUtil;
//import com.google.android.gms.maps.model.LatLng;

/**
 * Created by danie_000 on 7/11/2014.
 */
public class MainService extends Service {

    private static final String TAG = "MainActivity";

    private static final String ACTION_UICALLBACK = "ACTION_UICALLBACK";
    private static final String ACTION_EXITSERVICE = "ACTION_EXITSERVICE";
    private static final String ACTION_TOGGLELOCATIONFIXED = "ACTION_TOGGLELOCATIONFIXED";
    private static final String ACTION_EDITLOCATION = "ACTION_EDITLOCATION";

    private ServiceHandler serviceHandler;

    private Context context;
    private static final int notificationID = 0;
    private NotificationCompat.Builder builder;
    private NotificationManager notificationManager;
    private ArrayList<String> notificationLines = new ArrayList<String>();

    private SharedPreferences sharedPreferences;
    private boolean locationFixed;
    private LatLng fixedLocation;

    private BroadcastReceiver toggleLocationFixedReceiver, editLocationReceiver;

    private AssetTracker assetTracker;

    private Locator locator;

    private static boolean googlePlayServicesAvailable = false;

    private final class ServiceHandler extends Handler {
        public ServiceHandler(Looper looper) {
            super(looper);
        }

        @Override
        public void handleMessage(Message msg) {
            final PowerManager powerManager = (PowerManager) context.getSystemService(Context.POWER_SERVICE);
            long now = SystemClock.uptimeMillis();
            powerManager.userActivity(now, false);

            /*if (GooglePlayServicesUtil.isGooglePlayServicesAvailable(context) == ConnectionResult.SUCCESS) {
                googlePlayServicesAvailable = true;
            }
            locator.init(googlePlayServicesAvailable);*/

            fixedLocation = new LatLng(0, 0);

            final BroadcastReceiver uiCallbackReceiver, exitServiceReceiver;

            uiCallbackReceiver = new BroadcastReceiver() {
                @Override
                public void onReceive(Context context, Intent intent) {
                    Bundle extras = intent.getExtras();
                    if (!extras.isEmpty()) {
                        String message = extras.getString("MESSAGE");
                        long timeStamp = extras.getLong("TIMESTAMP");

                        // Update the notification
                        builder.setContentText(message)
                                .setStyle(addLine(Utils.getTimeString(timeStamp) + ": " + message))
                                .setWhen(timeStamp);
                        notificationManager.notify(notificationID, builder.build());
                    }
                }
            };

            exitServiceReceiver = new BroadcastReceiver() {
                @Override
                public void onReceive(Context context, Intent intent) {
                    if (intent.getAction().equals(ACTION_EXITSERVICE)) {
                        // First unregister this BroadcastReceiver!!
                        unregisterReceiver(this);

                        // Stop AssetTracking
                        assetTracker.stop();

                        if (googlePlayServicesAvailable) {
                            // Stop Locator
                            locator.disconnect();
                        }

                        // Unregister the UI broadcast receivers
                        unregisterReceiver(uiCallbackReceiver);
                        unregisterReceiver(toggleLocationFixedReceiver);
                        unregisterReceiver(editLocationReceiver);

                        // Close the notification
                        notificationManager.cancel(notificationID);

                        // Store the shared preferences
                        sharedPreferences
                                .edit()
                                .putBoolean("KEY_LOCATION_FIXED", locationFixed)
                                .apply();

                        // Stop the service
                        stopSelf();
                    }
                }
            };

            toggleLocationFixedReceiver = new BroadcastReceiver() {
                @Override
                public void onReceive(Context context, Intent intent) {
                    if (intent.getAction().equals(ACTION_TOGGLELOCATIONFIXED)) {
                        locationFixed = !locationFixed;

                        if (locationFixed) {
                            builder = getNewBuilder("Location was fixed");
                            if (googlePlayServicesAvailable) {
                                locator.stopUpdates();
                            }
                        } else {
                            builder = getNewBuilder("Location was released");
                            if (googlePlayServicesAvailable) {
                                locator.requestUpdates();
                            }
                        }

                        notificationManager.notify(notificationID, builder.build());
                    }
                }
            };

            editLocationReceiver = new BroadcastReceiver() {
                @Override
                public void onReceive(Context context, Intent intent) {
                    if (intent.getAction().equals(ACTION_EDITLOCATION)) {
                        double lat, lon;
                        lat = intent.getDoubleExtra("LATITUDE", -1);
                        lon = intent.getDoubleExtra("LONGITUDE", -1);
                        if (lat != -1 && lon != -1) {
                            fixedLocation = new LatLng(lat, lon);

                            Location location = new Location("");
                            location.setLatitude(lat);
                            location.setLongitude(lon);

                            locator.setLastLocation(location);

                            builder = getNewBuilder("Location was updated");

                            notificationManager.notify(notificationID, builder.build());
                        }
                    }
                }
            };

            registerReceiver(uiCallbackReceiver,
                    new IntentFilter(ACTION_UICALLBACK));

            registerReceiver(exitServiceReceiver,
                    new IntentFilter(ACTION_EXITSERVICE));

            registerReceiver(toggleLocationFixedReceiver,
                    new IntentFilter(ACTION_TOGGLELOCATIONFIXED));

            registerReceiver(editLocationReceiver,
                    new IntentFilter(ACTION_EDITLOCATION));

            start();
        }
    }

    @Override
    public void onCreate() {
        context = this;

        locator = new Locator(context, ACTION_UICALLBACK);

        assetTracker = new AssetTracker(context, ACTION_UICALLBACK, locator);

        sharedPreferences = getSharedPreferences("wACNPrefs", Context.MODE_PRIVATE);

        HandlerThread thread = new HandlerThread("ServiceStartArguments",
                android.os.Process.THREAD_PRIORITY_FOREGROUND);
        thread.start();

        serviceHandler = new ServiceHandler(thread.getLooper());
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startID) {

        Toast.makeText(this, "wACN service starting", Toast.LENGTH_SHORT).show();

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
        Toast.makeText(this, "wACN service shutdown", Toast.LENGTH_SHORT).show();
    }

    private void start() {
        if (sharedPreferences.contains("KEY_LOCATION_FIXED")) {
            locationFixed = sharedPreferences.getBoolean("KEY_LOCATION_FIXED", true);
        } else {
            locationFixed = true;
            sharedPreferences.edit().putBoolean("KEY_LOCATION_FIXED", true).apply();
        }

        builder = getNewBuilder("Starting up...");

        // Issue the notification
        notificationManager = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);
        notificationManager.notify(notificationID, builder.build());

        // Set WAKE LOCK
        ((PowerManager) getSystemService(POWER_SERVICE))
                .newWakeLock(PowerManager.PARTIAL_WAKE_LOCK, "WakeLockTag")
                .acquire();

        assetTracker.start();

        if (googlePlayServicesAvailable) {
            locator.connect(!locationFixed);

            builder = getNewBuilder("Google Play services available");
        } else {
            builder = getNewBuilder("Google Play services unavailable");
        }
        notificationManager.notify(notificationID, builder.build());
    }

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

    private NotificationCompat.Builder getNewBuilder(String newLine) {
        long timeStamp = System.currentTimeMillis();

        Intent exitServiceIntent = new Intent();
        exitServiceIntent.setAction(ACTION_EXITSERVICE);

        Intent toggleLocationFixedIntent = new Intent();
        toggleLocationFixedIntent.setAction(ACTION_TOGGLELOCATIONFIXED);

        NotificationCompat.Builder b = new NotificationCompat.Builder(context)
                .setOngoing(true)
                .setSmallIcon(R.drawable.ic_launcher)
                .setContentTitle(
                        getResources().getString(R.string.app_name) +
                                " V" + String.valueOf(getResources().getInteger(R.integer.google_play_services_version))
                )
                .setAutoCancel(true)
                .addAction(R.drawable.ic_action_cancel, "exit",
                        PendingIntent.getBroadcast(
                                context,
                                0,
                                exitServiceIntent,
                                PendingIntent.FLAG_UPDATE_CURRENT
                        )
                )
                .setContentText(newLine)
                .setStyle(addLine(Utils.getTimeString(timeStamp) + ": " + newLine))
                .setWhen(timeStamp);

        if (locationFixed) {
            Intent editLocationIntent = new Intent(context, EditCoordinatesDialog.class);
            editLocationIntent.putExtra("REQUEST_ACTION", ACTION_EDITLOCATION);
            String lat = "", lon = "";
            if (googlePlayServicesAvailable) {
                if (locator.isConnected()) {
                    Location location = locator.getLastLocation();
                    lat = Double.toString(location.getLatitude());
                    lon = Double.toString(location.getLongitude());
                }

                b
                        .addAction(R.drawable.ic_action_location_off, "release",
                                PendingIntent.getBroadcast(
                                        context,
                                        0,
                                        toggleLocationFixedIntent,
                                        PendingIntent.FLAG_UPDATE_CURRENT
                                )
                        );
            }
            editLocationIntent.putExtra("CURRENT_LAT", lat);
            editLocationIntent.putExtra("CURRENT_LON", lon);
            TaskStackBuilder stackBuilder = TaskStackBuilder.create(context);
            stackBuilder.addParentStack(EditCoordinatesDialog.class);
            stackBuilder.addNextIntent(editLocationIntent);

            b
                    .addAction(R.drawable.ic_action_edit, "edit",
                            stackBuilder.getPendingIntent(
                                    0,
                                    Intent.FLAG_ACTIVITY_NEW_TASK
                            )
                    );
        } else {
            if (googlePlayServicesAvailable) {
                b
                        .addAction(R.drawable.ic_action_location_found, "fix",
                                PendingIntent.getBroadcast(
                                        context,
                                        0,
                                        toggleLocationFixedIntent,
                                        PendingIntent.FLAG_UPDATE_CURRENT
                                )
                        );
            }
        }

        return b;
    }
}