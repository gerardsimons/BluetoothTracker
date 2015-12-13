<?

require_once('../../MySQLi/MysqliDb.php');
require_once('../../database.php');

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
        header("Content-Type: application/json; charset=utf-8");

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

class MyAPI extends API
{
    protected $database;

    private $apiKey;
    private $company;

    public function __construct($request, $origin) {
        parent::__construct($request);

        $this->database = new Database();

        // print_r($this->request);

        if (!array_key_exists('apiKey', $this->request)) {
            throw new Exception('No API Key provided');
        }
        // echo $this->request['apiKey'];
        $this->company = $this->authenticate($this->request['apiKey']);
        // print_r($this->company);
        if($this->company === NULL) {
            throw new Exception('Invalid API Key');
        }
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
        return $this->database->select_company_for_api_key($apiKey);
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
            if($this->verb == 'finish') {
                if($this->requestHasProperties(array('orderCaseId','orderId','endTime'))) {
                    $affected = $this->database->update_order_case_with_end_time($this->request['orderCaseId'],$this->request['orderId'],$this->request['endTime']);
                    return array(  
                        'affected' => (int)$affected
                    );
                }
                else throw new Exception("Missing parameters");
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
            print_r($this->args);
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
     *  Return orders including order_cases
     *  
     */
    protected function orders() {
        if ($this->method == 'GET') { 

        }
        else throw new Exception("Method $this->method is unsupported for end-point " . __FUNCTION__);
    }

    /** 
     *  Return routes for this company
     */
    protected function routes() {
        if ($this->method == 'GET') { 

            if(!is_null($this->args) && sizeof($this->args) > 0) {

                $routes = array();

                // print_r($this->args);
                // return;
                
                // return array('Routes' => $routes);
                foreach($this->args as $routeId) {
                    // print $routeId;
                    $route = $this->database->select_route($routeId);
                    // print_r($route);
                    $orders = $this->database->select_orders_for_route($routeId);

                    foreach($orders as &$order) {
                        $cases = $this->database->select_order_cases_for_order($order["ID"]);
                        $order["Cases"] = $cases;
                    }

                    $route["Orders"] = $orders;
                    array_push($routes, $route);
                }
                return $routes;
                
            }

            $routes = $this->database->select_routes_for_company($this->company['ID']);
            return array('Routes' => $routes);
        }
        else throw new Exception("Method $this->method is unsupported for end-point " . __FUNCTION__);
    }

    /**
     * Get company details. Deprecated, scope is too broad
     */
     protected function company() {
        if ($this->method == 'GET') {            
            $returnWrapper = array();

            $returnWrapper = $this->company;

            //Get orders
            $returnWrapper["Orders"] = $this->database->select_orders_for_company($this->company['ID']);

            //Get customers
            $returnWrapper["Customers"] = $this->database->select_customers_for_company($this->company['ID']);

            //Get all the routes
            $routes = $this->database->select_routes_for_company($this->company['ID']);

            
            //Get the orders for each route use (&) to pass by reference 
            foreach($routes as &$route) {
                $orderCases = $this->database->select_order_cases_for_route($route['ID']);  
                $route["Order_Cases"] = $orderCases;
            }

            $returnWrapper['Routes'] = $routes;

            //Get all the ble trackers
            $returnWrapper['BLE_Trackers'] = $this->database->select_ble_trackers($this->company['ID']);

            //Get all the ble tags
            $returnWrapper['BLE_Tags'] = $this->database->select_ble_tags($this->company['ID']);

            return $returnWrapper;
        } 
        else throw new Exception("Method $this->method is unsupported for end-point " . __FUNCTION__);
     }

     /**
       *   Create or retrieve a new route, in calculhmac(clent, data)se of creating also update the order cases that match the given ids (accepted as JSON array)
       */
     protected function route() {
        if($this->method == 'POST') {
            if($this->verb == 'depart') {
                if($this->requestHasProperties(array('routeId','departTime','lat','long'))) {
                    $affected = $this->database->update_route_with_departure($this->request['routeId'],$this->request['departTime'],$this->request['lat'],$this->request['long']);
                    return array("affected" => (int)$affected);
                    // return array("id" => $id);
                }
                else throw new Exception("Missing parameters");
            }
            if($this->verb == 'finish') {

                // print_r($this->request);

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
                // print_r($this->request['orderCases']);
                $routeId = $this->database->insert_route($this->request['deviceId'],$this->request['installId']);
                if($routeId) {
                    //Update the order cases so they link to the newly created id
                    foreach($orderCases as $orderCase) {
                        // print_r($orderCase);
                        $this->database->update_order_case($orderCase->orderId,$orderCase->orderCaseId,$routeId);
                    }

                    //Return the id of the route that was just created
                    return array(  
                        'ID' => $routeId
                    );
                }
            }
            else throw new Exception("Unknown verb $this->verb");
        }
        elseif($this->method == 'GET') {
            if($this->requestHasProperties(array('routeId'))) {
                //Select and return all data related to a route
                $route = $this->database->select_route($this->request['routeId']);
                // print_r($route);
                $gpsData = $this->database->select_gps_data($this->request['routeId']);

                //TODO: I was trying to remove database IDs from the GPS records as they seem to superfluous outside of a database context maybe
                //Remove all the unneccessary routeIds
                // foreach($gpsData as $gpsRecord) {
                //     foreach($gpsRecord as $key => $value) {
                //         if($key == 'ID') {
                //             unset($gpsRecord[$key]);
                //             print("<P>BOOM</P>");
                //         }
                //     }
                // }

                $route['gpsData'] = $gpsData;
                $route['rssiData'] = $this->database->select_rssi_data($this->request['routeId']);

                //TODO: add sensor data here

                return $route;
            }
            else throw new Exception("Missing parameters");
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
                    $gpsAdded += $this->database->insert_gps($routeId,$gpsRecord->time,$gpsRecord->lat,$gpsRecord->long);
                }
                foreach($rssiData as $rssiRecord) {
                    $rssiAdded += $this->database->insert_rssi($routeId,$rssiRecord->macAddress,$rssiRecord->time,$rssiRecord->rssi);
                }
                foreach($sensorData as $sensorRecord) {
                    $sensorAdded += $this->database->insert_sensoric($routeId,$sensorRecord->time,$sensorRecord->reading);
                }
                
                return array('gpsAdded' => $gpsAdded,'rssiAdded' => $rssiAdded, 'sensorAdded' => $sensorAdded);
            }
        }
        else throw new Exception("Method $this->method is unsupported for end-point " . __FUNCTION__);
     }

     //Register or authenticate and return a user
     //TODO: This is quite possibly in violation with standard RESTful API, as I send username and password as POST when doing a get request, this should at some point be re-evaluated, it is also not steteless?
     protected function user() {
        if($this->method == 'POST') {
            if($this->verb == 'register') {
                throw new Exception("STUB: Not yet implemented");
            }
            elseif($this->verb == 'login') { 
                if($this->requestHasProperties(array('username','password'))) {
                    $user = $this->database->select_user_for_credentials($this->request['username'],$this->request['password']);
                    // print_r($user);
                    if($user != NULL && count($user) != 0) {
                        return $user[0];
                    }
                    else throw new Exception("Authentication failure!");
                }
                throw new Exception("No username and/or password given");
            }
        }
        elseif($this->method == 'GET') {
            throw new Exception("Unsupported method " . $this->method);
        }
        else throw new Exception("Method $this->method is unsupported for end-point " . __FUNCTION__);
     }

     protected function settings() {
        if($this->method == 'POST') {
            if($this->requestHasProperties(array('username','password','alert'))) {
                $result = $this->database->update_settings($this->request['username'],$this->request['password'],(int)filter_var($this->request['alert'], FILTER_VALIDATE_BOOLEAN));
                return array("succes" => true);
            }
            else throw new Exception("Missing parameters");
        }
        else {
            throw new Exception("Unsupported method " . $this->method);
        }
     }
 }

// Requests from the same server don't have a HTTP_ORIGIN header
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

try {
    $API = new MyAPI($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
    echo $API->processAPI();
} catch (Exception $e) {
    echo json_encode(Array('error' => $e->getMessage()));
}

?>
