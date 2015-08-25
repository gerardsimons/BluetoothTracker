package com.simons.bletracker;

/**
 * Created by gerard on 31/07/15.
 *
 *  A static class containing all configuration parameters
 */
public class Configuration {
    public static int[] BARCODE_COMPONENT_LENGTHS = {4,6,10,2};
    public static String SERVER_API_URL = "http://whereatcloud.com/bletracker/api/v1/";
    public static String API_KEY = "]wv2Np:c@e8V9@>r37g){18u.32lY";

    public static int TRACKER_CACHE_SIZE = 1; //Size of tracker data before flushing it to the server
    public static int DEPART_DISTANCE = 50; //The distance in meters which is considered to trigger a departure
    public static int ARRIVE_DISTANCE = 50; //Idem but for arrivals (both the tracker on return and case orders)
    public static int BLE_TAG_LOST_DISTANCE = 100; //Distance in meters when a ble tag is considered 'lost'
}
