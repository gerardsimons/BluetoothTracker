<?

require_once('database-config.php');

/**
  *     The database wrapper, does the required database operations on the underlying MySQL database
  */
class Database {

    private $db; //The actual MySQLi db instance
    private $config;

    public function __construct() {
        $this->db = new MysqliDb (SERVER,USER,PASS,DATABASE_NAME);
    }

    /**
     *      Throws the last query and error as an exception
     */
    private function sql_error() {
        throw new Exception("Failed to execute SQL Query " . $this->db->getLastQuery() . " ERROR : " . $this->db->getLastError());
    }

    public function update_settings($user,$password,$alert) {
        $sql = 'UPDATE settings,users SET Alert = ? WHERE settings.User_ID = users.ID AND Username = ? AND Password = SHA1(?)';
        $result = $this->db->rawQuery($sql,array($alert,$user,$password));
        // echo $this->db->getLastQuery();

        return $result;
    }

    public function select_user_for_credentials($username,$password) {
        $sql = 'SELECT Company_ID,Logo_URL,Username,Email,Country,Business_Unit,First_Name,Last_Name,Phone_Number,Gender,Position,settings.* FROM users,companies,settings WHERE settings.User_ID = users.ID AND Username = ? AND Password = SHA1(?)';

        $result = $this->db->rawQuery($sql,array($username,$password));

        // echo $this->db->getLastQuery();

        return $result;
    }

    //Select company for a API_Key and is its subscription still active
    public function select_company_for_api_key($apiKey) {
        // $sql = 'SELECT * FROM Subscription,Company WHERE API_Key = ? AND Company.Subscription_ID = Subscription.ID AND Subscription.State = \'ACTIVE\' AND Expiry_Date > NOW()';
        $sql = 'SELECT companies.* FROM subscriptions,companies WHERE API_Key = ? AND subscriptions.State = \'ACTIVE\'';
        // $sql = 'SELECT companies.ID AS ID FROM subscriptions,companies WHERE API_Key = ?';
        $result = $this->db->rawQuery($sql,array($apiKey));
        // echo $this->db->getLastQuery();

        if(isset($result) && count($result) > 0) {
            return $result[0];
        }
        else return null;
    }

    public function select_ble_tags($companyId) {
        $sql = "SELECT * FROM ble_tags WHERE Company_ID = ?";
        return $this->db->rawQuery($sql,array($companyId));
    }

    public function select_ble_trackers($companyId) {
        $sql = "SELECT * FROM ble_trackers WHERE Company_ID = ?";
        return $this->db->rawQuery($sql,array($companyId));
    }

    //Select routes for a given company
    public function select_routes_for_company($companyId) {
        $sql = "SELECT routes.* FROM routes,ble_trackers WHERE routes.BLE_Tracker_ID = ble_trackers.ID AND Company_ID = ?";
        $result = $this->db->rawQuery($sql,array($companyId));
        
        if(isset($result)) {
            // echo $this->db->getLastQuery();
            return $result;
        }
        else $this->sql_error();
    }

    public function select_route($routeId) {
        $sql = "SELECT * FROM routes WHERE ID = ?";
        $result = $this->db->rawQuery($sql,array($routeId));

        if(isset($result) && count($result) > 0) {
            // echo $this->db->getLastQuery();
            return $result[0];
        }

        return $result;
    }

    public function select_full_route($routeId) {

    }

    public function select_gps_data($routeId) {
        $sql = "SELECT gps_data.* FROM gps_data,routes WHERE Route_ID = routes.ID AND routes.ID = ?";
        $result = $this->db->rawQuery($sql,array($routeId));

        // echo $this->db->getLastQuery();

        return $result;
    }

    public function select_rssi_data($routeId) {
        $sql = "SELECT rssi_data.* FROM rssi_data,routes WHERE Route_ID = routes.ID AND routes.ID = ?";
        $result = $this->db->rawQuery($sql,array($routeId));

        // echo $this->db->getLastQuery();

        return $result;
    }

    //Select the complete data for a given order 
    public function select_order($orderId) {

        $sql = "SELECT * FROM orders,customers,locations WHERE orders.ID = ? AND orders.Customer_ID = customers.ID AND locations.ID = customers.Location_ID";

        $result = $this->db->rawQuery($sql,array($orderId));
        // echo $this->db->getLastQuery();
        // print_r($result);
        return $result[0];
    }

    public function select_order_cases_for_order($orderId) {
        $sql = "SELECT * FROM order_cases WHERE Order_ID = ?";
        $result = $this->db->rawQuery($sql,array($orderId));
        // echo $this->db->getLastQuery();
        return $result;
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
        // echo $id;
        if($id) {
            return $id;
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

        // $dt = new DateTime("@$time");
        // $time = $dt->format('Y-m-d H:i:s');

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

        // $format = 'Y-m-d H:i:s';
        // $datetime = date($format,$timestamp);

        $result = $this->db->rawQuery($sql,array($routeId,$timestamp,$rssi,$bleTagMacAddress));

        // echo $this->db->getLastQuery();
        // echo $this->db->getLastError();
        // print_r($result);

        return 1;
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
        // $format = 'Y-m-d H:i:s';
        // $datetime = date($format,$end);

        $data = array(
            "End" => $end
        );

        $this->db->where('Order_ID', $orderId);
        $this->db->where('ID',$orderCaseId);
        $result = $this->db->update('order_cases', $data);

        // echo $this->db-getLastQuery();
        return $result;
    }

    /**
     *  Update a route with route id to reflect the new start time, and possibly a company's branch location
     */
    public function update_route_with_departure($routeId, $departTime, $lat, $long) {
        $data = array(
            "Departed" => $departTime,
            "Departure_Lat" => $lat,
            "Departure_Long" => $long
        );
        $this->db->where('ID', $routeId);
        // $this->db->
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

    /** 
      * Select orders for given company 
      */
    public function select_orders_for_company($companyId) {
        // $sql = SELECT 
        //TODO: FIX-ME: This SQL seems nasty with the group by, perhaps the SQL structure is off ?
        $sql = "SELECT orders.* FROM orders,order_cases,routes,ble_trackers WHERE Company_ID = ? AND orders.ID = order_cases.Order_ID AND routes.ID = order_cases.Route_ID AND routes.BLE_Tracker_ID = ble_trackers.ID GROUP BY Order_ID";
        return $this->db->rawQuery($sql,array($companyId));
    }

    public function select_orders_for_route($routeId) {
        $sql = "SELECT orders.* FROM orders,order_cases WHERE orders.ID = order_cases.Order_ID AND Route_ID = ? GROUP BY Orders.ID";
        return $this->db->rawQuery($sql,array($routeId));
    }

    /**
      * Select all the order cases that belong to the given route 
      */
    public function select_order_cases_for_route($routeId) {
        $sql = "SELECT * FROM order_cases WHERE Route_ID = ?";
        return $this->db->rawQuery($sql,array($routeId));
    }

    public function select_customers_for_company($companyId) {
        $sql = "SELECT * FROM customers WHERE Company_ID = ?";
        return $this->db->rawQuery($sql,array($companyId));
    }

    public function select_first_gps_data_for_route($routeId) {
        $sql = "SELECT Latitude,Longitude FROM gps_data WHERE route_id = ? order by Time_Read limit 1";
        return $this->db->rawQuery($sql,array($routeId));
    }

    /*
     *
     */
    // public function 
}

?>
