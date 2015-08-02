package com.simons.bletracker.remote;

import android.os.AsyncTask;
import android.util.Log;

import com.simons.bletracker.Configuration;
import com.simons.bletracker.models.sql.GPSMeasurement;
import com.simons.bletracker.models.sql.RSSIMeasurement;

import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.client.methods.HttpUriRequest;
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
import java.net.URLEncoder;
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

    //Some of the parameters are in the configuration file

    /** Collection of keys used as post parameters or as nested JSON keys for a POST JSONObject parameter
     *  These are only used internally
     **/
    private static class PostKeys {
        /**
         * Request parameter keys *
         */
        private static final String API = "apiKey";
        private static final String DEVICE_ID = "deviceId";
        private static final String INSTALL_ID = "installId";
        private static final String ORDER_ID = "orderId";
        private static final String ORDER_CASE_ID = "orderCaseId";
        private static final String ORDER_CASES = "orderCases";
        private static final String CUSTOMER_ID = "customerId";
        private static final String ROUTE_ID = "routeId";
        private static final String BAR_CODE = "barCode";
        private static final String BLE_TAG_MAC_ADDRESS = "bleTagMacAddress";

        /**
         * Nested parameter JSON keys used in POSTs *
         */
        private static final String MAC_ADDRESS = "mac";
        private static final String RSSI = "rssi";
        private static final String TIMESTAMP = "time";
        private static final String LATITUDE = "lat";
        private static final String LONGITUDE = "long";
        private static final String END_TIME = "endTime";
        private static final String START_TIME = "startTime";
        private static final String RSSI_DATA = "rssiData";
        private static final String GPS_DATA = "gpsData";
        private static final String SENSOR_DATA = "sensorData";
    }

    /** Public keys for JSONs returned by GET request, given in our SQL naming convention
     *  They are public as they are required in ServerRequestListener callback function(s)
     **/
    public static class GetKeys {
        public static final String ID = "ID";
        public static final String CUSTOMER_ID = "Customer_ID";
        public static final String CREATED = "Created";
        public static final String LOCATION_ID = "Location_ID";
        public static final String NAME = "Name";
        public static final String LATITUDE = "Latitude";
        public static final String LONGITUDE = "Longitude";
        public static final String STREET = "Street";
        public static final String STREET_NUMBER = "Street_Number";
        public static final String CITY = "City";
        public static final String ZIP_CODE = "Zip_Code";
    }

    /** Server end-points **/
    private static class EndPoints {
        private static final String BLE_TRACKER = "ble_tracker";
        private static final String ORDER = "order";
        private static final String ORDER_CASE = "order_case";
        private static final String COMPANY = "company";
        private static final String ROUTE = "route";
        private static final String TRACKING_DATA = "tracking_data";
    }

    /** Additional verbs to be used in conjunction with end-points **/
    private static final String START_VERB = "start";
    private static final String END_VERB = "end";

    /** The date format the server uses **/
    public static final DateFormat ServerDateTimeFormat = new SimpleDateFormat ("yyyy-MM-dd hh:mm:ss");

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

    private void doRequest(HttpUriRequest request, ServerRequestListener listener) {
        if(listener != null) {
            ServerRequestTask task = new ServerRequestTask(listener);
            task.execute(request);
        }
        else Log.e(TAG,"Listener was null");
    }

    /********************************************************************************
     *                                  PUBLIC API                                  *
     ********************************************************************************/

    /**         GET REQUESTS         **/

    /**
     * Get the entire order record for the given order id
     * @param orderId the order whose record we want
     * @param listener the callback object
     */
    public void getOrder(int orderId, ServerRequestListener listener) {
        String url = Configuration.SERVER_API_URL + EndPoints.ORDER + "/";

        try {
            final String format = "utf-8";

            //Note the encoding should only happen on query values!
            url += "?" + PostKeys.API + "=" + URLEncoder.encode(Configuration.API_KEY,format);
            url += "&" + PostKeys.ORDER_ID + "=" + orderId;

            Log.d(TAG,"url = " + url);
            doRequest(new HttpGet(url), listener);
        } catch (UnsupportedEncodingException e) {
            Log.e(TAG,"Unable to encode url = " + url);
        }

    }

    /**
     * Register the current device as a new BLE tracking controller, this device will act as a hub
     * to several BLETags.
     * @param deviceId a unique ID for the device, such as a Serial
     * @param installId a unique ID for the current installation, such as a random String created at each install
     * @return boolean indicating success of the operation
     */
    public void registerBLETracker(String deviceId, String installId, ServerRequestListener listener) {
        //Create POST object
        HttpPost httpPost = new HttpPost(Configuration.SERVER_API_URL + "/" + EndPoints.BLE_TRACKER);

        //Append API key
        //Append device and install ids
        List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(3);
        nameValuePairs.add(new BasicNameValuePair(PostKeys.API,Configuration.API_KEY));
        nameValuePairs.add(new BasicNameValuePair(PostKeys.DEVICE_ID, deviceId));
        nameValuePairs.add(new BasicNameValuePair(PostKeys.INSTALL_ID, installId));

        try {
            httpPost.setEntity(new UrlEncodedFormEntity(nameValuePairs));
        } catch (UnsupportedEncodingException e) {
            Log.e(TAG,"Unable to create FormEntity from parameters");
        }

        //Execute, the return value is given through the listener callback
        doRequest(httpPost,listener);
    }

    public void addNewOrder(int orderId, int customerId, ServerRequestListener listener) {
        HttpPost httpPost = new HttpPost(Configuration.SERVER_API_URL + EndPoints.ORDER);

        List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(3);
        nameValuePairs.add(new BasicNameValuePair(PostKeys.API, Configuration.API_KEY));
        nameValuePairs.add(new BasicNameValuePair(PostKeys.ORDER_ID, orderId + ""));
        nameValuePairs.add(new BasicNameValuePair(PostKeys.CUSTOMER_ID, customerId+ ""));

        try {
            httpPost.setEntity(new UrlEncodedFormEntity(nameValuePairs));
        } catch (UnsupportedEncodingException e) {
            Log.e(TAG,"Unable to create FormEntity from parameters");
        }

        //Execute, the return value is given through the listener callback
        doRequest(httpPost, listener);
    }

    public void addNewOrderCase(int orderId, int orderCaseId, String bleTagMac, String barcode, ServerRequestListener listener) {
        HttpPost httpPost = new HttpPost(Configuration.SERVER_API_URL + EndPoints.ORDER_CASE);

        List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(3);
        nameValuePairs.add(new BasicNameValuePair(PostKeys.API,Configuration.API_KEY));
        nameValuePairs.add(new BasicNameValuePair(PostKeys.ORDER_ID, orderId+""));
        nameValuePairs.add(new BasicNameValuePair(PostKeys.ORDER_CASE_ID, orderCaseId+""));
        nameValuePairs.add(new BasicNameValuePair(PostKeys.BLE_TAG_MAC_ADDRESS,bleTagMac));
        nameValuePairs.add(new BasicNameValuePair(PostKeys.BAR_CODE,barcode));

        try {
            httpPost.setEntity(new UrlEncodedFormEntity(nameValuePairs));
        } catch (UnsupportedEncodingException e) {
            Log.e(TAG,"Unable to create FormEntity from parameters");
        }

        //Execute, the return value is given through the listener callback
        doRequest(httpPost, listener);
    }

    /**
     * Create a new route for the given BLE Tracker, also links any order cases to the newly created route
     * @param deviceId identifies the BLE Tracker's device ID
     * @param installId identifies the BLE Tracker's installation
     * @param orderIds the order ids of the order cases
     * @param orderCaseIds the case ids of the order cases
     * @param listener the callback interface to which results are delegated
     */
    public void createRoute(String deviceId, String installId,int[] orderIds, int[] orderCaseIds, ServerRequestListener listener) {
        assert orderCaseIds.length == orderIds.length;

        HttpPost httpPost = new HttpPost(Configuration.SERVER_API_URL + EndPoints.ORDER);
        List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(3);

        nameValuePairs.add(new BasicNameValuePair(PostKeys.API,Configuration.API_KEY));
        nameValuePairs.add(new BasicNameValuePair(PostKeys.DEVICE_ID,deviceId));
        nameValuePairs.add(new BasicNameValuePair(PostKeys.INSTALL_ID,installId));

        //The order case ids are given as a JSON array of JSONObject, each JSONObject contains a pair of order and case ids
        JSONArray orderCases = new JSONArray();
        try {
            for(int i = 0 ; i < orderIds.length ; ++i) {
                JSONObject orderCaseJSON = new JSONObject();
                orderCaseJSON.put(PostKeys.ORDER_ID,orderIds[i]);
                orderCaseJSON.put(PostKeys.ORDER_CASE_ID,orderIds[i]);
                orderCases.put(orderCaseJSON);
            }
            nameValuePairs.add(new BasicNameValuePair(PostKeys.ORDER_CASES,orderCases.toString()));
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
        HttpPost httpPost = new HttpPost(Configuration.SERVER_API_URL + EndPoints.TRACKING_DATA);
        List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(3);

        nameValuePairs.add(new BasicNameValuePair(PostKeys.API, Configuration.API_KEY));
        nameValuePairs.add(new BasicNameValuePair(PostKeys.DEVICE_ID, deviceId));
        nameValuePairs.add(new BasicNameValuePair(PostKeys.INSTALL_ID, installId));

        try {
            JSONArray rssiJsonArray = new JSONArray();
            for (RSSIMeasurement rssiMeasurement : rssiMeasurements) {
                JSONObject rssiJson = new JSONObject();

                rssiJson.put(PostKeys.MAC_ADDRESS, rssiMeasurement.getMacAddress());
                rssiJson.put(PostKeys.RSSI, rssiMeasurement.rssi);
                rssiJson.put(PostKeys.TIMESTAMP, rssiMeasurement.timestamp);

                rssiJsonArray.put(rssiJson);
            }

            JSONArray gpsJsonArray = new JSONArray();
            for (GPSMeasurement gpsMeasurement : gpsMeasurements) {
                JSONObject gpsJson = new JSONObject();

                gpsJson.put(PostKeys.LATITUDE, gpsMeasurement.latitude);
                gpsJson.put(PostKeys.LONGITUDE, gpsMeasurement.longitude);
                gpsJson.put(PostKeys.TIMESTAMP, gpsMeasurement.timestamp);

                gpsJsonArray.put(gpsJson);
            }

            //TODO: Fill sensoric array here
            JSONArray sensoricArray = new JSONArray();

            nameValuePairs.add(new BasicNameValuePair(PostKeys.RSSI_DATA,rssiJsonArray.toString()));
            nameValuePairs.add(new BasicNameValuePair(PostKeys.GPS_DATA,gpsJsonArray.toString()));
            nameValuePairs.add(new BasicNameValuePair(PostKeys.SENSOR_DATA,sensoricArray.toString()));

        } catch (JSONException e) {
            e.printStackTrace();
        }

        //Execute, the return value is given through the listener callback
        doRequest(httpPost, listener);
    }

    public void finishOrderCase(int orderCaseId, int orderId, Date finishTime, ServerRequestListener listener) {
        HttpPost httpPost = new HttpPost(Configuration.SERVER_API_URL + EndPoints.ORDER_CASE + "/" + END_VERB);
        List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(3);

        nameValuePairs.add(new BasicNameValuePair(PostKeys.API, Configuration.API_KEY));
        nameValuePairs.add(new BasicNameValuePair(PostKeys.ORDER_ID, orderId + ""));
        nameValuePairs.add(new BasicNameValuePair(PostKeys.ORDER_CASE_ID, orderCaseId + ""));

        //Execute, the return value is given through the listener callback
        doRequest(httpPost, listener);
    }

    public void startRoute(int routeId, Date startTime, ServerRequestListener listener) {
        HttpPost httpPost = new HttpPost(Configuration.SERVER_API_URL + EndPoints.ROUTE + "/" + START_VERB);
        List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(3);

        nameValuePairs.add(new BasicNameValuePair(PostKeys.API, Configuration.API_KEY));
        nameValuePairs.add(new BasicNameValuePair(PostKeys.ROUTE_ID,routeId+""));
        nameValuePairs.add(new BasicNameValuePair(PostKeys.START_TIME, ServerDateTimeFormat.format(startTime)));

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

    private class ServerRequestTask extends AsyncTask<HttpUriRequest, Void, JSONObject> {

        private Exception exception;
        private ServerRequestListener listener;

        public ServerRequestTask(ServerRequestListener listener) {
            this.listener = listener;
        }

        protected JSONObject doInBackground(HttpUriRequest... requests) {
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
