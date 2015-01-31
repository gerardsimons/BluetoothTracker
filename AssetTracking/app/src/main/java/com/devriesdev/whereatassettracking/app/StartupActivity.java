package com.devriesdev.whereatassettracking.app;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;

public class StartupActivity extends Activity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        Intent service = new Intent(this, MainService.class);
        startService(service);

        finish();
    }
}
