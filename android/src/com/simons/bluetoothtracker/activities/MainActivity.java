package com.simons.bluetoothtracker.activities;

import android.app.Activity;
import android.os.Bundle;
import android.util.Log;

import com.simons.bluetoothtracker.R;
import com.simons.bluetoothtracker.connection.Connection;

public class MainActivity extends Activity {
   private static Connection connection;

    private static final String TAG = "MainActivity";

    public static Connection getConnection() {
        return connection;
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        /*LoginFragment loginFragment = new LoginFragment(this);
        loginFragment.setArguments(getIntent().getExtras());
        getFragmentManager().beginTransaction().add(R.id.loginFragmentContainer, loginFragment).commit();*/

        init();

    }

    @Override
    protected void onPause() {
        super.onPause();
    }

    @Override
    protected void onResume() {
        super.onResume();
    }

    private void init() {
        connection = new Connection(this);

        connection.authIsLoggedIn(new Connection.ConnectionRequestListener() {
            @Override
            public void onRequestFinished() {
                Log.d(TAG,"User succesfully logged in.");
            }

            @Override
            public void onRequestFailed(String errorMessage) {
                Log.d(TAG, "Login failed message = " + errorMessage);
            }
        });
    }
}
