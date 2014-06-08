<?php

if(!isset($_POST['sc']))
{
    echo '"sc" post parameter is missing!';
    return;
}

// get the shortcode
$sc = $_POST['sc'];

//Used for wp functions
require_once('../../../../../wp-config.php');

?>
<!doctype html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title></title>
    <?php wp_head(); ?>
    <style type="text/css">
        html{
            margin-top:0 !important;
        }
        #wpadminbar{
            display: none;
        }
    </style>
</head>
<body>
    <?php echo do_shortcode( $sc ); ?>
    <?php wp_footer(); ?>
</body>
</html>