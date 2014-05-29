<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
		<meta http-equiv="Content-Language" content="en" />
		
		<meta name="description" content="whereAt Label, whereAt Shield and whereAt Asset Tracking, all connected via the whereAt Cloud." />
		
        <link rel="shortcut icon" HREF="<?php echo $mainurl ?>favicon.png" type="image/png" />
        
        
        
        
        <!--
/**
 * @license
 * MyFonts Webfont Build ID 2798713, 2014-04-22T07:59:33-0400
 * 
 * The fonts listed in this notice are subject to the End User License
 * Agreement(s) entered into by the website owner. All other parties are 
 * explicitly restricted from using the Licensed Webfonts(s).
 * 
 * You may obtain a valid license at the URLs below.
 * 
 * Webfont: Brandon Grotesque Thin Italic by HVD Fonts
 * URL: http://www.myfonts.com/fonts/hvdfonts/brandon-grotesque/thin-italic/
 * 
 * Webfont: Brandon Grotesque Thin by HVD Fonts
 * URL: http://www.myfonts.com/fonts/hvdfonts/brandon-grotesque/thin/
 * 
 * Webfont: Brandon Grotesque Regular by HVD Fonts
 * URL: http://www.myfonts.com/fonts/hvdfonts/brandon-grotesque/regular/
 * 
 * Webfont: Brandon Grotesque Bold by HVD Fonts
 * URL: http://www.myfonts.com/fonts/hvdfonts/brandon-grotesque/bold/
 * 
 * 
 * License: http://www.myfonts.com/viewlicense?type=web&buildid=2798713
 * Licensed pageviews: 10,000
 * Webfonts copyright: Copyright (c) 2009 by Hannes von Doehren. All rights reserved.
 * 
 * © 2014 MyFonts Inc
*/

-->
		
        
        
        
        
        
        
        
        
        
		
		<link rel="stylesheet" href="<?php echo $mainurl ?>styles.css" type="text/css" />
        
		<title>whereAt</title>
        
        <script src="jquery.min.js" type="text/javascript"></script>
        <script src="jquery.smooth-scroll.js" type="text/javascript"></script>
        <script type="text/javascript">
			var startopac;
			$(document).ready(function(e) {
                $(window).resize(function(e) {
                    handleResize();
                });
				$(window).scroll(function(e) {
                    handleScroll();
                });
				startopac = $(".menubarfloatbg").css("opacity");
				handleResize();
				handleScroll();
				startFloatMenu();
            });
			function startFloatMenu() {
				$(".menubarfloat a").each(function(index, element) {
                    var content = $(this).html();
					content = "<span style='position:relative;top:-3px'>"+content+"</span>";
					$(this).html(content);
                });
				$("#menubarfloatcenter img").each(function(index, element) {
                    $(this).css({position: "relative", top: "4px"});
                });
				$("#menubarfloatcenter .active").each(function(index, element) {
					$(this).hide();
				});
				$("#menubarfloatcenter a").each(function(index, element) {
					$(this).click(function(e) {
						e.preventDefault();
						$.smoothScroll({scrollTarget: "#content"+(index+1)});
						/*var top = $("#content"+(index+1));
						if (top.length > 0) {
							top = top.offset().top;
							//$("html, body").scrollTop(top);
							$('html, body').animate({
								scrollTop: top
							}, 2000);
						}*/
                    });
                });
				handleResize();
				handleScroll();
			}
			function handleResize() {
				var limit = 960;
				
				var w = $(window).width();
				if (w < limit) w = limit;
				
				var cw = $(".container").first().width();
				var cleft = (w - cw) * 0.5;
				if (cleft < 0) cleft = 0;
				$("#menubarfloatcenter").css("left", cleft+"px");
				
				var cwidth = cw;
				var rwidth = $("#menubarfloatright a").width();
				var rleft = $(window).width() - rwidth;
				if (rleft + rwidth < limit) {
					rleft = limit - rwidth;
					$("#menubarfloatright").css("left", rleft+"px");
				} else {
					$("#menubarfloatright").css("left", "");
				}
				if (rleft - cleft < cw) {
					cwidth = rleft - cleft;
				}
				$("#menubarfloatcenter").width(cwidth);
				
				var nrmenu = $("#menubarfloatcenter li").length;
				var aw = (cwidth / nrmenu) - 2;
				$("#menubarfloatcenter a").each(function(index, element) {
                    $(this).width(aw);
                });
				posActiveFloat();
			}
			function handleScroll() {
				var pos = $(window).scrollTop();
				
				var menutop = $("#headernav").offset().top;
				var menuheight = $("#headernav").height();
				var menubottom = menutop + menuheight;
				
				if (pos < menutop) {
					$(".menubarfloatbg").hide();
					$("#menubarfloatright").hide();
				} else if (pos > menubottom) {
					$(".menubarfloatbg").show().css("opacity", startopac);
					$("#menubarfloatright").show().css("opacity", 1);
				} else {
					var perc = (pos - menutop) / menuheight;
					$(".menubarfloatbg").show().css("opacity", perc * startopac);
					$("#menubarfloatright").show().css("opacity", perc);
				}
				
				<?php if ($page == "home") { ?>
				var buttonstop = $("#headernav").offset().top + 50;
				var buttonsheight =  $("#headernav").height();
				var buttonsbottom = buttonstop + buttonsheight;
				buttonsheight = 30;
				buttonstop = buttonsbottom - buttonsheight;
				
				if (pos < buttonstop) {
					$("#menubarfloatcenter").hide();
				} else if (pos > buttonsbottom) {
					$("#menubarfloatcenter").show().css("opacity", 1);
					posActiveFloat();
				} else {
					var perc = (pos - buttonstop) / buttonsheight;
					$("#menubarfloatcenter").show().css("opacity", perc);
					posActiveFloat();
				}
				
				var buttonheight = $("#menubarfloatcenter").height();
				$("#menubarfloatcenter a").each(function(index, element) {
					var top = $("#content"+(index+1));
					var nexttop = $("#content"+(index+2));
					var actel = $(this).parent().find(".active");
					if (top.length > 0 && actel.length > 0) {
						top = top.offset().top - buttonheight;
						bottom = top + buttonheight;
						
						if (pos < top) {
							actel.hide();
						} else if (pos > bottom) {
							if (nexttop.length > 0) {
								nexttop = nexttop.offset().top - buttonheight;
								nextbottom = nexttop + buttonheight;
								
								if (pos < nexttop) {
									actel.show().css("opacity", 1);
								} else if (pos > nextbottom) {
									actel.hide();
								} else {
									var perc = 1 - ((pos - nexttop) / buttonheight);
									actel.show().css("opacity", perc);
								}
							} else {
								actel.show().css("opacity", 1);
							}
						} else {
							var perc = (pos - top) / buttonheight;
							actel.show().css("opacity", perc);
						}
					}
                });
				<?php } ?>
			}
			function posActiveFloat() {
				$("#menubarfloatcenter .active").each(function(index, element) {
					$(this).height($(this).parent().height() - 2);
					$(this).width($(this).parent().width() - 2);
				});
			}
		</script>
	</head>
    <body>
    	
    	<?php include_once("analyticstracking.php") ?> 
    	
        <div class="menubarfloatbg"></div>
        <div id="menubarfloatcenter" class="menubarfloat">
        </div>
        <div id="menubarfloatright" class="menubarfloat">
            <ul>
                <li>
                    <a href="<?php echo $mainurl ?>contact">Contact</a>
                </li>
            </ul>
        </div>
    	
        <div class="wrapper">
            <div class="container">
                <div id="header">
                    <a href="<?php echo str_replace("index.php", "", $_SERVER['SCRIPT_NAME']); ?>"><div id="logonav"></div></a>
                    <div id="headernav" class="menu">
                        <ul>
                            <li><a href="<?php echo str_replace("index.php", "", $_SERVER['SCRIPT_NAME']); ?>">Products</a>
                                <?php /*<ul>
                                    <li><a href="<?php echo $mainurl ?>label">Label</a></li>
                                    <li><a href="<?php echo $mainurl ?>shield">Shield</a></li>
                                    <li><a href="<?php echo $mainurl ?>assettracking">Asset Tracking</a></li>
                                </ul>*/ ?>
                            </li>
                            <li>//</li>
                            <li><a href="<?php echo $mainurl ?>contact">Contact</a></li>
                        </ul>
                    </div>
                    <div style="float:right;margin-right:60px;margin-top:100px;">
                    	<a href="https://www.facebook.com/pages/whereAt/229252010581611" target="new"><img src="<?php echo $mainurl ?>images/fb.jpg" /></a>
                    	<a href="https://twitter.com/@whereAtcloud" target="new"><img src="<?php echo $mainurl ?>images/tw.jpg" /></a>
                    	<a href="https://www.linkedin.com/company/5028689" target="new"><img src="<?php echo $mainurl ?>images/li.jpg" /></a>
                    </div>
                </div>
            </div>
        </div>
        
        <?php echo $pagecontent; ?>
        
        <div class="wrapper footerbg">
            <div class="container">
                <div id="footer">
                    <div style="float:right;margin-right:20px;margin-top:10px;">
                    	<a href="https://www.facebook.com/pages/whereAt/229252010581611" target="new"><img src="<?php echo $mainurl ?>images/fb.jpg" /></a>
                    	<a href="https://twitter.com/@whereAtcloud" target="new"><img src="<?php echo $mainurl ?>images/tw.jpg" /></a>
                    	<a href="https://www.linkedin.com/company/5028689" target="new"><img src="<?php echo $mainurl ?>images/li.jpg" /></a>
                    </div>
                    &copy; 2014, whereAt International&#8482;. All rights reserved.
                    <div id="disclaimer">
                        The whereAt &#8482; logo, the whereAt Label&#8482; logo, the whereAt Cloud&#8482; logo and the whereAt Shield&#8482; logo are registered trademarks of whereAt International&#8482; and may not be copied or reproduced without written permission.
                    </div>
                </div>
            </div>
        </div>
        
    </body>
</html>