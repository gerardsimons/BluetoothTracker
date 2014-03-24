package com.devriesdev.devicemanager;

//import org.json.JSONArray;

//import com.loopj.android.http.JsonHttpResponseHandler;

import android.app.Activity;
import android.app.Fragment;
import android.os.Bundle;
import android.content.Context;
import android.content.SharedPreferences;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.CompoundButton;
import android.widget.CompoundButton.OnCheckedChangeListener;
import android.widget.EditText;

import com.devriesdev.connection.Connection;
//import android.widget.TextView;

public class LoginFragment extends Fragment {

    private Context context;
    private static SharedPreferences sharedPrefs;

    private static final String KEY_MESSAGE = "KEY_MESSAGE";

    private static final String KEY_REMEMBER = "KEY_REMEMBER";
    private static final String KEY_STAY = "KEY_STAY";
    private static final String KEY_EMAIL = "KEY_EMAIL";
    private static final String KEY_PASS = "KEY_PASS";

    private static Connection connection;
    private View v;

    private boolean bRemember = false;
    private boolean bStay = false;
    private String sEmail = "";
    private String sPass = "";

    static EditText editEmail;
    static EditText editPass;
    static CheckBox checkRemember;
    static CheckBox checkStay;
    static Button bLogin;

    public LoginFragment() {
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        v = inflater.inflate(R.layout.fragment_login, container, false);
        return v;
    }

    @Override
    public void onActivityCreated(Bundle savedInstanceState) {
        super.onActivityCreated(savedInstanceState);
        //setContentView(R.layout.activity_login);

        context = getActivity();
        connection = ((MainActivity) context).getConnection();

        //connection = new Connection(context);


        editEmail = (EditText) v.findViewById(R.id.editEmail);
        editPass = (EditText) v.findViewById(R.id.editPass);
        checkRemember = (CheckBox) v.findViewById(R.id.checkRemember);
        checkStay = (CheckBox) v.findViewById(R.id.checkStay);
        bLogin = (Button) v.findViewById(R.id.buttonLogin);
        Button bTestLoggedIn = (Button) v.findViewById(R.id.bTestLoggedIn);

        checkStay.setOnCheckedChangeListener(new OnCheckedChangeListener() {
            @Override
            public void onCheckedChanged(CompoundButton buttonView, boolean isChecked) {
                checkRemember.setChecked(isChecked);
                checkRemember.setEnabled(!isChecked);
                //checkRemember.setActivated(isChecked);
            }
        });

        bLogin.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                sEmail = editEmail.getText().toString();
                sPass = editPass.getText().toString();
                login();
                connection.authLogin(sEmail, sPass);
            }
        });

        bTestLoggedIn.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                connection.authIsLoggedIn();
                connection.authGetUserId();
            }
        });

        /*sharedPrefs = getSharedPreferences("loginPrefs", MODE_PRIVATE);
        bRemember = sharedPrefs.getBoolean(KEY_REMEMBER, false);
        //bStay = sharedPrefs.getBoolean(KEY_STAY, false);
        sEmail = sharedPrefs.getString(KEY_EMAIL, null);
        //sPass = sharedPrefs.getString(KEY_PASS, null);
        editEmail.setText(sEmail);*/
//--------------------------------------------------------------------------------------------------
        ((Button)v.findViewById(R.id.bTestLogin)).setOnClickListener(new OnClickListener() {

            @Override
            public void onClick(View v) {
                Log.v("Connection", "Klik!");
                connection.authLogin("asdf@asdf.com", "test");
            }
        });
//--------------------------------------------------------------------------------------------------

        /*Bundle extras = getIntent().getExtras();
        if (extras != null) {
            if (extras.getString(KEY_MESSAGE) == "E001") {
                //LAAT FF ZIEN DAH HET FOUT IS GEGAAN JONGUH!
            }
        }*/
    }

    @Override
    public void onPause() {
        super.onPause();
    }

    @Override
    public void onResume() {
        super.onResume();
    }

    /*@Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        //getMenuInflater().inflate(R.menu.login, menu);
        return true;
    }*/

    public void login() {
        SharedPreferences.Editor ed = sharedPrefs.edit();
        ed.putBoolean(KEY_REMEMBER, true);
        ed.putString(KEY_EMAIL, sEmail);
        ed.commit();

        /*Intent intent = new Intent(context, ListActivity.class);
        intent.putExtra(KEY_EMAIL, sEmail);
        intent.putExtra(KEY_PASS, sPass);*/

        //startActivity(intent);

    }

    /*@Override
    public void onPause() {
        super.onPause();
    	/*SharedPreferences.Editor ed = sharedPrefs.edit();
    	if (bRemember){
    		ed.putBoolean(KEY_REMEMBER, true);
    		ed.putString(KEY_EMAIL, sEmail);
    		ed.putString(KEY_PASS, sPass);
    	}
    	if (bStay) {
    		ed.putBoolean(KEY_STAY, true);
    	}
    	ed.commit();*/
    //}


}
