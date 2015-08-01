package com.simons.bletracker.remote;

import android.os.AsyncTask;
import android.util.Log;

import com.simons.bletracker.models.sql.GPSMeasurement;
import com.simons.bletracker.models.sql.RSSIMeasurement;

import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.UnsupportedEncodingException;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;

/**
 * Created by Gerard on 18-7-2015.
 *
 * This class acts as the single interface between the app and the remote server
 */
public class ServerAPI {

//    public static String SERVER_API_URL = "www.api2.whereatcloud.com";

    public static String SERVER_API_URL = "http://192.168.230.152/bletracker/api/v1/device";
    public static String API_KEY = "]wv2Np:c@e8V9@>r37g)?{18u.32lY";

    /** Request parameter keys **/
    private static final String API_KEY_KEY = "apiKey";
    private static final String REQUEST_KEY = "request";
    private static final String REQUEST_NAME_KEY = "name";
    private static final String DEVICE_ID_KEY = "deviceId";
    private static final String INSTALL_ID_KEY = "installId";
    private static final String ORDER_ID_KEY = "orderId";
    private static final String ORDER_CASE_ID_KEY = "orderCaseId";
    private static final String ORDER_CASES_KEY = "orderCases";
    private static final String CUSTOMER_ID_KEY = "customerId";
    private static final String ROUTE_ID_KEY = "routeId";

    /** Nested parameter JSON keys **/
    private static final String MAC_ADDRESS_KEY = "mac";
    private static final String RSSI_KEY = "rssi";
    private static final String TIMESTAMP_KEY = "time";
    private static final String LATITUDE_KEY = "lat";
    private static final String LONGITUDE_KEY = "long";
    private static final String END_TIME_KEY = "endTime";
    private static final String START_TIME_KEY = "startTime";

    /** Server end-points **/
    private static final String BLE_TRACKER_ENDPOINT = "ble_tracker";
    private static final String ORDER_ENDPOINT = "order";
    private static final String ORDER_CASE_ENDPOINT = "order_case";
    private static final String COMPANY_ENDPOINT = "company";
    private static final String ROUTE_ENDPOINT = "route";
    private static final String TRACKING_DATA_ENDPOINT = "tracking_data";

    /** Additional verbs to be used in conjunction with end-points **/
    private static final String START_VERB = "start";
    private static final String END_VERB = "end";

    private final DateFormat serverDateTimeFormat = new SimpleDateFormat ("yyyy-MM-dd hh:mm:ss");
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

    public void addNewOrder(int orderId, ServerRequestListener listener) {
        HttpPost httpPost = new HttpPost(SERVER_API_URL + "/" + ORDER_ENDPOINT);
        List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(3);
        nameValuePairs.add(new BasicNameValuePair(API_KEY_KEY, API_KEY));
        nameValuePairs.add(new BasicNameValuePair(ORDER_ID_KEY, orderId + ""));

        try {
            httpPost.setEntity(new UrlEncodedFormEntity(nameValuePairs));
        } catch (UnsupportedEncodingException e) {
            Log.e(TAG,"Unable to create FormEntity from parameters");
        }

        //Execute, the return value is given through the listener callback
        doRequest(httpPost, listener);
    }

    public void addNewOrderCase(int orderId, int orderCaseId, ServerRequestListener listener) {
        HttpPost httpPost = new HttpPost(SERVER_API_URL + "/" + ORDER_ENDPOINT);
        List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(3);
        nameValuePairs.add(new BasicNameValuePair(API_KEY_KEY,API_KEY));
        nameValuePairs.add(new BasicNameValuePair(ORDER_ID_KEY, orderId+""));
        nameValuePairs.add(new BasicNameValuePair(ORDER_CASE_ID_KEY, orderCaseId+""));

        try {
            httpPost.setEntity(new UrlEncodedFormEntity(nameValuePairs));
        } catch (UnsupportedEncodingException e) {
            Log.e(TAG,"Unable to create FormEntity from parameters");
        }

        //Execute, the return value is given through the listener callback
        doRequest(httpPost, listener);
    }

    public void createRoute(String deviceId, String installId,int[] orderIds, int[] orderCaseIds, ServerRequestListener listener) {
        assert orderCaseIds.length == orderIds.length;

        HttpPost httpPost = new HttpPost(SERVER_API_URL + "/" + ORDER_ENDPOINT);
        List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(3);

        nameValuePairs.add(new BasicNameValuePair(API_KEY_KEY,API_KEY));
        nameValuePairs.add(new BasicNameValuePair(DEVICE_ID_KEY,deviceId));
        nameValuePairs.add(new BasicNameValuePair(INSTALL_ID_KEY,installId));

        JSONArray orderCases = new JSONArray();
        try {
            for(int i = 0 ; i < orderIds.length ; ++i) {
                JSONObject orderCaseJSON = new JSONObject();
                orderCaseJSON.put(ORDER_ID_KEY,orderIds[i]);
                orderCaseJSON.put(ORDER_CASE_ID_KEY,orderIds[i]);
                orderCases.put(orderCaseJSON);
            }
            nameValuePairs.add(new BasicNameValuePair(ORDER_CASES_KEY,orderCases.toString()));
            httpPost.setEntity(new UrlEncodedFormEntity(nameValuePairs));
        } catch (UnsupportedEncodingException e) {
            Log.e(TAG,"Unable to create FormEntity from parameters");
        } catch (JSONException e) {
            Log.e(TAG,"Unable to create JSON OrderCase array",e);
        }

        //Execute, the return value is given through the listener callback
        doRequest(httpPost, listener);
    }

    public void sendTrackingData(String deviceId, String installId, RSSIMeasurement[] rssiMeasurements, GPSMeasurement[] gpsMeasurements, ServerRequestListener listener) {
        HttpPost httpPost = new HttpPost(SERVER_API_URL + "/" + ORDER_ENDPOINT);
        List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(3);

        nameValuePairs.add(new BasicNameValuePair(API_KEY_KEY, API_KEY));
        nameValuePairs.add(new BasicNameValuePair(DEVICE_ID_KEY, deviceId));
        nameValuePairs.add(new BasicNameValuePair(INSTALL_ID_KEY, installId));

        try {
            JSONArray rssiJsonArray = new JSONArray();
            for (RSSIMeasurement rssiMeasurement : rssiMeasurements) {
                JSONObject rssiJson = new JSONObject();

                rssiJson.put(MAC_ADDRESS_KEY, rssiMeasurement.getMacAddress());
                rssiJson.put(RSSI_KEY, rssiMeasurement.rssi);
                rssiJson.put(TIMESTAMP_KEY, rssiMeasurement.timestamp);

                rssiJsonArray.put(rssiJson);
            }

            for (GPSMeasurement gpsMeasurement : gpsMeasurements) {
                JSONObject rssiJson = new JSONObject();

                rssiJson.put(LATITUDE_KEY, gpsMeasurement.latitude);
                rssiJson.put(LONGITUDE_KEY, gpsMeasurement.longitude);
                rssiJson.put(TIMESTAMP_KEY, gpsMeasurement.timestamp);

                rssiJsonArray.put(rssiJson);
            }

        } catch (JSONException e) {
            e.printStackTrace();
        }

        //Execute, the return value is given through the listener callback
        doRequest(httpPost, listener);
    }

    public void finishOrderCase(int orderCaseId, int orderId, Date finishTime, ServerRequestListener listener) {
        HttpPost httpPost = new HttpPost(SERVER_API_URL + "/" + ORDER_CASE_ENDPOINT + "/" + END_VERB);
        List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(3);

        nameValuePairs.add(new BasicNameValuePair(API_KEY_KEY, API_KEY));
        nameValuePairs.add(new BasicNameValuePair(ORDER_ID_KEY, orderId + ""));
        nameValuePairs.add(new BasicNameValuePair(ORDER_CASE_ID_KEY, orderCaseId + ""));

        //Execute, the return value is given through the listener callback
        doRequest(httpPost, listener);
    }

    public void startRoute(int routeId, Date startTime, ServerRequestListener listener) {
        HttpPost httpPost = new HttpPost(SERVER_API_URL + "/" + ROUTE_ENDPOINT + "/" + START_VERB);
        List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(3);

        nameValuePairs.add(new BasicNameValuePair(API_KEY_KEY, API_KEY));
        nameValuePairs.add(new BasicNameValuePair(ROUTE_ID_KEY,routeId+""));
        nameValuePairs.add(new BasicNameValuePair(START_TIME_KEY, serverDateTimeFormat.format(startTime)));

        //Execute, the return value is given through the listener callback
        doRequest(httpPost, listener);
    }

    public void finishRoute() {
        throw new RuntimeException("Stub");
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
