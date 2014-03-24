package com.devriesdev.devicemanager;

import android.app.Activity;
import android.app.Fragment;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.os.Build;

import com.devriesdev.connection.Connection;

import org.json.JSONObject;

public class MainActivity extends Activity {
   private static Connection connection;

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

        connection.authIsLoggedIn(new Connection.OwnHandler() {
            @Override
            public void handle(String s) {

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
