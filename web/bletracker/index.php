<?
require_once("MySQLi/MysqliDb.php");

$server = "localhost:3306";
$user = "whereAt";
$pass = "whereAt2014";
$dbName = "ble_tracker";

$db = new MysqliDb ($server,$user,$pass,$dbName);

abstract class Actions
{
	const Authenticate = "authenticate";
	const RegisterBLEController = "register_ble_controller";
    const LinkLabelToOrder = "link_label";
    const StartRoute = "start_route";
    // const Monday = 
}

function authenticate($jsonRequest) {
	global $db;

	if(isset($jsonRequest) && property_exists($jsonRequest,'apiKey')) {

		$api_key = $jsonRequest->apiKey;

		// echo "PRINT";
		// $sql = 'SELECT * FROM Subscription,Company WHERE API_Key = ? AND Company.Subscription_ID = Subscription.ID AND Subscription.State = \'ACTIVE\' AND Expiry_Date > NOW()';
		$sql = 'SELECT * FROM Subscription,Company WHERE API_Key = ?';
		$result = $db->rawQuery($sql,array($api_key));

		// $db->where ("API_Key", $api_key);
		// $user = $db->getOne ("Company");
		// print_r($result);
		if(isset($result)) {
			return count($result) > 0;
		}
		else return false;
	}
	else return false;
}

function printPostAndGetVars() {

	echo "POST VARS : <br/>";
	foreach ($_POST as $param_name => $param_val) {
    	echo "Param: $param_name; Value: $param_val<br />\n";
	}

	echo "<br/><br/>GET VARS : <br/>";
	foreach ($_GET as $param_name => $param_val) {
	    echo "Param: $param_name; Value: $param_val<br />\n";
	}
}

function failResponse($jsonResponse,$message) {
	$jsonResponse['success'] = false;
	$jsonResponse['message'] = $message;

	return $jsonResponse;
}

function successResponse($jsonRsponse) {
	$jsonResponse['success'] = true;
	return $jsonResponse;
}

function linkLabelsToOrder() {

}

function insertBLEController($deviceId,$installId) {
	global $db;

	$data = array(	
		'Device_ID' => $deviceId,
		'Install_ID' => $installId
	);
	$id = $db->insert('BLE_Controller',$data);
	return $id;
}

function registerBLEController($response, $requestObject) {
	// print_r($requestObject);

	$deviceId = $requestObject->deviceId;
	$installId = $requestObject->installId;

	$id = insertBLEController($deviceId,$installId);

	if($id) {
		return successResponse($response);
	}
	else {
		return failResponse($response,"Registering new BLE Controller failed");
	}
} 

function processRequest($jsonRequest) {

	//Always authenticate first

	//When succesful determine if a valid action is requested

	$jsonResponse = array();

	if(authenticate($jsonRequest)) {
		
		//Get action, determine what to do to accordingly
		if(property_exists($jsonRequest,'request') && property_exists($jsonRequest->request, 'name')) {

			$request = $jsonRequest->request;
			$requestName = $request->name;
			// print_r ($request);
			// print_r($requestName);
			//
			switch ($requestName) {
				case Actions::Authenticate:
					return successResponse($jsonResponse);
				case Actions::LinkLabelToOrder:
					return linkLabelsToOrder();
				case Actions::RegisterBLEController:
					return registerBLEController($jsonResponse,$request);
				default:
					return failResponse($jsonResponse,"Unknown request");
			}
		}
		
		return failResponse($jsonResponse,"No request object found");
	}
	else {
		return failResponse($jsonResponse,"Authentication failed.");
	}
}

//RAW VERSION
// $mysqli = mysqli_connect("localhost", $user, $pass, $db);
// $res = mysqli_query($mysqli, "SELECT Name FROM Company");
// $row = mysqli_fetch_assoc($res);
// echo $row['Name'];

$json = json_decode(file_get_contents('php://input'));
// echo $response;

header('Content-Type: application/json');
$response = processRequest($json);

// echo '{ "test" : 123 }'
// echo json_encode();
printPostAndGetVars();

echo json_encode($response);

// print "<br/>";

// $users = $db->get ("Company");
// if ($db->count > 0)
//     foreach ($users as $user) { 
//         print_r ($user);
//     }

?>