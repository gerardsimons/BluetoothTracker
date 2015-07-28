package com.simons.bletracker.remote;

import android.os.AsyncTask;
import android.util.Log;

import com.simons.bletracker.models.BLETag;
import com.simons.bletracker.models.OrderCase;
import com.simons.bletracker.models.Route;

import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.protocol.HTTP;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.UnsupportedEncodingException;
import java.util.concurrent.ExecutionException;
import java.util.concurrent.TimeUnit;
import java.util.concurrent.TimeoutException;

/**
 * Created by Gerard on 18-7-2015.
 *
 * This class acts as the single interface between the app and the remote server
 */
public class ServerAPI {

//    public static String SERVER_API_URL = "www.api2.whereatcloud.com";

    public static String SERVER_API_URL = "http://192.168.1.123/whereat/index.php";
    public static String API_KEY = "]wv2Np:c@e8V9@>r37g)?{18u.32lY";

    private static final String API_KEY_KEY = "apiKey";
    private static final String REQUEST_KEY = "request";
    private static final String REQUEST_NAME_KEY = "name";
    private static final String DEVICE_ID_KEY = "deviceId";
    private static final String INSTALL_ID_KEY = "installId";

    private static final String REGISTER_CONTROLLER_REQUEST_NAME = "register_ble_controller";

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

    private interface RequestCallback {
//        public void onRequestFailed(JSONException exception);
        public void onRequestDenied(JSONObject respsonse);
        public void onRequestSuccesfull(JSONObject response);
    }

    private static String ConvertStreamToString(InputStream inputStream) {
        String line = "";
        StringBuilder total = new StringBuilder();
        BufferedReader rd = new BufferedReader(new InputStreamReader(inputStream));
        try {
            while ((line = rd.readLine()) != null) {
                total.append(line);
            }
        }
        catch(IOException ioE) {
            Log.e(TAG,"Unable to convert response inputstream to String",ioE);
        }
        return total.toString();
    }

    private static JSONObject CreateAuthenticationJSONObject() {
        JSONObject jsonObject = new JSONObject();
        try {
            jsonObject.put(API_KEY_KEY, API_KEY);
        } catch (JSONException e) {
            Log.e(TAG,"Unable to create authentication JSON object",e);
        }
        return jsonObject;
    }

    private JSONObject getResponseFromServer(HttpPost postRequest) {
        final long timeout = 30000;
        ServerRequestTask task = new ServerRequestTask();
        task.execute(postRequest);
        try {
            return task.get(timeout, TimeUnit.MILLISECONDS);
        } catch (InterruptedException e) {
            Log.e(TAG,"Task was interrupted",e);
        } catch (ExecutionException e) {
            Log.e(TAG,"Task failed to execute",e);
        } catch (TimeoutException e) {
            Log.e(TAG, "Server request task timed out after " + timeout + " ms" , e);
        }
        return null;
    }

    private static HttpPost CreateHttpPost(JSONObject jsonObject) {
        if(jsonObject != null) {
            HttpPost httpPost = new HttpPost(SERVER_API_URL);
            StringEntity entity = null;
            try {
                entity = new StringEntity(jsonObject.toString(), HTTP.UTF_8);
            } catch (UnsupportedEncodingException e) {
                Log.e(TAG, "Bad encoding", e);
            }
            entity.setContentType("application/json");
            httpPost.setEntity(entity);
            return httpPost;
        }
        return null;
    }

    private static JSONObject CreateJSONRequest(JSONObject requestObject) {
        assert requestObject != null;

        try {
            JSONObject jsonObj = CreateAuthenticationJSONObject();
            jsonObj.put("request",requestObject);
            return jsonObj;
        }
        catch (JSONException e) {
            Log.e(TAG,"Unablet to create JSON request from request object " + requestObject,e);
        }
        return null;
    }


    private JSONObject doRequest(JSONObject request) {

        HttpPost postObject = CreateHttpPost(request);

        if(postObject != null) {
            Log.d(TAG, "Issuing new server request : \n" + request.toString());
            long start = System.currentTimeMillis();

            JSONObject responseJSON = getResponseFromServer(postObject);
            long end = System.currentTimeMillis();
            long elapsed = end - start;

            if(responseJSON != null) {
                Log.d(TAG, "Response from server received after " + elapsed + " ms : \n" + responseJSON.toString());
            }
            else {
                Log.d(TAG, "Still no response from server received after " + elapsed + " ms");
            }

            return responseJSON;
        }
        else {
            Log.e(TAG, "postObject is null.");
            return null;
        }
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
    public boolean registerBLEController(String deviceId, String installId) {
        try {
            JSONObject requestObject = new JSONObject();
            requestObject.put(REQUEST_NAME_KEY,REGISTER_CONTROLLER_REQUEST_NAME);
            requestObject.put(DEVICE_ID_KEY,deviceId);
            requestObject.put(INSTALL_ID_KEY, installId);

            JSONObject json  = CreateJSONRequest(requestObject);
            JSONObject response = doRequest(json);

            return RequestWasSuccesful(response);


        } catch (JSONException e) {
            e.printStackTrace();
        }

        return false;
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

    private class ServerRequestTask extends AsyncTask<HttpPost, Void, JSONObject> {

        private Exception exception;

        protected JSONObject doInBackground(HttpPost... requests) {
            if(requests.length > 1) {
                Log.e(TAG,"Only 1 request at a time supported.");
                return null;
            }
            try {
                HttpResponse response = httpClient.execute(requests[0]);
                if(response != null) {
                    InputStream inputstream = response.getEntity().getContent();
                    String stringResponse = ConvertStreamToString(inputstream);
                    try {
                        return new JSONObject(stringResponse);
                    }
                    catch (JSONException e) {
                        Log.e(TAG,String.format("Unable to convert server response '%s' to a JSONObject",stringResponse),e);
                    }
                } else {
                    return null;
                }
            } catch (Exception e) {
                this.exception = e;
            }
            return null;
        }

        protected void onPostExecute(JSONObject result) {
            if(exception != null) {
                Log.e(TAG,"ServerRequestTask failed",exception);
            }
            // TODO: check this.exception
            // TODO: do something with the feed
        }
    }
}
