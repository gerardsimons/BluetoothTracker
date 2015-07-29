package com.simons.bletracker.remote;

import android.os.AsyncTask;
import android.util.Log;

import com.simons.bletracker.models.BLETag;
import com.simons.bletracker.models.Order;
import com.simons.bletracker.models.OrderCase;
import com.simons.bletracker.models.Route;

import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.UnsupportedEncodingException;
import java.util.ArrayList;
import java.util.List;

/**
 * Created by Gerard on 18-7-2015.
 *
 * This class acts as the single interface between the app and the remote server
 */
public class ServerAPI {

//    public static String SERVER_API_URL = "www.api2.whereatcloud.com";

    public static String SERVER_API_URL = "http://192.168.1.16/bletracker/api/v1/device";
    public static String API_KEY = "]wv2Np:c@e8V9@>r37g)?{18u.32lY";

    /** Request parameter keys **/
    private static final String API_KEY_KEY = "apiKey";
    private static final String REQUEST_KEY = "request";
    private static final String REQUEST_NAME_KEY = "name";
    private static final String DEVICE_ID_KEY = "deviceId";
    private static final String INSTALL_ID_KEY = "installId";
    private static final String ORDER_ID_KEY = "orderId";
    private static final String CUSTOMER_ID_KEY = "customerId";

    /** Server end-points **/
    private static final String BLE_TRACKER_ENDPOINT = "ble_tracker";
    private static final String NEW_ORDER_ENDPOINT = "order";

    private HttpClient httpClient;
    private static final String TAG = ServerAPI.class.getSimpleName();

    private static ServerAPI instance;

    private ServerAPI() {
        httpClient = new DefaultHttpClient();
    }

    public static ServerAPI GetInstance() {
        if(instance == null) {
            instance = new ServerAPI();
        }
        return instance;
    }

    private static String ConvertStreamToString(InputStream inputStream) {
        String line = "";
        StringBuilder total = new StringBuilder();
        BufferedReader rd = new BufferedReader(new InputStreamReader(inputStream));
        try {
            while ((line = rd.readLine()) != null) {
                total.append(line);
            }
        } catch (IOException ioE) {
            Log.e(TAG, "Unable to convert response inputstream to String", ioE);
        }
        return total.toString();
    }

    private void doRequest(HttpPost postRequest, ServerRequestListener listener) {
        assert listener != null;

        ServerRequestTask task = new ServerRequestTask(listener);
        task.execute(postRequest);
    }

    private static boolean RequestWasSuccesful(JSONObject response) {
        if(response != null) {
            try {
                boolean success = response.getBoolean("success");
                return success;
            } catch (JSONException e) {
                Log.e(TAG, "Unable to determine succes of response.", e);
                return false;
            }
        }
        else return false;
    }

    /********************************************************************************
     *                                  PUBLIC API                                  *
     ********************************************************************************/

    /**
     * Register the current device as a new BLE tracking controller, this device will act as a hub
     * to several BLETags.
     * @param deviceId a unique ID for the device, such as a Serial
     * @param installId a unique ID for the current installation, such as a random String created at each install
     * @return boolean indicating success of the operation
     */
    public void registerBLEController(String deviceId, String installId, ServerRequestListener listener) {
        //Create POST object
        HttpPost httpPost = new HttpPost(SERVER_API_URL + "/" + BLE_TRACKER_ENDPOINT);

        //Append API key
        //Append device and install ids
        List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(3);
        nameValuePairs.add(new BasicNameValuePair(API_KEY_KEY,API_KEY));
        nameValuePairs.add(new BasicNameValuePair(DEVICE_ID_KEY, deviceId));
        nameValuePairs.add(new BasicNameValuePair(INSTALL_ID_KEY, installId));

        try {
            httpPost.setEntity(new UrlEncodedFormEntity(nameValuePairs));
        } catch (UnsupportedEncodingException e) {
            Log.e(TAG,"Unable to create FormEntity from parameters");
        }

        //Execute, the return value is given through the listener callback
        doRequest(httpPost,listener);
    }

    public void addNewOrder(Order order) {
        HttpPost httpPost = new HttpPost(SERVER_API_URL + "/" + NEW_ORDER_ENDPOINT);
        List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(3);
        nameValuePairs.add(new BasicNameValuePair(API_KEY_KEY,API_KEY));
        nameValuePairs.add(new BasicNameValuePair(DEVICE_ID_KEY, deviceId));
        nameValuePairs.add(new BasicNameValuePair(INSTALL_ID_KEY, installId));
    }

    public void addNewOrderCase(OrderCase orderCase) {

    }

    public static OrderCase CreateOrderCase(OrderCase orderCase, BLETag tag) {
        return null;
    }

    public static Route CreateRoute() {
        return null;
    }

    public static boolean StartRoute() {
        return false;
    }

    public static boolean FinishRoute() {
        return false;
    }

    public interface ServerRequestListener {
        public void onRequestFailed();
        public void onRequestCompleted(JSONObject response);
    }

    private class ServerRequestTask extends AsyncTask<HttpPost, Void, JSONObject> {

        private Exception exception;
        private ServerRequestListener listener;

        public ServerRequestTask(ServerRequestListener listener) {
            this.listener = listener;
        }

        protected JSONObject doInBackground(HttpPost... requests) {
            if(requests.length > 1) {
                Log.e(TAG,"Only 1 request at a time supported.");
                listener.onRequestFailed();
                return null;
            }
            try {
                HttpResponse response = httpClient.execute(requests[0]);
                if(response != null) {
                    InputStream inputstream = response.getEntity().getContent();
                    String stringResponse = ConvertStreamToString(inputstream);
                    try {
                        JSONObject jsonResponse = new JSONObject(stringResponse);
                        listener.onRequestCompleted(jsonResponse);
                        return jsonResponse;
                    }
                    catch (JSONException e) {
                        Log.e(TAG,String.format("Unable to convert server response '%s' to a JSONObject",stringResponse),e);
                    }
                } else {
                    listener.onRequestFailed();
                    return null;
                }
            } catch (Exception e) {
                listener.onRequestFailed();
                this.exception = e;
            }
            return null;
        }

        protected void onPostExecute(JSONObject result) {
            if(exception != null) {
                Log.e(TAG,"ServerRequestTask failed",exception);
            }
        }
    }
}
