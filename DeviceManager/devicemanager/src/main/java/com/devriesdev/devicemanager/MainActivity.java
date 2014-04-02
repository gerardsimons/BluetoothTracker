package com.devriesdev.devicemanager;

import android.app.Activity;
import android.app.Fragment;
import android.app.FragmentTransaction;
import android.os.Bundle;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.os.Build;

import com.devriesdev.connection.Connection;

import org.json.JSONObject;

public class MainActivity extends Activity {
    private static final String TAG = "MainActivity";
    private Fragment loginFragment;
    private Fragment listFragment;

    private static Connection connection;

    public static Connection getConnection() {
        return connection;
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        LoginFragment loginFragment = new LoginFragment();
        loginFragment.setArguments(getIntent().getExtras());
        getFragmentManager().beginTransaction().add(R.id.fragmentContainer, loginFragment).commit();

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

        connection.getStatus();

        connection.authIsLoggedIn(new Connection.OwnHandler() {
            @Override
            public void handle(Object o) {
                if (o instanceof String) {
                    if (o.equals("1")) {
                        Log.v(TAG, "Logged in!");
                        listFragment = new ListFragment();
                        FragmentTransaction transaction = getFragmentManager().beginTransaction();

                        transaction.replace(R.id.fragmentContainer, listFragment);
                        transaction.addToBackStack(null);

                        transaction.commit();
                    } else {
                        Log.v(TAG, "Not logged in!");
                    }
                } else {
                    Log.v(TAG, "Something went wrong: authIsLoggedIn did not return String");
                }
            }
        });
    }


   /* @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        
        // Inflate the menu; this adds items to the action bar if it is present.
        //getMenuInflater().inflate(R.menu.main, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();
        if (id == R.id.action_settings) {
            return true;
        }
        return super.onOptionsItemSelected(item);
    }*/
}
