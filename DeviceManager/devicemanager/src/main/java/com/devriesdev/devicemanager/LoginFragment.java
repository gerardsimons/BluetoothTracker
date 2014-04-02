package com.devriesdev.devicemanager;

//import org.json.JSONArray;

//import com.loopj.android.http.JsonHttpResponseHandler;

import android.app.Fragment;
import android.app.FragmentTransaction;
import android.os.Bundle;
import android.content.Context;
import android.content.SharedPreferences;
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

import com.devriesdev.connection.Connection;

import org.json.JSONException;
import org.json.JSONObject;
//import android.widget.TextView;

public class LoginFragment extends Fragment {
    private static final String TAG = "LoginFragment";

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

    private com.devriesdev.utils.Dialog dialog;

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

        dialog = new com.devriesdev.utils.Dialog(context);


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
                try {
                    sEmail = editEmail.getText().toString();
                    sPass = editPass.getText().toString();
                    connection.doRequest(new Connection.OwnHandler() {
                        @Override
                        public void handle(Object o) {
                            loginRequest(sEmail, sPass);
                        }
                    }, "status.status");
                } catch (NullPointerException e) {
                    Log.v(TAG, "No input given");

                }
            }
        });

        bTestLoggedIn.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                connection.authIsLoggedIn(new Connection.OwnHandler() {
                    @Override
                    public void handle(Object o) {
                        if (((String)o).equals("1")) {
                            dialog.show("Logged in!");
                        } else {
                            dialog.show("Not logged in!");
                        }
                    }
                });
                //connection.authGetUserId();
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
                connection.doRequest(new Connection.OwnHandler() {
                    @Override
                    public void handle(Object o) {
                        loginRequest("asdf@asdf.com", "test");
                    }
                }, "status.status");
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

    public void loginRequest(String email, String pass) {
        connection.authLogin(new Connection.OwnHandler() {
            @Override
            public void handle(Object o) {
                if (o instanceof String) {
                    String[] s = ((String)o).split("\n");
                    int i = 0;
                    while (s[i].contains("<br />")) {i++;}
                    if (i < s.length) {
                        try {
                            //Handle the output with the given Handler is response is JSON
                            o = new JSONObject(s[i]);
                        } catch (JSONException e) {
                            //
                        }
                    }
                }
                if (o instanceof JSONObject) {
                    try {
                        JSONObject json = (JSONObject) o;
                        Log.v(TAG, "Got JSONObject!: \n" + json.toString());
                        if (json.getInt("result") == 1) {
                            connection.setSessionId(json.getString("sessionid"));
                            connection.setLoginKey(json.getString("loginkey"));
                            connection.setUserId(json.getString("userid"));
                            connection.setUserName(json.getString("username"));

                            Log.v(TAG, "Logged in; starting list fragment!");
                            dialog.show("Succesfully logged in!");

                            startListFragment();
                        } else {
                            dialog.show("Invalid username/password provided!");
                        }
                    } catch (JSONException e) {
                        dialog.show("Oops... Something went wrong");
                    }
                } else {
                    Log.v(TAG, "Something went wrong: authIsLoggedIn did not return JSONObject");
                    Log.v(TAG, o.toString());
                    dialog.show("Oops... Something went wrong");
                }
            }
        }, email, pass);
    }

    private void startListFragment() {
        // Create new fragment and transaction
        Fragment newFragment = new ListFragment();
        FragmentTransaction transaction = getFragmentManager().beginTransaction();

        // Replace whatever is in the fragment_container view with this fragment,
        // and add the transaction to the back stack
        transaction.setTransition(FragmentTransaction.TRANSIT_FRAGMENT_FADE);
        transaction.replace(R.id.fragmentContainer, newFragment);
        transaction.addToBackStack(null);

        // Commit the transaction
        transaction.commit();
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
