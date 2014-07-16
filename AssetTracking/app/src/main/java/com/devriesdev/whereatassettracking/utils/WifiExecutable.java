package com.devriesdev.whereatassettracking.utils;

import android.content.Context;
import android.net.wifi.WifiConfiguration;
import android.net.wifi.WifiInfo;
import android.net.wifi.WifiManager;
import android.os.Environment;
import android.util.Log;

import com.devriesdev.whereatassettracking.app.R;
import com.loopj.android.http.AsyncHttpClient;
import com.loopj.android.http.BinaryHttpResponseHandler;
import com.loopj.android.http.FileAsyncHttpResponseHandler;
import com.loopj.android.http.TextHttpResponseHandler;

import org.apache.http.Header;

import java.io.DataInputStream;
import java.io.DataOutputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOError;
import java.io.IOException;
import java.io.OutputStream;
import java.util.List;

/**
 * Created by danie_000 on 6/15/2014.
 */
public class WifiExecutable implements Utils.Executable {

    private final String TAG = "WifiExecutable";

    private Context context;
    private Callback callback;
    private AsyncHttpClient client;
    private String baseURL;

    private WifiManager wifiManager;

    public WifiExecutable(Context context, AsyncHttpClient client, String baseURL, Callback callback) {
        this.context = context;
        this.client = client;
        this.baseURL = baseURL;
        this.callback = callback;
    }

    @Override
    public void execute() {
        Log.w(TAG, "Executing wifi executable");
        wifiManager = (WifiManager) context.getSystemService(Context.WIFI_SERVICE);
        while (!wifiManager.isWifiEnabled()) {
            Log.w(TAG, "wifi isn't enabled yet");
            try {
                wifiManager.setWifiEnabled(true);
                Log.w(TAG, "Enabling wifi, going to sleep...");
                Thread.sleep(500);
            } catch (InterruptedException e) {
                e.printStackTrace();
            }
        }

        Log.w(TAG, "wifi was enabled");
        if (wifiManager.getConnectionInfo().getIpAddress() == 0) {
            if (wifiManager.getConfiguredNetworks().size() > 0) {
                while (wifiManager.getConnectionInfo().getIpAddress() == 0) {
                    try {
                        Log.w(TAG, "connecting to a WiFi network, going to sleep...");
                        Thread.sleep(500);
                    } catch (InterruptedException e) {
                        e.printStackTrace();
                    }
                }

                WifiInfo wInfo = wifiManager.getConnectionInfo();
                Log.w(TAG, "Connected to WiFi. Network info: \n" +
                        "SSID: " + wInfo.getSSID() + "\n" +
                        "MAC: " + wInfo.getMacAddress() + "\n" +
                        "IP: " + wInfo.getIpAddress() + "\n" +
                        "link speed: " + wInfo.getLinkSpeed() + "\n" +
                        "network ID: " + wInfo.getNetworkId() + "\n" +
                        "RSSI: " + wInfo.getRssi() + "\n");

            } else {
                Log.w(TAG, "there were no configured networks... that sucks");
            }
        } else {
            wifiManager.reassociate();
        }
    }

    @Override
    public void postExecute() {
        Log.w(TAG, "wifi executable is at postExecute");
        Log.w(TAG, "Checking for updates");

        client.get(context, baseURL + "getlastversion.php", new TextHttpResponseHandler() {
            @Override
            public void onFailure(
                    int statusCode,
                    org.apache.http.Header[] headers,
                    String responseBody,
                    Throwable error) {
                Log.w(TAG, "Something went wrong when requesting last version. \n" +
                        "Server response: " + responseBody + "\n" +
                        "Error: " + error.getMessage());
            }

            @Override
            public void onSuccess(
                    int statusCode,
                    org.apache.http.Header[] headers,
                    String responseBody) {
                Log.w(TAG, "Got a response from the server: " + responseBody);
                if (responseBody.trim().isEmpty()) {
                    Log.w(TAG, "Got an empty response, gonna assume we are up to date");
                    getUnitID();
                } else {
                    int lastVersion = Integer.valueOf(responseBody.trim());
                    int thisVersion = context.getResources().getInteger(R.integer.version);
                    if (lastVersion > thisVersion) {
                        Log.w(TAG, "New version available: V" + lastVersion);
                        update();
                    } else {
                        Log.w(TAG, "We are up to date. Starting app.");
                        getUnitID();
                    }
                }
            }
        });
    }

    private void update() {
        Log.w(TAG, "Updating the app");
        String[] allowedContentTypes = new String[] {".*"};
        client.get(context, baseURL + "app-release.apk", new BinaryHttpResponseHandler(allowedContentTypes) {
            @Override
            public void onSuccess(int statusCode, Header[] headers, byte[] binaryData) {
                Log.w(TAG, "Got something from the server when downloading the update!");

                String path = Environment.getExternalStorageDirectory().getPath() + "/";
                try {
                    File file = new File(path + "app-release.apk");
                    OutputStream f = new FileOutputStream(file);
                    f.write(binaryData); //your bytes
                    f.close();

                    Process p = Runtime.getRuntime().exec("su");

                    DataOutputStream stdin = new DataOutputStream(p.getOutputStream());
                    stdin.writeBytes(
                            "pm install -r " + path + "app-release.apk \n" +
                            "am start -n \"com.devriesdev.whereatassettracking.app/.StartupActivity\" -a android.intent.action.MAIN -c android.intent.category.LAUNCHER \n"
                    );

                } catch (IOException e) {
                    Log.w(TAG, "Something went wrong: we got an IOException when trying to install the apk");
                    e.printStackTrace();
                }
            }
        });
    }

    private void getUnitID() {
        String mac = wifiManager.getConnectionInfo().getMacAddress().toUpperCase();
        Log.w(TAG, "Gonna request unitID with this URL: " + baseURL + "getunitid.php?mac=" + mac);
        client.get(context, baseURL + "getunitid.php?mac=" + mac, new TextHttpResponseHandler() {
            @Override
            public void onSuccess(int statusCode, org.apache.http.Header[] headers, String responseBody) {
                Log.w(TAG, "Got a response from the server: " + responseBody);
                if (!responseBody.trim().isEmpty()) {
                    callback.call(Integer.valueOf(responseBody));
                } else {
                    callback.call(-1);
                }
            }
        });
    }
}
