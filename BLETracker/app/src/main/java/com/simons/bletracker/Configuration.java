package com.simons.bletracker;

/**
 * Created by gerard on 31/07/15.
 *
 *  A static class containing all configuration parameters
 */
public class Configuration {
    public static final String[] BLE_TAG_MAC_ADDRESSES = {  "ED:77:96:59:D1:F1", //whereAt T
                                                            "C5:E5:14:59:A0:A7",
                                                            "C1:DC:2C:5E:6F:6D",
                                                            "EB:93:83:C2:EB:D4",
                                                            "C1:BA:85:58:D5:71",
                                                            "ED:38:93:84:72:42",
                                                            "FD:6B:36:81:AF:9E",
                                                            "F2:8A:77:1D:93:AA",
                                                            "EA:56:32:30:4A:1A",
                                                            "F6:06:37:99:29:D1",
                                                            "FC:91:57:FA:03:65",
                                                            "F0:1A:97:79:F5:1E"};

    public static int[] BARCODE_COMPONENT_LENGTHS = {4,6,10,2};

//    public static String SERVER_API_URL = "http://192.168.1.15/bletracker/api/v1/";
    public static String SERVER_API_URL = "http://whereatcloud.com/bletracker/api/v1/";
    public static String API_KEY = "]wv2Np:c@e8V9@>r37g){18u.32lY";

    /** GPS STUFF **/
    public static int GPS_UPDATE_INTERVAL = 100;
    public static int GPS_FASTEST_INTERVAL = 50;
    public static int GPS_DISPLACEMENT = 1;

//    public static int TRACKER_CACHE_SIZE = 25; //Size of tracker data before flushing it to the server
    public static int TRACKER_CACHE_SIZE = 1; //Size of tracker data before flushing it to the server
    public static int DEPART_DISTANCE = 30; //The distance in meters which is considered to trigger a departure
    public static int ARRIVE_DISTANCE = 80; //Idem but for arrivals (both the tracker on return and case orders)
    public static int RSSI_DISCOVERY_INTERVAL = 250; //Sleep time in ms to wait until discovery is restarted after succesfully finding a licensed tag
    public static int BLE_TAG_LOST_TIMEOUT = 300; //Time in seconds when a ble tag is considered 'lost'

    private static String intParamToString(String name, int param) {
        return name + " : " + param + "\n";
    }

    private static String intArrayParamToString(String name, int[] param) {
        String string = name + " : [";

        for(int i : param) {
            string += "" + i + ",";
        }
        string = string.substring(0,string.length()-1) + "]\n";

        return string;
    }

    public static String ToString() {
        String toString = "Configuration:\n";

        toString += intArrayParamToString("Bar_Code_Component_Length",BARCODE_COMPONENT_LENGTHS);
        toString += "Server_Api_URL : " + SERVER_API_URL + "\n";
        toString += intParamToString("GPS_Update_Interval",GPS_UPDATE_INTERVAL);
        toString += intParamToString("GPS_Fastest_Interval",GPS_FASTEST_INTERVAL);
        toString += intParamToString("GPS_Displacement",GPS_DISPLACEMENT);
        toString += intParamToString("Tracker_Cache_Size",TRACKER_CACHE_SIZE);
        toString += intParamToString("Depart_Distance_Trigger",DEPART_DISTANCE);
        toString += intParamToString("Arrive_Distance_Trigger",ARRIVE_DISTANCE);
        toString += intParamToString("RSSI_Discovery_Interval",RSSI_DISCOVERY_INTERVAL);

        return toString;
    }
}
