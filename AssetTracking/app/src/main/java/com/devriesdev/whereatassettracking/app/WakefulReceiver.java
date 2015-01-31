package com.devriesdev.whereatassettracking.app;

import android.content.Context;
import android.content.Intent;
import android.support.v4.content.WakefulBroadcastReceiver;

/**
 * Created by danie_000 on 7/10/2014.
 */
public class WakefulReceiver extends WakefulBroadcastReceiver {
    @Override
    public void onReceive(Context context, Intent intent) {
        Intent service = new Intent(context, MainService.class);
        startWakefulService(context, service);
    }
}
