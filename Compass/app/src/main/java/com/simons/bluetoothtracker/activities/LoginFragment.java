package com.simons.bluetoothtracker.activities;

//import org.json.JSONArray;

//import com.loopj.android.http.JsonHttpResponseHandler;

import android.app.Fragment;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.CompoundButton;
import android.widget.CompoundButton.OnCheckedChangeListener;
import android.widget.EditText;

import com.simons.bluetoothtracker.BluetoothTrackerApplication;
import com.simons.bluetoothtracker.R;
import com.simons.bluetoothtracker.settings.UserSettings;
import com.simons.bluetoothtracker.connection.Connection;


//import android.widget.TextView;

public class LoginFragment extends Fragment {

    private static final String TAG = "LoginFragment";

    private Context context;

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

    private BluetoothTrackerApplication application;

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
        application = (BluetoothTrackerApplication) getActivity().getApplication();

        UserSettings userSettings = application.loadUserSettings();

        connection = ((MainActivity) context).getConnection();

        //connection = new Connection(context);


        editEmail = (EditText) v.findViewById(R.id.editEmail);
        editPass = (EditText) v.findViewById(R.id.editPass);
        checkRemember = (CheckBox) v.findViewById(R.id.checkRemember);
        checkStay = (CheckBox) v.findViewById(R.id.checkStay);
        bLogin = (Button) v.findViewById(R.id.buttonLogin);
        Button bTestLoggedIn = (Button) v.findViewById(R.id.bTestLoggedIn);

        if(userSettings != null) {
            editEmail.setText(userSettings.email);
            editPass.setText(userSettings.pass);
            checkRemember.setChecked(userSettings.rememberMe);
            checkStay.setChecked(userSettings.stayLoggedIn);
        }

        checkStay.setOnCheckedChangeListener(new OnCheckedChangeListener() {
            @Override
            public void onCheckedChanged(CompoundButton buttonView, boolean isChecked) {
                checkRemember.setChecked(isChecked);
                checkRemember.setEnabled(!isChecked);
                //checkRemember.setActivated(isChecked);
            }
        });

        bLogin.setOnClickListener(new OnClickListener() {
            @Override
            public void onClick(View v) {
                sEmail = editEmail.getText().toString();
                sPass = editPass.getText().toString();
                bRemember = checkRemember.isChecked();
                bStay = checkStay.isChecked();
                if(checkRemember.isChecked()) {
                    application.storeUserSettings(sEmail, sPass, bRemember, bStay);
                }
                connection.authLogin(sEmail, sPass, new Connection.ConnectionRequestListener() {
                    @Override
                    public void onRequestFinished() {
                        //Success
                        Log.d(TAG,"sessionId = " + connection.getSessionId());

//                        Intent intent = new Intent(context,ListActivity.class);
                        Intent intent = new Intent(context,MenuActivity.class);

                        startActivity(intent);
                    }

                    @Override
                    public void onRequestFailed(String errorMessage) {
                        Log.e(TAG,"error = " + errorMessage);
                    }
                });
            }
        });

        bTestLoggedIn.setOnClickListener(new OnClickListener() {
            @Override
            public void onClick(View v) {
                connection.authIsLoggedIn(null);
                connection.authGetUserId(null);
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
                connection.authLogin("asdf@asdf.com", "test", new Connection.ConnectionRequestListener() {
                    @Override
                    public void onRequestFinished() {
                        //Success
                    }

                    @Override
                    public void onRequestFailed(String errorMessage) {
                        //Failure
                    }
                });
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
}
