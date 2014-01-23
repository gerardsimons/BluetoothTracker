<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
		<meta http-equiv="Content-Language" content="en" />
		
		<meta name="author" content="whereAt" />
		<meta name="description" content="whereAt enables the tracking of (lost) items through whereAt Label and tracking of children through whereAt Shield, all connected via whereAt Cloud." />
		<meta name="keywords" content="whereat label, whereat shield, whereat cloud, whereat, bluetooth, lost items tracking" />
		
        <link rel="shortcut icon" HREF="<?php echo $mainurl ?>favicon.png" type="image/png" />
		
		<link rel="stylesheet" href="<?php echo $mainurl ?>styles.css" type="text/css" />
        
		<title>whereAt Cloud</title>
        
        <script src="<?php echo $mainurl ?>jquery.min.js" type="text/javascript"></script>
        <script type="text/javascript">
			var menuTop = 0;
			function handleScroll() {
				var pos = $("#wrapper").scrollTop();
				var containerWidth = $("#container").outerWidth(true);
				var wrapperWidth = $("#wrapper").width();
				var pad = wrapperWidth - containerWidth - 2;
				var w = wrapperWidth - pad;
				if (pos > menuTop) {
					$("#menu").attr("style", "position:fixed;top:0px;left:0px;width:"+w+"px;padding-right:"+pad+"px");
					$("#menuplaceholder").show();
				} else {
					$("#menu").removeAttr("style");
					$("#menuplaceholder").hide();
				}
			}
			
			$(document).ready(function(e) {
				menuTop = $("#menu").offset().top;
                $(window).resize(function(e) {
                    handleScroll();
                });
				$("#wrapper").scroll(function(e) {
                    handleScroll();
                });
            });
		</script>
	</head>
    <body>
    	<div id="wrapper"><div id="container"><div id="innerborder">
        	<div id="header">
            	
            </div>
            <div id="menuplaceholder"></div>
            <div id="menu">
            	<?php echo $menu; ?>
            </div>
            <div id="content">
            	<?php echo $pagecontent; ?>
    		</div>
            <div id="footer">
            	&copy; 2014, whereAt Industries. All rights reserved.
                <div id="disclaimer">
                	The whereAt logo, the whereAt Label logo, the whereAt Cloud logo and the whereAt Shield logo are registered trademarks of whereAt Industries and may not be copied or reproduced without written permission.
                </div>
            </div>
        </div></div></div>
    </body>
</html>