package com.simons.bletracker;

import android.util.Log;

import org.apache.http.Header;
import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.ClientProtocolException;
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
import java.util.zip.GZIPInputStream;

/**
 * Created by Gerard on 18-7-2015.
 *
 * This class acts as the single interface between the app and the remote server
 */
public class ServerAPI {



    private static final HttpClient HttpClient = new DefaultHttpClient();

    private static final String TAG = ServerAPI.class.getSimpleName();
    private static final String SERVER_API_URL = "www.api2.whereatcloud.com";
    private static final String API_KEY = "}168N$lL974o}ng1*k:ebIS154;]Bj";

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

    private static JSONObject GetResponseFromServer(HttpPost postRequest) {

        String errorMsg = "Unable to get a response from " + postRequest.toString();
        try {
            HttpResponse response = HttpClient.execute(postRequest);
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
        } catch (ClientProtocolException e) {
            Log.e(TAG,errorMsg,e);
        } catch (IOException e) {
            Log.e(TAG,errorMsg,e);
        }
        return null;
    }

    /**
     *
     * @param requestPairs
     * @return A HttpPost object containing the
     */
    private static HttpPost CreateJSONPostRequest(String... requestPairs) {
        assert(requestPairs.length % 2 != 0); //String values should come in key-value pairs

        JSONObject jsonObj = new JSONObject();

        try {
            for (int i = 0; i < requestPairs.length; i += 2) {
                jsonObj.put(requestPairs[i], requestPairs[i + i]);
            }

            //API
            jsonObj.put("apikey", API_KEY);

            HttpPost httpPost = new HttpPost(SERVER_API_URL);
            StringEntity entity = new StringEntity(jsonObj.toString(), HTTP.UTF_8);
            entity.setContentType("application/json");
            httpPost.setEntity(entity);

            return httpPost;
        }
        catch(JSONException e) {
            Log.e(TAG,"Unable to create JSONPostRequest from key-value pairs : " + requestPairs,e);
        }
        catch(UnsupportedEncodingException e) {
            Log.e(TAG,"Invalid encoding " + HTTP.UTF_8,e);
        }

        return null;
    }

    public static JSONObject GetJSONFromHttpResponse(HttpResponse httpResponse) {
        if(httpResponse != null) {

            try {
                HttpEntity resultentity = httpResponse.getEntity();
                InputStream inputstream = resultentity.getContent();
                Header contentencoding = httpResponse.getFirstHeader("Content-Encoding");
                if (contentencoding != null && contentencoding.getValue().equalsIgnoreCase("gzip")) {
                    inputstream = new GZIPInputStream(inputstream);
                }
                String resultstring = ConvertStreamToString(inputstream);
                inputstream.close();
                resultstring = resultstring.substring(1, resultstring.length() - 1);

                JSONObject jsonReply = new JSONObject(resultstring);
                return jsonReply;
            }
            catch(IOException e) {
                Log.e(TAG,"Unable to create stream from httpRespionse",e);
            }
            catch(JSONException e) {
                Log.e(TAG,"Unable to create JSONObject from response",e);
            }
        }
        return null;
    }

//    public static Order GetOrdersForUser(User user) {
//        HttpPost postObject = CreateJSONPostRequest("user_id",user.getID()+"");
//
//        try {
//            JSONObject responseJSON = GetResponseFromServer(postObject);
//            String succesString = (String)responseJSON.get("succes");
//            if(Boolean.parseBoolean(succesString)) {
//
//            }
//        }
//        catch(JSONException e) {
//            Log.e(TAG,"Unable to retrieve JSON values",e);
//        }
//
//    }
}
