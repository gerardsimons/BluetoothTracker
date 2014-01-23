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
        
        <script src="jquery.min.js" type="text/javascript"></script>
        <script type="text/javascript">
			var menuHidden = <?php echo $menuhidden; ?>;
			
			var menuTop = 0;
			function handleScroll() {
				var pos = $("#wrapper").scrollTop();
				var containerWidth = $("#container").outerWidth(true);
				var wrapperWidth = $("#wrapper").width();
				var pad = wrapperWidth - containerWidth;
				var w = wrapperWidth - pad;
				if (pos > menuTop) {
					$("#menu").attr("style", "position:fixed;top:0px;left:0px;width:"+w+"px;padding-right:"+pad+"px");
					$("#menuplaceholder").show();
				} else {
					$("#menu").removeAttr("style");
					$("#menuplaceholder").hide();
				}
			}
			
			function createLongBG() {
				$(".longbg").each(function(index, element) {
                    var id = $(this).attr("id");
					if (id != "") {
						if (!$(this).is(":hidden")) {
							var longid = "long_"+id;
							var top = $(this).offset().top;
							if ($(this).hasClass("extendtobottom")) {
								var height = $("#wrapper")[0].scrollHeight - top;
							} else {
								var height = $(this).outerHeight();
							}
							var bgcolor = $(this).css("background-color");
							var style = "top:"+top+"px;height:"+height+"px;background-color:"+bgcolor;
							if ($("#"+longid).length == 0) {
								$("#widecontainer").append("<div id='"+longid+"' style='"+style+"'></div>");
							} else {
								$("#"+longid).attr("style", style);
							}
						}
					}
                });
			}
			
			$(document).ready(function(e) {
				menuTop = $("#menu").offset().top;
				if (menuHidden == true) $("#menu").hide();
                $(window).resize(function(e) {
                    if (menuHidden == false) handleScroll();
					createLongBG();
                });
				$("#wrapper").scroll(function(e) {
                    if (menuHidden == false) handleScroll();
                });
				$("img").load(function(e) {
                    createLongBG();
                });
				createLongBG();
            });
		</script>
	</head>
    <body>
    	<div id="wrapper">
        	<div id="widecontainer">
            </div>
        	<div id="container" class="longbg">
                <div id="header" class="longbg">
                	<a href="<?php echo str_replace("index.php", "", $_SERVER['SCRIPT_NAME']); ?>"><div id="logonav"></div></a>
                    <div id="headernav">
                        <div style="float:right">// <a href="<?php echo $mainurl ?>contact">Contact</a></div>
                        <a href="<?php echo $mainurl ?>label">Label</a> // <a href="<?php echo $mainurl ?>shield">Shield</a> // <a href="<?php echo $mainurl ?>app">App</a> // <a href="<?php echo $mainurl ?>app">Cloud</a>
                    </div>
                </div>
                <div id="menuplaceholder"></div>
                <div id="menu" class="longbg">
                	<?php echo $menu; ?>
                </div>
                <div id="content" class="longbg">
                    <?php echo $pagecontent; ?>
                </div>
                <div id="footer" class="longbg extendtobottom">
                    &copy; 2014, whereAt Industries. All rights reserved.
                    <div id="disclaimer">
                        The whereAt logo, the whereAt Label logo, the whereAt Cloud logo and the whereAt Shield logo are registered trademarks of whereAt Industries and may not be copied or reproduced without written permission.
                    </div>
                </div>
        	</div>
        </div>
    </body>
</html>