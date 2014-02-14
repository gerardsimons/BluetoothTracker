<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
		<meta http-equiv="Content-Language" content="en" />
		
		<meta name="description" content="whereAt Label, whereAt Shield and whereAt Asset Tracking, all connected via the whereAt Cloud." />
		
        <link rel="shortcut icon" HREF="<?php echo $mainurl ?>favicon.png" type="image/png" />
		
		<link rel="stylesheet" href="<?php echo $mainurl ?>styles.css" type="text/css" />
        
		<title>whereAt</title>
        
        <script src="jquery.min.js" type="text/javascript"></script>
	</head>
    <body>
    	
        <div class="wrapper">
            <div class="container">
                <div id="header">
                    <a href="<?php echo str_replace("index.php", "", $_SERVER['SCRIPT_NAME']); ?>"><div id="logonav"></div></a>
                    <div id="headernav" class="menu">
                        <ul>
                            <li><a href="<?php echo str_replace("index.php", "", $_SERVER['SCRIPT_NAME']); ?>">Products</a>
                                <ul>
                                    <li><a href="<?php echo $mainurl ?>label">Label</a></li>
                                    <li><a href="<?php echo $mainurl ?>shield">Shield</a></li>
                                    <li><a href="<?php echo $mainurl ?>assettracking">Asset Tracking</a></li>
                                </ul>
                            </li>
                            <li>//</li>
                            <li><a href="<?php echo $mainurl ?>contact">Contact</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <?php echo $pagecontent; ?>
        
        <div class="wrapper footerbg">
            <div class="container">
                <div id="footer">
                    &copy; 2014, whereAt Industries. All rights reserved.
                    <div id="disclaimer">
                        The whereAt logo, the whereAt Label logo, the whereAt Cloud logo and the whereAt Shield logo are registered trademarks of whereAt Industries and may not be copied or reproduced without written permission.
                    </div>
                </div>
            </div>
        </div>
        
    </body>
</html>