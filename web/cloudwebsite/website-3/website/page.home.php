<script type="application/javascript">$(document).ready(function(e) {
	var html = '<ul>';
	html = html + '<li><div class="active"></div><a href="#"><img src="images/label.png" style="height:30px;" /> Label</a></li>';
	html = html + '<li><div class="active"></div><a href="#"><img src="images/shield.png" style="height:30px;" /> Shield</a></li>';
	html = html + '<li><div class="active"></div><a href="#"><img src="images/assettracking.png" style="height:30px;" /> AT</a></li>';
	html = html + '<li><div class="active"></div><a href="#"><img src="images/cloud.png" style="width:30px;margin:5px 0;" /> Cloud</a></li>';
	html = html + '<li><div class="active"></div><a href="#"><img src="images/smartphone.png" style="height:30px;" /> App</a></li>';
	html = html + '<li><div class="active"></div><a href="#"><img src="images/tech.png" style="height:30px;" /> Tech</a></li>';
	html = html + '</ul>';
    $("#menubarfloatcenter").html(html);
	startFloatMenu();
});</script>

<div class="wrapper">
	<div class="container">
        <div style="width:100%;height:520px" id="slider">
        	<img src="images/slide1.png" alt="whereAt Cloud" style="margin-top:-80px;display:none;" />
            <img src="images/slide2.png" alt="whereAt Label" style="margin-top:-80px;display:none;" />
            <img src="images/slide3.png" alt="whereAt Shield" style="margin-top:-80px;display:none;" />
            <img src="images/slide4.png" alt="whereAt Asset Tracking" style="margin-top:-80px;display:none;" />
        </div>
    </div>
</div>

<style type="text/css">
#slider {
	position:relative;
}

.slidesjs-pagination {
	z-index: 100;
	margin: 6px 0 0;
	list-style: none;
	text-align:center;
	position:relative;
	top:-30px;
	display:none;
}

.slidesjs-pagination li {
	display:inline-block;
	margin: 0 1px;
}

.slidesjs-pagination li a {
  display: block;
  width: 13px;
  height: 0;
  padding-top: 13px;
  background-image: url(images/pagination.png);
  background-position: 0 0;
  float: left;
  overflow: hidden;
}

.slidesjs-pagination li a.active,
.slidesjs-pagination li a:hover.active {
  background-position: 0 -13px
}

.slidesjs-pagination li a:hover {
  background-position: 0 -26px
}
</style>

<script type="text/javascript" src="jquery.slides.js"></script>
<script type="text/javascript">
$(document).ready(function(e) {
    $("#slider").slidesjs({
		width: 960,
		height: 520,
		navigation: {
			active: false
		},
		pagination: {
			active: true,
			effect: "fade"
		},
		play: {
			active: false,
			effect: "fade",
			interval: 5000,
			auto: true,
			swap: true
		},
		effect: {
			face: {speed: 400}
		},
		callback: {
			start: function(id) {
				if (id == 4) id = 0;
				id++;
				SetButtonActive(id);
			}
		}
	});
});
</script>

<div class="wrapper">
	<div class="container">
    	<table border="0" style="width:100%">
        	<tr><td>
                <div class="bigbutton" id="button1">
                    <div class="active"></div><div class="hover"></div>
                    <div class="buttoncontent">
                        <br />
                        <img src="images/cloud.png" style="height:100px" />
                        <br />
                        <span style="font-size:36px">
	                        Cloud
                        </span>
                    </div>
                    <a class="buttonlink" href="#"></a>
                </div>
        	</td><td>
                <div class="bigbutton" id="button2">
                    <div class="active"></div><div class="hover"></div>
                    <div class="buttoncontent">
                        <br />
                        <img src="images/label.png" style="height:100px" />
                        <br />
                        <span style="font-size:36px">
	                        Label
                        </span>
                    </div>
                    <a class="buttonlink" href="#"></a>
                </div>
        	</td><td>
                <div class="bigbutton" id="button3">
                    <div class="active"></div><div class="hover"></div>
                    <div class="buttoncontent">
                        <br />
                        <img src="images/shield.png" style="height:100px" />
                        <br />
                        <span style="font-size:36px">
	                        Shield
                        </span>
                    </div>
                    <a class="buttonlink" href="#"></a>
                </div>
        	</td><td>
                <div class="bigbutton" id="button4">
                    <div class="active"></div><div class="hover"></div>
                    <div class="buttoncontent">
                        <br />
                        <img src="images/assettracking.png" style="height:100px" />
                        <br />
                        <span style="font-size:36px">
	                        Asset Tracking
                        </span>
                    </div>
                    <a class="buttonlink" href="#"></a>
                </div>
        	</td></tr>
       	</table>
    </div>
</div>
<script type="text/javascript">
var speed = 200;
$(document).ready(function(e) {
	$(".bigbutton .active").each(function(index, element) {
        $(this).hide();
		$(this).height($(this).parent().height() - 2);
		$(this).width($(this).parent().width() - 2);
    });
	$(".bigbutton .hover").each(function(index, element) {
        $(this).hide();
		$(this).height($(this).parent().height() - 2);
		$(this).width($(this).parent().width() - 2);
    });
	$(".bigbutton").each(function(index, element) {
        $(this).hover(function(e) {
			$(this).find(".hover").fadeIn(speed);
		}, function(e) {
			$(this).find(".hover").fadeOut(speed);
		});
    });
	$(".bigbutton .buttonlink").click(function(e) {
        ClickButton(this);
    });
	SetButtonActive(1);
});

var cancelanim = false;
function SetButtonActive(id) {
	if (cancelanim == true) return;
	$(".bigbutton").each(function(index, element) {
        var objid = $(this).attr("id");
		if (objid == "button"+id) {
			$(this).find(".active").fadeIn(speed);
		} else {
			$(this).find(".active").fadeOut(speed);
		}
    });
}

function ClickButton(el) {
	var id = $(el).parent().attr("id");
	id = id.substr(6);
	SetButtonActive(id);
	cancelanim = true;
	setTimeout("cancelanim = false;", 500);
	$(".slidesjs-pagination li:eq("+(id-1)+") a").click();
}
</script>
<style type="text/css">
.bigbutton {
	position:relative;
	height:200px;
	margin:0;
	padding:0;
	background: #0070c0; /* Old browsers */
	background: -moz-linear-gradient(top,  #b3b2b2 0%, #9e9e9e 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#b3b2b2), color-stop(100%,#9e9e9e)); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top,  #b3b2b2 0%,#9e9e9e 100%); /* Chrome10+,Safari5.1+ */
	background: -o-linear-gradient(top,  #b3b2b2 0%,#9e9e9e 100%); /* Opera 11.10+ */
	background: -ms-linear-gradient(top,  #b3b2b2 0%,#9e9e9e 100%); /* IE10+ */
	background: linear-gradient(to bottom,  #b3b2b2 0%,#9e9e9e 100%); /* W3C */
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#b3b2b2', endColorstr='#9e9e9e',GradientType=0 ); /* IE6-9 */
}
.bigbutton .active {
	position:absolute;
	left:0px;
	top:0px;
	border:1px #0070c0 solid;
	background: #b3b2b2; /* Old browsers */
	background: -moz-linear-gradient(top,  #b3b2b2 0%, #738a9e 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#b3b2b2), color-stop(100%,#738a9e)); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top,  #b3b2b2 0%,#738a9e 100%); /* Chrome10+,Safari5.1+ */
	background: -o-linear-gradient(top,  #b3b2b2 0%,#738a9e 100%); /* Opera 11.10+ */
	background: -ms-linear-gradient(top,  #b3b2b2 0%,#738a9e 100%); /* IE10+ */
	background: linear-gradient(to bottom,  #b3b2b2 0%,#738a9e 100%); /* W3C */
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#b3b2b2', endColorstr='#738a9e',GradientType=0 ); /* IE6-9 */
	z-index:80;
}
.bigbutton .hover {
	position:absolute;
	left:0px;
	top:0px;
	border:1px #0066FF solid;
	-moz-box-shadow:    0px 0px 5px #0070c0, inset 0 0 5px #0070c0;
	-webkit-box-shadow: 0px 0px 5px #0070c0, inset 0 0 5px #0070c0;
	box-shadow:         0px 0px 5px #0070c0, inset 0 0 5px #0070c0;
	z-index:100;
	background: #b3b2b2; /* Old browsers */
	background: -moz-linear-gradient(top,  #b3b2b2 0%, #738a9e 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#b3b2b2), color-stop(100%,#738a9e)); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top,  #b3b2b2 0%,#738a9e 100%); /* Chrome10+,Safari5.1+ */
	background: -o-linear-gradient(top,  #b3b2b2 0%,#738a9e 100%); /* Opera 11.10+ */
	background: -ms-linear-gradient(top,  #b3b2b2 0%,#738a9e 100%); /* IE10+ */
	background: linear-gradient(to bottom,  #b3b2b2 0%,#738a9e 100%); /* W3C */
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#b3b2b2', endColorstr='#738a9e',GradientType=0 ); /* IE6-9 */
}
.bigbutton .buttoncontent {
	position:absolute;
	left:0px;
	top:0px;
	height:100%;
	width:100%;
	z-index:150;
	text-align:center;
}
.bigbutton .buttonlink {
	display:block;
	position:absolute;
	left:0px;
	top:0px;
	height:100%;
	width:100%;
	z-index:200;
}
</style>

<div class="wrapper lightgrey">
	<div class="container">
    	<div class="content" id="content1">
            <table border="0" style="width:100%"><tr><td style="height:400px;vertical-align:middle">
                <h3 class="right">whereAt Label</h3>
                
                <div style="font-weight:bold;text-align:right;padding-right:30px;color:#108fff;">the next generation of app-enabled tracking devices.</div>
                
                <table border="0" style="width:100%">
                    <tr><td>
                    	<img src="images/label.png" style="margin-left:50px;margin-right:0;height:160px" />
                    </td><td>
                        <table border="0" style="width:100%">
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td>Label your bike, umbrella, keys, briefcase, etc... And you will always know where they are.</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td>This smartphone accessory will find a needle in a haystack, provided that you labelled it first.</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td>When you know you've lost something, finding it is no longer the hard part.</td></tr>
                        </table>
                    </td></tr>
                </table>
            
            </td></tr></table>
        </div>
    </div>
</div>

<div class="wrapper white">
	<div class="container">
    	<div class="content" id="content2">
            <table border="0" style="width:100%"><tr><td style="height:400px;vertical-align:middle">
                <h3 class="">whereAt Shield</h3>
                
                <div style="font-weight:bold;text-align:left;padding-left:30px;color:#108fff;">keep your children in sight, even if they try to hide.</div>
                
                <br />
                
                <table border="0" style="width:100%">
                    <tr><td>
                        <table border="0" style="width:100%">
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td>At the park behind the bushes or at the mall looking at toys... It's not important how your children may have wandered off. What really matters is getting reunited again, and the <i>whereAt Shield</i> will navigate you straight to them in a matter of seconds.</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td>Share the permission to search with aunts, uncles and grandparents so that they too will benefit from advantages the Shield has to offer.</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td>Kindergarten and pre-school excursions will never leave a child behind ever again. Also headcounts will become obsolete, because with whereAt you always know where the children are.</td></tr>
                        </table>
                    </td><td>
                    	<img src="images/shield.png" style="margin-left:50px;margin-right:50px;height:160px" />
                    </td></tr>
                </table>
            
            </td></tr></table>
        </div>
    </div>
</div>

<div class="wrapper lightgrey">
	<div class="container">
    	<div class="content" id="content3">
            <table border="0" style="width:100%"><tr><td style="height:400px;vertical-align:middle">
                <h3 class="right">whereAt Asset Tracking</h3>
                
                <div style="font-weight:bold;text-align:right;padding-right:30px;color:#108fff;">effective management of your corporate resources.</div>
                
                <br />
                
                <table border="0" style="width:100%">
                    <tr><td>
                    	<img src="images/assettracking.png" style="margin-left:50px;margin-right:50px;height:160px" />
                    </td><td>
                        <table border="0" style="width:100%">
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td>Achieve 100% on-site traceability of your corporate assets.</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td>Ideal solution for production and industrial use cases, as well as for medium sized warehouses.</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td>Accessible by all layers of management and operational staff, from a range of mobile and stationary digital interfaces.</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td>Uplink the tracking information to your company's ERP system for complete workflow integration.</td></tr>
                        </table>
                    </td></tr>
                </table>
            
            </td></tr></table>
        </div>
    </div>
</div>

<div class="wrapper white">
	<div class="container">
    	<div class="content" id="content4">
            <table border="0" style="width:100%"><tr><td style="height:400px;vertical-align:middle">
                <h3 class="">whereAt Cloud</h3>
                
                <div style="font-weight:bold;text-align:left;padding-left:30px;color:#108fff;">a tracking platform, powered by the users themselves.</div>
                
                <table border="0" style="width:100%">
                    <tr><td>
                        <table border="0" style="width:100%">
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td>Become a user and help extend the network range.</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td>Manage your whereAt products and access them from different devices.</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td>Invite your friends, family or colleagues to help you search together.</td></tr>
                        </table>
                    </td><td>
                    	<img src="images/cloud.png" style="margin-left:50px;margin-right:50px;height:160px" />
                    </td></tr>
                </table>
            
            </td></tr></table>
        </div>
    </div>
</div>

<div class="wrapper lightgrey">
	<div class="container">
    	<div class="content" id="content5">
            <table border="0" style="width:100%"><tr><td style="height:400px;vertical-align:middle">
                <h3 class="right">whereAt App</h3>
                
                <br />
                
                <table border="0" style="width:100%">
                    <tr><td>
                    	<img src="images/smartphone.png" style="margin-left:50px;margin-right:0;height:160px" />
                    </td><td>
                        <table border="0" style="width:100%">
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td><b>Compass:</b> navigate directly towards your lost device.</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td><b>Always close by:</b> Receive an alert if something is about to be left behind.</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td><b>View on a map:</b> Pinpoint the last know location on a map, even if you are worlds away.</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td><b>whereAt Cloud:</b> Invite your social connections to assist you in finding what you lost.</td></tr>
                        </table>
                    </td></tr>
                </table>
            
            </td></tr></table>
        </div>
    </div>
</div>

<div class="wrapper white">
	<div class="container">
    	<div class="content" id="content6">
            <table border="0" style="width:100%"><tr><td style="height:400px;vertical-align:middle">
                <h3 class="">Technology</h3>
                <br />
                <table border="0" style="width:100%">
                    <tr><td>
                        <table border="0" style="width:100%">
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td>Bluetooth 4.0</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td>Battery life is 1 year, the battery can be easily replaced.</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td>Range of operation of 50 meters (165 feet), without using whereAt Cloud.</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td>All whereAt products are waterproof.</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td><img src="<?php echo $mainurl ?>images/vinkje.png" /></td><td style="width:10px"></td><td>The product-appearances are adjustable, ensuring a tailored user experience.</td></tr>
                        </table>
                    </td></tr>
                </table>
            
            </td></tr></table>
        </div>
    </div>
</div>