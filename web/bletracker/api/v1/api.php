<?

require_once('../../MySQLi/MysqliDb.php');

/** SQL CLASSES **/
class Customer {
    public $id;
    public $location;
    public $name;

    public function __construct($id,$location,$name) {
        $this->id = $id;
        $this->location = $location;
        $this->name = $name;
    }
}

class Order {
    public $id;
    public $customer;
    public $created;

    public function __construct($id,$customer,$created) {
        $this->id = $id;
        $this->customer = $customer;
        $this->created = $created;
    }
}

class Order_Case {
    public $id;
    public $order;
    public $bleTag;
    public $route;
    public $barCode;
    public $status;
    public $start;
    public $end;

    public function __construct($id,$order,$bleTag,$route,$status,$start,$end) {
        $this->id = $id;
        $this->order = $order;
        $this->bleTag = $bleTag;
        $this->route = $route;
        $this->status = $status;
        $this->start = $start;
        $this->end = $end;
    }
}

class Location {
    public $id;
    public $name;
    public $latitude;
    public $longitude;
    public $street;
    public $street_number;
    public $city;
    public $zip_code;

    public function __construct($id,$name,$latitude,$longitude,$street,$street_number,$city,$zip_code) {
        $this->id = $id;
        $this->name = $name;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->street = $street;
        $this->street_number = $street_number;
        $this->city = $city;
        $this->zip_code = $zip_code;
    }
}

class BLE_Tag {
    public $id;
    public $company;
    public $mac;

    public function __construct($id) {
        $this->id = $id;
    }
}

class Route {
    public $id;

    public function __construct($id) {
        $this->id = $id;
    }
}

abstract class API
{
    /**
     * Property: method
     * The HTTP method this request was made in, either GET, POST, PUT or DELETE
     */
    protected $method = '';
    /**
     * Property: endpoint
     * The Model requested in the URI. eg: /files
     */
    protected $endpoint = '';
    /**
     * Property: verb
     * An optional additional descriptor about the endpoint, used for things that can
     * not be handled by the basic methods. eg: /files/process
     */
    protected $verb = '';
    /**
     * Property: args
     * Any additional URI components after the endpoint and verb have been removed, in our
     * case, an integer ID for the resource. eg: /<endpoint>/<verb>/<arg0>/<arg1>
     * or /<endpoint>/<arg0>
     */
    protected $args = array();
    /**
     * Property: file
     * Stores the input of the PUT request
     */
     protected $file = null;


     // protected $request;

    /**
     * Constructor: __construct
     * Allow for CORS, assemble and pre-process the data
     */
    public function __construct($request) {
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        $this->args = explode('/', rtrim($request, '/'));
        $this->endpoint = array_shift($this->args);
        if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
            $this->verb = array_shift($this->args);
        }

        // echo $_SERVER['HTTP_X_HTTP_METHOD'];

        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }

        // print_r("API:<br/>");
        // print_r($this);

        switch($this->method) {
            case 'DELETE':
            case 'POST':
                $this->request = $this->_cleanInputs($_POST);
                break;
            case 'GET':
                $this->request = $this->_cleanInputs($_GET);
                break;
            case 'PUT':
                $this->request = $this->_cleanInputs($_GET);
                $this->file = file_get_contents("php://input");
                break;
            case 'PATCH':
                // print_r(file_get_contents("php://input"));
                $input = file_get_contents("php://input");
                // $this->request = $this->_cleanInputs($input);
                $this->request = $this->_paramArrayFromRaw($input);
                // print_r($input);
                print_r($this->request);
                // echo $this->request;
            default:
                $this->_response('Invalid Method', 405);
                break;
        }
    }

    public function processAPI() {
        if (method_exists($this, $this->endpoint)) {
            return $this->_response($this->{$this->endpoint}($this->args));
        }
        return $this->_response('{"error":"No Endpoint: $this->endpoint"', 404);
    }

    private function _paramArrayFromRaw($rawString) {
        $pairs = explode('&', $rawString);
        $paramArray = array();
        foreach($pairs as $pair) {
            $keyValuePair = explode('=', $pair);
            $paramArray[$keyValuePair[0]] = $keyValuePair[1];
        }

        return $paramArray;
    }

    private function _response($data, $status = 200) {
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
        return json_encode($data);
    }

    private function _cleanInputs($data) {
        $clean_input = array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->_cleanInputs($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

    private function _requestStatus($code) {
        $status = array(  
            200 => 'OK',
            404 => 'Not Found',   
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        ); 
        return ($status[$code])?$status[$code]:$status[500]; 
    }
}

/**
  *     The database wrapper, does the required database operations on the underlying MySQL database
  */
class Database {

    private $server = "localhost:3306";
    private $user = "whereAt";
    private $pass = "whereAt2014";
    private $dbName = "ble_tracker";

    private $db;

    public function __construct() {
        $this->db = new MysqliDb ($this->server,$this->user,$this->pass,$this->dbName);
    }

    /**
     *      Throws the last query and error as an exception
     */
    private function sql_error() {
        throw new Exception("Failed to execute SQL Query " . $this->db->getLastQuery() . " ERROR : " . $this->db->getLastError());
    }

    //Does a company exist with this API_Key and is its subscription still active
    public function select_company($apiKey) {
        // $sql = 'SELECT * FROM Subscription,Company WHERE API_Key = ? AND Company.Subscription_ID = Subscription.ID AND Subscription.State = \'ACTIVE\' AND Expiry_Date > NOW()';
        // $sql = 'SELECT companies.ID AS ID FROM subscriptions,companies WHERE API_Key = ? AND subscriptions.State = \'ACTIVE\'';
        $sql = 'SELECT companies.ID AS ID FROM subscriptions,companies WHERE API_Key = ?';
        $result = $this->db->rawQuery($sql,array($apiKey));
        // echo $this->db->getLastQuery();

        if(isset($result) && count($result) > 0) {
            return $result[0]['ID'];
        }
        else return -1;
    }

    //Select the complete data for a given order 
    public function select_order($orderId) {

        $sql = "SELECT * FROM orders,customers,locations WHERE orders.ID = ? AND orders.Customer_ID = customers.ID AND locations.ID = customers.Location_ID";

        $result = $this->db->rawQuery($sql,array($orderId));
        // echo $this->db->getLastQuery();
        // print_r($result);
        return $result[0];
    }

    //Select the complete data for a given order_case 
    public function select_order_case($orderId,$orderCaseId) {

        $sql = "SELECT *, locations.Name AS Location_Name, order_cases.ID AS Order_Case_ID FROM order_cases,orders,customers,locations WHERE orders.ID = ? AND order_cases.ID = ? AND orders.Customer_ID = customers.ID AND locations.ID = customers.Location_ID";

        $result = $this->db->rawQuery($sql,array($orderId,$orderCaseId));
        // echo $this->db->getLastQuery();
        // print_r($result);
        return $result[0];
    }

    /**
     *   Inserts a new BLE_Tracker with the given device and install IDs. The IDs are composite uniques preventing duplicates
     */
    public function insert_ble_tracker($deviceId,$installId) {
        $data = array(  
            'Device_ID' => $deviceId,
            'Install_ID' => $installId
        );
        $id = $this->db->insert('ble_trackers',$data);
        if(!$id) {
            $this->sql_error();
        }
        else return $id;
    }

    /**
     *  Insert a new Order when a valid Customer.ID is given and the Order does not exist yet, returns the ID of the new Order when succesful
     */
    public function insert_order($orderId,$customerId) {
        $data = array(  
            'ID' => $orderId,
            'Customer_ID' => $customerId
        );  
        $id = $this->db->insert('orders',$data);
        if(!$id) {
            $this->sql_error();
        }
        else return $id;
    }

    /**
     *  Insert a new order case
     */
    public function insert_order_case($caseId,$orderId,$bleMacAddress,$barCode) {

        $sql = 'INSERT INTO order_cases (ID,Order_ID,BLE_Tag_ID,Bar_Code) SELECT ?,?,ble_tags.ID,? FROM ble_tags WHERE Mac_Address = ?';
        $this->db->rawQuery($sql,array($caseId,$orderId,$barCode, $bleMacAddress));

        //The return does not work for order cases probably because of the composite key
        $order = $this->select_order_case($orderId,$caseId);
        // print_r($order);
        if(!$order) {
            $this->sql_error();
        }
        else return $order;
    }

    /**
      * Insert a new route, using the BLE_Tracker identified by the device and install ID
      */
    public function insert_route($deviceId,$installId) {
        // $data = array("BLE_Tracker_ID" => $bleTrackerId);
        // $sql = 'INSERT INTO routes (BLE_Tracker_ID) SELECT ID FROM ble_trackers WHERE Device_ID = ? AND Install_ID = ?';

        $bleTracker = $this->db->subQuery();
        $bleTracker->where ("Device_ID", $deviceId);
        $bleTracker->where ("Install_ID", $installId);
        $bleTracker->getOne ("ble_trackers", "ID");

        $data = array(
            "BLE_Tracker_ID" => $bleTracker
        );     
               
        // $id = $this->db->rawQuery($sql,array($deviceId,$installId));
        $id = $this->db->insert("routes",$data);
        if($id) {

            //Somehow the value is not being returned, get it manually
            $data = array("Device_ID" => $deviceId,"Install_ID" => $installId);
            $id = $this->db->rawQuery('SELECT routes.ID from routes,ble_trackers where Device_ID = ? AND Install_ID = ? AND ble_trackers.ID = routes.BLE_Tracker_ID',$data);
            // print_r($id);

            if($id !== null) {
                return $id[0]['ID'];
            }
            else {
                $this->sql_error();
            }
        }
        else $this->sql_error();
    }

    /**
      *     Update order cases matching the order and order case ids so they link to the route of the given route id
      */
    public function update_order_case($orderId,$orderCaseId,$routeId) {
        // 'UPDATE items,month SET items.price=month.price WHERE items.id=month.id';

        $data = Array (
            'Route_ID' => $routeId,
        );
        $this->db->where('Order_ID', $orderId);
        $this->db->where('ID',$orderCaseId);
        if ($this->db->update ('order_cases', $data)) {
            // echo $this->db->count . ' records were updated';
            // echo $this->db->getLastQuery();

            return $this->db->count;
        }
    }

    /**
      *     Insert a new GPS (latitude,longitude) row for a given route, the time indicating the time the device read it
      */
    public function insert_gps($routeId,$time,$latitude,$longitude) {
        // echo $time;

        $dt = new DateTime("@$time");
        $time = $dt->format('Y-m-d H:i:s');

        $data = array(
            "Route_ID" => $routeId,
            "Time_Read" => $time,
            "Latitude" => $latitude,
            "Longitude" => $longitude
        );
               
        return $this->db->insert ('gps_data', $data);
    }

    /** 
      *     Insert a new RSSI reading row for a given route, read at the specified time
      */
    public function insert_rssi($routeId,$bleTagMacAddress,$timestamp,$rssi) {
        // $data = array(
        //     "Route_ID" => $routeId,
        //     "BLE_Tag_ID" => $bleTagId,
        //     "Time_Read" => $time,
        //     "RSSI" => $rssi
        // );
        $sql = "INSERT INTO rssi_data (Route_ID,BLE_Tag_ID,Time_Read,RSSI) SELECT ?,ID,?,? FROM ble_tags WHERE Mac_Address = ?";

        $format = 'Y-m-d H:i:s';
        $datetime = date($format,$timestamp);

        $result = $this->db->rawQuery($sql,array($routeId,$datetime,$rssi,$bleTagMacAddress));

        // echo $this->db->getLastQuery();
        // print_r($result);

        return $result;
    }

    /**
      *  Insert new sensor data (time and value) for a route with the given route ID returns the id of the new row
      */
    public function insert_sensoric($routeId,$time,$sensoric) {
        $data = array(
            "Route_ID" => $routeId,
            "Sensor_1" => $time
        );
        return $this->db->insert ('sensor_data', $data);
    }

    /**
     *   Updates the end time to the value of end of the order case identified by the composite key matching the order and order case id
     */
    public function update_order_case_with_end_time($orderCaseId,$orderId,$end) {
        $format = 'Y-m-d H:i:s';
        $datetime = date($format,$end);

        $data = array(
            "End" => $datetime
        );

        $this->db->where('Order_ID', $orderId);
        $this->db->where('ID',$orderCaseId);
        return $this->db->update('order_cases', $data);
    }

    /**
     *  Update a route with route id to reflect the new start time
     */
    public function update_route_with_start_time($routeId, $startTime) {
        $data = array(
            "Start" => $startTime
        );
        $this->db->where('ID', $routeId);
        $result = $this->db->update('routes', $data);

        return $result;
    }

    /**
      * Finish route by updating the end time for the route with the given routeId
      */
    public function update_route_with_end_time($routeId, $endTime) {
        $data = array(
            "End" => $endTime
        );
        $this->db->where('ID', $routeId);
        $result = $this->db->update('routes', $data);

        return $result;
    }

    /*
     *
     */
    // public function 
}

class MyAPI extends API
{
    protected $database;

    private $apiKey;
    private $companyId;

    public function __construct($request, $origin) {
        parent::__construct($request);

        $this->database = new Database();

        // print_r($this->request);

        if (!array_key_exists('apiKey', $this->request)) {
            throw new Exception('No API Key provided');
        }
        
        $companyId = $this->authenticate($this->request['apiKey']);
        if($companyId != -1) {
            $this->companyId = $companyId;
        }
        else throw new Exception('Invalid API Key');
    }

    /**
      * Helper function that determines if the request object contains the keys given in properties. Properties may either be an array of strings or a singular string
      */
    private function requestHasProperties($properties) {
        if(!is_array($properties)) {
            $properties = array($properties);
        }
        
        foreach($properties as $property) {
            // echo $property;
            if(!array_key_exists($property, $this->request)) {
                return false;
            }
        }
        
        return true;
    }

    /**
      *  Return the id of the company that has the given the api key, will throw an exception upon failure
      */ 
    private function authenticate($apiKey) {
        return $this->database->select_company($apiKey);
    }

    /**
     * The device endpoint is able to register new devices (as BLE trackers)
     */    
    protected function ble_tracker() {
        //Add new device
        if($this->method == 'POST') {
            // print_r($this->request);
            if(array_key_exists('deviceId', $this->request) && array_key_exists('installId', $this->request)) {
               $id = $this->database->insert_ble_tracker($this->request['deviceId'],$this->request['installId']);

               return array(  
                    'ID' => $id
                );
            }
            else throw new Exception("Missing parameters");
        }
        else {
            throw new Exception("Method $this->method is unsupported");
        }
    }

    /**
     * The order endpoint allows to GET a order by its ID or add a order by POST by giving a orderId and customerId
     */   
    protected function order() {
        if($this->method == 'POST') {
            if(array_key_exists('orderId', $this->request) && array_key_exists('customerId', $this->request)) {
                $id = $this->database->insert_order($this->request['orderId'],$this->request['customerId']);

                return array(  
                    'ID' => $id
                );
            }
            else throw new Exception("Missing parameters");
        }
        elseif($this->method == 'GET') {
            if(array_key_exists('orderId', $this->request)) {
                $result = $this->database->select_order($this->request['orderId']);
                return $result;
            }
            else throw new Exception("Missing parameters");
        }
        else throw new Exception("Method $this->method is unsupported for end-point " . __FUNCTION__);
    }

    /**
     * The order_case endpoint is able to insert new order cases and linking them with BLE_Tags
     */    
    protected function order_case() {
        if($this->method == 'POST') { 
            if($this->verb == 'update') {
                if($this->requestHasProperties(array('orderCaseId','orderId','endTime'))) {
                    $affected = $this->database->update_order_case_with_end_time($this->request['orderCaseId'],$this->request['orderId'],$this->request['endTime']);
                    return array(  
                        'affected' => (int)$affected
                    );
                }
            }
            elseif($this->requestHasProperties(array('orderCaseId','orderId','bleTagMacAddress','barCode'))) {
               $sqlOrder = $this->database->insert_order_case($this->request['orderCaseId'],$this->request['orderId'],$this->request['bleTagMacAddress'],$this->request['barCode']);

                //TODO: This seems extremely archaic, and should be improved
                $orderCase = new Order_Case($sqlOrder['Order_Case_ID'],
                    new Order($sqlOrder['Order_ID'],
                        new Customer(
                            $sqlOrder['Customer_ID'],
                            new Location(
                                $sqlOrder['Location_ID'],
                                $sqlOrder['Location_Name'],
                                $sqlOrder['Latitude'],
                                $sqlOrder['Longitude'],
                                $sqlOrder['Street'],
                                $sqlOrder['Street_Number'],
                                $sqlOrder['City'],
                                $sqlOrder['Zip_Code']
                            ),
                            $sqlOrder['Name']
                        ),
                        $sqlOrder['Created']
                    ),
                    new BLE_Tag($sqlOrder['BLE_Tag_ID']),
                    new Route($sqlOrder['Route_ID']),
                    $sqlOrder['Status'],
                    $sqlOrder['Start'],
                    $sqlOrder['End']
                );

               //Correctly format it
               // $order->customer = 

                //Wrap it in a object
               // $return = new stdObject();
               // $return->order

               return $orderCase;
            }
            else throw new Exception("Missing parameters");
        }
        else if($this->method == 'PATCH') {
            if($this->requestHasProperties(array('orderCaseId','orderId','end'))) {
                $this->database->update_order_case_with_end_time($this->request['orderCaseId'],$this->request['orderId'],$this->request['end']);
                return array(  
                    'ID' => $id
                );
            }
        }
        else throw new Exception("Method $this->method is unsupported for end-point " . __FUNCTION__);
    }

    /**
     * Example of an Endpoint
     */
     protected function company() {
        if ($this->method == 'GET') {
            return "Your company ID = " . $this->companyId;
        } 
        else throw new Exception("Method $this->method is unsupported for end-point " . __FUNCTION__);
     }

     /**
       *   Create or retrieve a new route, in case of creating also update the order cases that match the given ids (accepted as JSON array)
       */
     protected function route() {
        if($this->method == 'POST') {
            if($this->verb == 'start') {
                if($this->requestHasProperties(array('routeId','startTime'))) {
                    $affected = $this->database->update_route_with_start_time($this->request['routeId'],$this->request['startTime']);
                    return array("affected" => (int)$affected);
                    // return array("id" => $id);
                }
                else throw new Exception("Missing parameters");
            }
            if($this->verb == 'finish') {
                if($this->requestHasProperties(array('routeId','endTime'))) {
                    $affected = $this->database->update_route_with_end_time($this->request['routeId'],$this->request['endTime']);
                    return array("affected" => (int)$affected);
                    // return array("id" => $id);
                }
                else throw new Exception("Missing parameters");
            }
            //No verb, simply insert new 
            else if($this->requestHasProperties(array('deviceId','installId','orderCases'))) {
                $orderCases = json_decode($this->request['orderCases']);
                $routeId = $this->database->insert_route($this->request['deviceId'],$this->request['installId']);

                if($routeId) {
                    //Update the order cases so they link to the newly created id
                    foreach($orderCases as $orderCase) {
                        $this->database->update_order_case($orderCase->orderId,$orderCase->orderCaseId,$routeId);
                    }

                    //Return the id of the route that was just created
                    return array(  
                        'ID' => $routeId
                    );
                }
            }
            else throw new Exception("Missing parameters");
        }
        elseif($this->method == 'GET') {

        }
        elseif($this->method == 'PATCH') { //Update the finish time of the route
            if($this->requestHasProperties(array('routeId','end'))) {

            }
        }
        else throw new Exception("Method $this->method is unsupported for end-point " . __FUNCTION__);
     }

     /**
       *    Create or retrieve tracking data (GPS, RSSI and possible sensor data).
       *    In case of a POST method, GPS, RSSI and Sensor data should be submitted as separate arrays
       */
     protected function tracking_data() {
        if($this->method == 'POST') {
            if($this->requestHasProperties(array('routeId','gpsData','rssiData','sensorData'))) {
                $gpsData = json_decode($this->request['gpsData']);
                $rssiData = json_decode($this->request['rssiData']);
                $sensorData = json_decode($this->request['sensorData']);
                $routeId = $this->request['routeId'];

                $gpsAdded = 0;
                $rssiAdded = 0;
                $sensorAdded = 0;

                foreach($gpsData as $gpsRecord) {
                    // print_r($gpsRecord);
                    $gpsAdded = $this->database->insert_gps($routeId,$gpsRecord->time,$gpsRecord->lat,$gpsRecord->long);
                }
                foreach($rssiData as $rssiRecord) {
                    $rssiAdded = $this->database->insert_rssi($routeId,$rssiRecord->mac,$rssiRecord->time,$rssiRecord->rssi);
                }
                // foreach($sensorData as $sensorRecord) {
                //     $sensorAdded = $this->database->insert_sensoric($routeId,$sensorRecord$sensorRecord->time,$sensorRecord->reading);
                // }

                return json_encode(array("gpsAdded" => $gpsAdded,"rssiAdded" => $rssiAdded, "sensorAdded" => $sensorAdded));
            }
        }
        else throw new Exception("Method $this->method is unsupported for end-point " . __FUNCTION__);
     }
 }

// Requests from the same server don't have a HTTP_ORIGIN header
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

// print_r($_POST);
// print_r($_GET);

try {
    $API = new MyAPI($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
    echo $API->processAPI();
} catch (Exception $e) {
    echo json_encode(Array('error' => $e->getMessage()));
}

?>
