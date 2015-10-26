<?

$string = "[{\"orderId\":20,\"orderCaseId\":1001100}]";
$encoded = json_decode($string);

// print_r($string);

$emptyArray = array(1);

if($emptyArray == false) {
	echo "EMPTY";
}

echo "DECODED: ";

foreach($encoded as $element) {
	print_r($element->orderId);
}

// print($encoded);

// $dateString = $_GET['date'];
// echo $dateString;
// $format = 'Y-m-d H:i:s';  
// $date = DateTime::createFromFormat($format, $dateString);
//     echo $date->format('Y-m-d H:i:s'); 

// $test = array();
// $test[0] = 1;

// print_r($test);


function testUnset() {
	$arr = array(
		"something" => "special",
		"another" => "hello"
	);



	print_r($arr);

	unset($arr['something']);
	print("<br/>");

	print_r($arr);
}

// testUnset();

?>