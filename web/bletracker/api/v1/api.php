<?

require_once('../../MySQLi/MysqliDb.php');

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
    protected $args = Array();
    /**
     * Property: file
     * Stores the input of the PUT request
     */
     protected $file = Null;


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
            default:
                $this->_response('Invalid Method', 405);
                break;
        }
    }

    public function processAPI() {
        if (method_exists($this, $this->endpoint)) {
            return $this->_response($this->{$this->endpoint}($this->args));
        }
        return $this->_response("No Endpoint: $this->endpoint", 404);
    }

    private function _response($data, $status = 200) {
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
        return json_encode($data);
    }

    private function _cleanInputs($data) {
        $clean_input = Array();
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

    public function sql_error() {

        throw new Exception("Failed to execute SQL Query " . $this->db->getLastQuery() . " ERROR : " . $this->db->getLastError());
    }

    //Does a company exist with this API_Key and is its subscription still active
    public function select_company($apiKey) {
        // $sql = 'SELECT * FROM Subscription,Company WHERE API_Key = ? AND Company.Subscription_ID = Subscription.ID AND Subscription.State = \'ACTIVE\' AND Expiry_Date > NOW()';
        $sql = 'SELECT companies.ID AS ID FROM subscriptions,companies WHERE API_Key = ?';
        $result = $this->db->rawQuery($sql,array($apiKey));

        if(isset($result) && count($result) > 0) {
            return $result[0]['ID'];
        }
        else return -1;
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
        $id = $this->db->rawQuery($sql,array($caseId,$orderId,$barCode, $bleMacAddress));
        if(!$id) {
            $this->sql_error();
        }
        else return $id;
    }

    /**
      * Insert a new route, using the BLE_Tracker identified by the device and install ID
      */
    public function insert_route($deviceId,$installId) {

        $sql = 'INSERT INTO routes (BLE_Tracker_ID) SELECT ble_trackers.ID FROM ble_trackers WHERE Device_ID = ? AND Install_ID = ?';
        $id = $this->db->rawQuery($sql,array($deviceId,$installId));
        if(!$id) {
            $this->sql_error();
        }
        else {
            return $id;
        }
    }

    /**
      *     Update order cases matching the order and order case ids so they link to the route of the given route id
      */
    public function update_order_cases($orderId,$orderCaseId,$routeId) {
        // 'UPDATE items,month SET items.price=month.price WHERE items.id=month.id';

        $data = Array (
            'Route_ID' => $routeId,
        );
        $db->where ('Order_ID', 1);
        $db->where ('ID',$orderCaseId);
        if ($db->update ('orders', $data)) {
            echo $db->count . ' records were updated';
        }
    }

    /**
      * Finish route by setting the end time
      */
    public function update_route($routeId, $endTime) {

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

        // print_r("request = " .  $request);  
        // print_r($this->request['apiKey']);

        // Abstracted out for example
        // $APIKey = new Models\APIKey();
        // $User = new Models\User();

        if (!array_key_exists('apiKey', $this->request)) {
            throw new Exception('No API Key provided');
        }
        $companyId = $this->authenticate($this->request['apiKey']);
        if($companyId != -1) {
            $this->companyId = $companyId;
        }
        else throw new Exception('Invalid API Key');


        // } else if (!$APIKey->verifyKey($this->request['apiKey'], $origin)) {
        //     throw new Exception('Invalid API Key');
        // } else if (array_key_exists('token', $this->request) &&
        //      !$User->get('token', $this->request['token'])) {

        //     throw new Exception('Invalid User Token');
        // }

        // $this->User = $User;
    }

    /**
      * Helper function that determines if the request object contains the keys given in properties. Properties may either be an array of strings or a singular string
      */
    private function requestHasProperties($properties) {
        if(is_array($properties)) {
            $properties = array($properties);
        }
        else {
            foreach($properties as $property) {
                if(!array_key_exists($properties, $this->request)) {
                    return false;
                }
            }
        }
        return true;
    }

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
        // elseif($this->method == 'GET') {
        //     throw new Exception("Unimplemented");
        // }
        else throw new Exception("Method $this->method is unsupported for end-point " . __FUNCTION__);
    }



    /**
     * The order_case endpoint is able to insert new order cases and linking them with BLE_Tags
     */    
    protected function order_case() {
        
        if($this->method == 'POST') { 
            if($this->requestHasProperties(array('orderCaseId','orderId','bleTagId','barCode'))) {
               $id = $this->database->insert_order_case($this->request['orderCaseId'],$this->request['orderId'],$this->request['bleTagMacAddress'],$this->request['barCode']);
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
     * Example of an Endpoint
     */
     protected function company() {
        if ($this->method == 'GET') {
            return "Your company ID = " . $this->companyId;
        } else {
            return "Only accepts GET requests";
        }
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
