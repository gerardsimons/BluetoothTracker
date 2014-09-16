/*
jQuery plugin to enable long page menus (buttons to go to certain parts of the long page that stick to the top of the page when scrolling down)
Written by Jasper Bussemaker, 2014

Usage:
<div class="longpagemenu" style="text-align: center;">[button text='Industries' url='#industries'/][button text='Benefits' url='#Benefits'/][button text='Pricing' url='#Pricing'/][button text='Cluster Network' url='#cn'/][button text='Show Case' url='#showcase'/]</div>

Only one menu per page!
*/

var menuoffset = 50; //[px]

var menuobj = null;
var menuattobj = null;
var menutop = null;
var buttontops = null;
var lpforceheight = 35;

var lpbgcolor = null;
var lpcolor = null;
var lpmenuheight = null;

(function($) {
	$(document).ready(function(e) {
		menuobj = $("div.longpagemenu");
		if (menuobj.length != 1) return;
		if (menuobj.find("a.button").length == 0) return;
		
		menutop = $(menuobj).offset().top;
		
		lpbgcolor = menuobj.find("a.button").css("background-color");
		lpcolor = menuobj.find("a.button").css("color");
		lpmenuheight = (lpforceheight !== null) ? lpforceheight: menuobj.height();
		
		var nr = menuobj.find("a.button").length;
		var maxperc = Math.floor(parseFloat(100 / nr));
		
		var maxw = 0;
		menuobj.find("a.button").each(function(index, element) {
			var bw = $(this).outerWidth();
			if (bw > maxw) {
				maxw = bw;
			}
		});
		var buttonw = maxw;
		
		var html = "<div id='longpagemenu' style='top:"+menuoffset+"px;height:"+lpmenuheight+"px;line-height:"+lpmenuheight+"px;'>";
		menuobj.find("a.button").each(function(index, element) {
            var href = $(this).attr("href");
			var content = $(this).html();
			html += "<a style='height:"+lpmenuheight+"px;max-width:"+maxperc+"%;width:"+buttonw+"px;' href='"+href+"'>"+content+"</a>";
        });
		html += "</div><div id='longpagemenushadow' style='top:"+(menuoffset+0.5*lpmenuheight)+"px;height:"+(lpmenuheight*0.5)+"px;'></div>";
		
		menuobj.after(html);
		
		getButtonTops();
		setTimeout(getButtonTops, 100);
		setTimeout(getButtonTops, 500);
		setTimeout(getButtonTops, 1000);
		setInterval(getButtonTops, 2000);
		
		setMenuTop();
		setTimeout(setMenuTop, 100);
		setTimeout(setMenuTop, 500);
		setTimeout(setMenuTop, 1000);
		setInterval(setMenuTop, 2000);
		
		$(window).scroll(function(e) {
			longPageHandleScroll();
        });
		longPageHandleScroll();
		
		menuobj.hide();
		
		$(".wrap #main").prepend("<div style='height:20px'></div>");
	});
	
	function getButtonTops() {
		buttontops = [];
		
		$(menuobj).find("a.button").each(function(index, element) {
			var href = $(this).attr("href");
			if (href.substr(0, 1) != "#") return;
			
			var target = "[name="+href.substr(1)+"]";
			if ($(target).length == 0) {
				target = "#"+href.substr(1);
				if ($(target).length == 0) return;
			}
			
			buttontops[index] = Math.floor($(target).offset().top);
		});
		
		longPageHandleScroll();
	}
	
	function longPageHandleScroll() {
		var scrollTop = $(window).scrollTop();
		
		/*if (scrollTop < menutop - menuoffset) { //menu on original position
			$("#longpagemenu").fadeOut(300);
			$("#longpagemenushadow").fadeOut(300);
		} else { //menu attached to top
			$("#longpagemenu").fadeIn(300);
			$("#longpagemenushadow").fadeIn(300);
		}*/
		
		var active = false;
		for (var i in buttontops) {
			if (buttontops[i] <= scrollTop) active = i;
		}
		$("#longpagemenu").find("a").each(function(index, element) {
			if (index == active && active !== false) {
				$(this).css({'background-color': lpbgcolor, color: lpcolor});
			} else {
				$(this).css({'background-color': "", color: ""});
			}
		});
		
		setMenuTop();
	}
	
	function setMenuTop() {
		var headertop = parseInt($("header.header-default").offset().top - $(window).scrollTop() + $("header.header-default").height());
		if (headertop < 0) headertop = 0;
		var menutop = headertop;
		
		var currtop = parseInt($("#longpagemenu").css("top").replace("px", ""));
		if (currtop != menutop) {
			var diff = menutop - currtop;
			$("#longpagemenu").css("top", menutop+"px");
			
			var shadowtop = parseInt($("#longpagemenushadow").css("top").replace("px", "")) + diff;
			$("#longpagemenushadow").css("top", shadowtop+"px");
		}
	}
})(jQuery);