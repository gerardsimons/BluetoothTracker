package com.simons.bluetoothtracker.connection;

import android.content.Context;
import android.net.wifi.WifiManager;
import android.text.TextUtils;
import android.util.Log;

import com.loopj.android.http.AsyncHttpClient;
import com.loopj.android.http.TextHttpResponseHandler;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;


/**
 * This class handles all the communication with the API.
 */
public class Connection {
    private static final String BASE_URL = "http://api.whereatcloud.com/?";
    private static final String TAG = "ConClass";
    private static final String apiKey = "wCyfzy3KFURWBQqUpZQXu58f";

    private static AsyncHttpClient client = new AsyncHttpClient();
    private static Context context;
    private static String macHash;
    private static WifiManager wifiManager;

    private String sessionId;
    private String loginKey;
    private String userId;
    private String userName;
    private String serverTimestamp;

    public interface ConnectionRequestListener {
        public void onRequestFinished();
        public void onRequestFailed(String errorMessage);
    }

    //Initialized the connection with the API
    //-First wifi is forced to be on
    //-Then the macaddress is found and hashed using MD5
    //-Then the status of the API is checked
    public Connection(Context context) {
        //Get the application context
        this.context = context;

        //Force wifi on
        wifiManager = (WifiManager) context.getSystemService(Context.WIFI_SERVICE);
        if (!wifiManager.isWifiEnabled()) {
            wifiManager.setWifiEnabled(true);
        }

        //Get the MAC address
        String macAddress = wifiManager.getConnectionInfo().getMacAddress();
        if (macAddress == null) {
            throw new NullPointerException("getMacAddress() returned null.");
        }

        //Hash the MAC address using MD5
        macHash = md5Hash(macAddress);

        //If something goes wrong a new RuntimeException is thrown
        if (macHash == null) {
            throw new RuntimeException("Error hashing the mac address.");
        }

        //Check the status of the API
        getStatus(null);
    }

    private String md5Hash(String str) {
        try {
            byte[] macBytes = str.getBytes("UTF-8");
            MessageDigest md = MessageDigest.getInstance("MD5");
            byte[] hashBytes = md.digest(macBytes);
            str = hashBytes.toString();
        } catch (UnsupportedEncodingException e) {
            return null;
        } catch (NoSuchAlgorithmException e) {
            return null;
        }
        return str;
    }

    //Returns the request URL to perform function with inputs
    private String getRequestUrl(String function, String... input) {
        String url = BASE_URL + "apikey=" + apiKey;

        if (sessionId != null) {
            url += "&sessionid=" + sessionId;
        }

        url += "&function=" + function;

        if (input.length > 0) {
            url += "&input=";
            try {
                for (int i = 0; i < input.length; i++) {
                    input[i] = URLEncoder.encode(input[i], "UTF-8");
                }
                url += URLEncoder.encode(TextUtils.join("&", input), "UTF-8");
            } catch (UnsupportedEncodingException e) {return null;}
        }

        return url;
    }

    //Performs the HTTP request to the API
    private void doRequest(final Handler handler,final ConnectionRequestListener callback, String function, String... input) {
        //Construct the request URL
        String url = getRequestUrl(function, input);
        if (url != null) {
            //Log.v(TAG, "URL: " + url);
            //Perform the request
            client.get(url, null, new TextHttpResponseHandler() {
                @Override
                public void onSuccess(int statusCode, org.apache.http.Header[] headers, String responseBody) {
                    try {
                        //Handle the output with the given Handler is response is JSON
                        handler.handle(new JSONObject(responseBody));
                        if(callback != null) {
                            callback.onRequestFinished();
                        }
                    } catch (JSONException e) {
                        //If response is not JSON handle the responseBody String
                        handler.handle(responseBody);
                        if(callback != null) {
                            callback.onRequestFailed(e.getMessage());
                        }

                    }
                }
            });
        } else {
            Log.v(TAG, "Unsupported URL Encoding.");
        }
    }

    //Performs the HTTP request to the API
    private void doRequest(final OwnHandler handler, String function, String... input) {
        //Construct the request URL
        String url = getRequestUrl(function, input);
        if (url != null) {
            //Log.v(TAG, "URL: " + url);
            //Perform the request
            client.get(url, null, new TextHttpResponseHandler() {
                @Override
                public void onSuccess(int statusCode, org.apache.http.Header[] headers, String responseBody) {
                    try {
                        //Handle the output with the given Handler is response is JSON
                        handler.handle(new JSONObject(responseBody));
                    } catch (JSONException e) {
                        //If response is not JSON handle the responseBody String
                        handler.handle(responseBody);
                    }
                }
            });
        } else {
            Log.v(TAG, "Unsupported URL Encoding.");
        }
    }

    public interface OwnHandler {
        public void handle(String s);
        public void handle(JSONObject j);
    }

    public abstract class Handler {
        public void handle(JSONObject json) {
            try {
                if (!json.has("error")) {
                    Log.v(TAG, json.toString());
                    execute(json);
                } else {
                    //Handle error
                }
            } catch (JSONException e) {Log.e(TAG, e.toString());}
        }

        public void handle(String str) {};

        public void execute(JSONObject json) throws JSONException {};
    }



    private class GetStatusHandler extends Handler {
        public void execute(JSONObject json) throws JSONException {
            sessionId = json.getString("sessionid");
            serverTimestamp = json.getString("timestamp");
        }
    }

    public void getStatus(ConnectionRequestListener callback) {
        doRequest(new GetStatusHandler(), callback, "status.status");
    }

    /*
    Auth Class
     */
    public class AuthIsLoggedInHandler extends Handler {
        public void handle(String str) {
            if (str.equals("1")) {
                Log.v(TAG, "Logged in!");
            } else {
                Log.v(TAG, "Not logged in!");
            }
        }
    }

    private class AuthGetUserIdHandler extends Handler {
        public void handle(String str) {
            if (str.equals("0")) {
                Log.v(TAG, "Not logged in!");
            } else {
                Log.v(TAG, "User ID: " + str);
            }
        }
    }

    private class AuthLoginHandler extends Handler {
        public void execute(JSONObject json) throws JSONException {
            if (json.getInt("result") == 1) {
                sessionId = json.getString("sessionid");
                loginKey = json.getString("loginkey");
                userId = json.getString("userid");
                userName = json.getString("username");
            }
        }
    }

    private class AuthLogoutHandler extends Handler {
        public void handle(String str) {
            if (str.equals("1")) {
                loginKey = null;
                Log.v(TAG, "Logged out!");
            } else {
                Log.v(TAG, "auth.logout returned false. API broken.");
            }
        }
    }

    private class AuthAutoLoginHandler extends AuthLoginHandler {}

    private class AuthRegisterHandler extends Handler {
        public void execute(JSONObject json) throws JSONException {
            if (json.getInt("result") == 1) {
                userId = json.getString("userid");
                userName = json.getString("username");
                Log.v(TAG, "Successfully registered: userID=" + userId + ", userName=" + userName);
            } else {
                Log.v(TAG, "Registration unsuccessful: " + json.getString("description"));
            }
        }
    }

    private class AuthExistsHandler extends Handler {
        public void handle(String str) {
            if (str.equals("1")) {
                Log.v(TAG, "User exists.");
            } else {
                Log.v(TAG, "User does not exist.");
            }
        }
    }

    private class AuthGetRegTypesHandler extends Handler {
        public void execute(JSONObject json) throws JSONException {
            Log.v(TAG, json.toString());
        }
    }


    public void authIsLoggedIn(ConnectionRequestListener callback) {
        doRequest(new AuthIsLoggedInHandler(), callback, "auth.isloggedin");
    }

    public void authGetUserId(ConnectionRequestListener callback) {
        doRequest(new AuthGetUserIdHandler(), callback, "auth.getuserid");
    }

    public void authLogin(String email, String pass, ConnectionRequestListener callback, boolean... remember) {
        String[] input = {email, pass, macHash, "false"};
        if (remember.length > 0) {
            if (remember[0]) {
                input[3] = "true";
            }
        }

        doRequest(new AuthLoginHandler(), callback, "auth.login", input);
    }

    public void authLogout(ConnectionRequestListener callback) {
        doRequest(new AuthLogoutHandler(), callback, "auth.logout");
    }

    public void authAutoLogin(ConnectionRequestListener callback) {
        String hash = loginKey + apiKey + macHash + userName + serverTimestamp;
        hash = md5Hash(hash);
        doRequest(new AuthAutoLoginHandler(), callback, "auth.autologin", userId, loginKey, hash);
    }

    public void authRegister(ConnectionRequestListener callback, String userName, String email, String name, String pass, String regType, String[]... data) {
        doRequest(new AuthRegisterHandler(), callback, "auth.register", userName, email, name, pass, regType);
    }

    public void authExists(String userName, ConnectionRequestListener callback) {
        doRequest(new AuthExistsHandler(), callback, "auth.exists", userName);
    }

    public void authGetRegTypes(ConnectionRequestListener callback) {
        doRequest(new AuthGetRegTypesHandler(), callback, "auth.getregtypex");
    }

    public String getServerTimestamp() {
        return serverTimestamp;
    }

    public String getSessionId() {
        return sessionId;
    }

    public String getLoginKey() {
        return loginKey;
    }

    public String getUserId() {
        return userId;
    }

    public String getUserName() {
        return userName;
    }
}
