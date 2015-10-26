<?php

require_once('../api/v1/api.php')

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');

    echo 'Text to send if user hits Cancel button';

    exit;
} else {

	//Verify user

    echo "<p>Hello {$_SERVER['PHP_AUTH_USER']}.</p>";
    echo "<p>You entered {$_SERVER['PHP_AUTH_PW']} as your password.</p>";

    

    //Show the webpage
    echo '<div id="profile">';

    print_r($user);
    // echo '<table>'
    // echo '<tr><td>Name:'
    // echo '</table>'
    echo '</div>'

    readfile("index.html");
}
?>