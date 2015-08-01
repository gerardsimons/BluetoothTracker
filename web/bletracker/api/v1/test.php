<?

$dateString = $_GET['date'];
// echo $dateString;
$format = 'Y-m-d H:i:s';  
$date = DateTime::createFromFormat($format, $dateString);
    echo $date->format('Y-m-d H:i:s'); 

// $test = array();
// $test[0] = 1;

// print_r($test);

?>