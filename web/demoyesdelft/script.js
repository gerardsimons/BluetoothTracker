var drawunits = true;
var drawlabels = true;
var labelinfo = true;
var labellive = true;

//units
var startcu = [2, 3, 9]; //27348b -> 239
var endcu = [10, 10, 10]; //aaa
var cfromtsu = 60 * 10; //[s], number of seconds after which the color starts to fade from startc to endc
var ctotsu = 3600; //[s], number of seconds after which the color is at endc
//labels
var startc = [0, 15, 0]; //0f0
var endc = [10, 10, 10]; //aaa
var cfromts = 60 * 10; //[s], number of seconds after which the color starts to fade from startc to endc
var ctots = 3600; //[s], number of seconds after which the color is at endc

var map = null;
var overlay = null;

var startpos = new google.maps.LatLng(51.993102, 4.386495);
var startzoom = 16;

$(document).ready(function(e) {
	var opts = {
		center: startpos, //whereAt Yes!Delft coordinates
		zoom: startzoom,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		streetViewControl: false,
		mapTypeControl: false
	};
	map = new google.maps.Map(document.getElementById("map"), opts);
	
	if (mapbounds !== false) {
		map.fitBounds(mapbounds);
	}
	
	google.maps.event.addListener(map, "click", function(e) {console.log(e.latLng.lat(), e.latLng.lng())});
	
	//Yes!Delft overlay
	var opts = {
		map: map,
		clickable: false,
		opacity: 1
	};
	var indoorbounds = new google.maps.LatLngBounds(new google.maps.LatLng(51.99283139483534, 4.385908743187653), new google.maps.LatLng(51.99388561404941, 4.387863656514265));
	overlay = new google.maps.GroundOverlay("mapnorth.png", indoorbounds, opts);
	
	$("#controlpanel button.toggle").each(function(index, element) {
        var buttons = $(this).parent().find("button.toggle");
		var nr = buttons.length;
		var maxw = false;
		buttons.each(function() {
			var w = $(this).outerWidth();
			if (w > maxw || maxw === false) maxw = w;
		});
		var perc = 100 / nr;
		$(this).css({width: perc+"%", 'min-width': maxw+"px"});
    });
	
	$("#mobilemenu").click(function(e) {
        $("body").toggleClass("movedright");
    });
	
	$("#pos_you").hide();
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(function() {
			$("#pos_you").show();
			$("#pos_you").click(function(e) {
				navigator.geolocation.getCurrentPosition(function(pos) {
					$("body").removeClass("movedright");
					var lat = pos.coords.latitude;
					var lon = pos.coords.longitude;
					map.setCenter(new google.maps.LatLng(lat, lon));
					map.setZoom(startzoom);
				}, function() {
					$("#pos_you").hide();
				});
			});
		}, function() {/*no position available*/});
	}
	$("#pos_warehouse").click(function(e) {
        $("body").removeClass("movedright");
        map.fitBounds(indoorbounds);
    });
	$("#pos_bullseye").click(function(e) {
        $("body").removeClass("movedright");
        var minlat = false;
		var minlon = false;
		var maxlat = false;
		var maxlon = false;
		for (var i in labels) {
			var lat = labels[i][0];
			var lon = labels[i][1];
			var display = labels[i][5];
			if (display == false) continue;
			if (lat < minlat || minlat === false) minlat = lat;
			if (lat > maxlat || maxlat === false) maxlat = lat;
			if (lon < minlon || minlon === false) minlon = lon;
			if (lon > maxlon || maxlon === false) maxlon = lon;
		}
		if (minlat === false) return;
        map.fitBounds(new google.maps.LatLngBounds(new google.maps.LatLng(minlat, minlon), new google.maps.LatLng(maxlat, maxlon)));
    });
	$("#settings_toggle").click(function(e) {
        $("#settingscontainer").toggle();
        $("#clusternetwork").toggle();
    });
	$("#toggletags").click(function(e) {
        $("#arrowup").toggle();
        $("#arrowdown").toggle();
        $("#tagstable").toggle();
		$(window).trigger("resize");
    });
	$(window).resize(function(e) {
        if ($("#tagstable").is(":hidden") == false) {
			if ($(window).width() < 979) {
				var top = $("#tagswrapper").offset().top;
				var bottom = top + $("#tagscontainer").outerHeight();
				console.log(top, bottom, $(window).height());
				if (top < 60 || bottom > $(window).height()) {
					$("#tagswrapper").css("top", "60px");
					$("#tagswrapper").css("bottom", "");
					return;
				}
			}
		}
		
		$("#tagswrapper").css("top", "");
		$("#tagswrapper").css("bottom", "0px");
    });
	setTimeout(function() {$(window).trigger("resize");}, 10);
	
	$("button.toggle").each(function(index, element) {
		$(this).click(function(e) {
			$("button[name="+$(this).attr("name")+"]").removeClass("active");
			$(this).addClass("active");
        });
    });
	$("button[name=maptype]").each(function(index, element) {
		$(this).click(function(e) {
			var val = $(this).val();
			if (val == "map") {
				map.setMapTypeId(google.maps.MapTypeId.ROADMAP);
			} else {
				map.setMapTypeId(google.maps.MapTypeId.SATELLITE);
			}
        });
    });
	$("button[name=indoor]").each(function(index, element) {
		$(this).click(function(e) {
			var val = $(this).val();
			if (val == "on") {
				overlay.setMap(map);
			} else {
				overlay.setMap(null);
			}
        });
    });
	$("button[name=access]").each(function(index, element) {
		$(this).click(function(e) {
			var val = $(this).val();
			drawunits = (val == "on") ? true: false;
			DrawUnits();
        });
    });
	$("button[name=labelinfo]").each(function(index, element) {
		$(this).click(function(e) {
			var val = $(this).val();
			labelinfo = (val == "on") ? true: false;
			DrawUnits(true);
			DrawLabels(true);
        });
    });
	$("button[name=labellive]").each(function(index, element) {
		$(this).click(function(e) {
			var val = $(this).val();
			labellive = (val == "rt") ? true: false;
			DrawUnits(true);
			DrawLabels(true);
        });
    });
	
	$("input[type=checkbox].labelcheckbox").each(function(index, element) {
        $(this).change(function(e) {
			var display = ($(this).is(":checked")) ? true: false;
			var id = $(this).attr("labelid");
			labels[id][5] = display;
            DrawLabels();
        });
    });
	
	DrawUnits();
	DrawLabels();
	UpdateLabels();
});

var unitmarkers = [];
var prevunitstate = {};

function DrawUnits(forceupdate) {
	if (typeof forceupdate == "undefined") forceupdate = false;
	if (uniteditmode == false) {
		if (drawunits == false) {
			for (var id in units) {
				if (typeof unitmarkers[id] != "undefined") {
					unitmarkers[id].close();
				}
				if (typeof prevunitstate[id] != "undefined") {
					delete prevunitstate[id];
				}
			}
			return;
		}
		for (var id in units) {
			var lat = units[id][0];
			var lon = units[id][1];
			var ago = units[id][2];
			var name = units[id][3];
			
			if (labellive == true) {
				if (ago < cfromtsu) {
					var rgb = startcu;
				} else if (ago > ctotsu) {
					var rgb = endcu;
				} else {
					var frac = (ago - cfromtsu) / (ctotsu - cfromtsu);
					var rgb = [];
					for (var i=0;i<3;i++) {
						rgb[i] = Math.round(startcu[i] + (endcu[i] - startcu[i]) * frac);
					}
				}
			} else {
				var rgb = startcu;
			}
			var color = "#";
			for (var i=0;i<3;i++) {
				color += rgb[i].toString(16);
			}
			
			if (typeof prevunitstate[id] != "undefined" && forceupdate == false) {
				if (lat == prevunitstate[id][0] && lon == prevunitstate[id][1] && color == prevunitstate[id][2]) continue;
			}
			
			var opts = {
				content: "<div class='itemwrapper' id='unit"+id+"'><div class='unitdot' style='background:"+color+"'></div><div class='unittext'>"+((labelinfo)?name:"")+"</div></div>",
				disableAutoPan: true,
				pixelOffset: new google.maps.Size(0,0),
				position: new google.maps.LatLng(lat, lon),
				closeBoxURL: "",
				isHidden: false,
				pane: "floatPane",
				enableEventPropagation: true,
				zIndex: 9999,
				draggable: true
			};
			if (typeof unitmarkers[id] != "undefined") {
				unitmarkers[id].close();
			}
			unitmarkers[id] = new InfoBox(opts);
			unitmarkers[id].open(map);
			
			prevunitstate[id] = [lat, lon, color];
		}
	} else {
		for (var id in units) {
			var lat = units[id][0];
			var lon = units[id][1];
			var opts = {
				map: map,
				draggable: true,
				position: new google.maps.LatLng(lat, lon),
				title: id
			}
			unitmarkers[id] = new google.maps.Marker(opts);
			google.maps.event.addListener(unitmarkers[id], "dragend", function(e) {
				var lat = e.latLng.lat();
				var lon = e.latLng.lng();
				ReportPos(this.title, lat, lon);
			});
		}
	}
}

var labelmarkers = [];
var labelcircles = [];
var prevstate = {};

function DrawLabels(forceupdate) {
	if (typeof forceupdate == "undefined") forceupdate = false;
	if (drawlabels == false) {
		for (var id in labels) {
			if (typeof labelmarkers[id] != "undefined") {
				labelmarkers[id].close();
			}
			if (typeof labelcircles[id] != "undefined") {
				labelcircles[id].setMap(null);
				delete labelcircles[id];
			}
			if (typeof prevstate[id] != "undefined") {
				delete prevstate[id];
			}
		}
		return;
	}
	for (var id in labels) {
		var lat = labels[id][0];
		var lon = labels[id][1];
		var acc = labels[id][2];
		var ago = labels[id][3];
		var name = labels[id][4];
		var display = labels[id][5];
		
		if (display == false) {
			if (typeof labelmarkers[id] != "undefined") {
				labelmarkers[id].close();
				delete prevstate[id];
			}
			continue;
		}
		
		if (labellive == true) {
			if (ago < cfromts) {
				var rgb = startc;
			} else if (ago > ctots) {
				var rgb = endc;
			} else {
				var frac = (ago - cfromts) / (ctots - cfromts);
				var rgb = [];
				for (var i=0;i<3;i++) {
					rgb[i] = Math.round(startc[i] + (endc[i] - startc[i]) * frac);
				}
			}
		} else {
			var rgb = startc;
		}
		var color = "#";
		for (var i=0;i<3;i++) {
			color += rgb[i].toString(16);
		}
		
		if (typeof prevstate[id] != "undefined" && forceupdate == false) {
			if (lat == prevstate[id][0] && lon == prevstate[id][1] && acc == prevstate[id][2] && color == prevstate[id][3]) continue;
		}
		
		var opts = {
			content: "<div class='itemwrapper'><div class='labeldot' style='background:"+color+"'></div><div class='itemtext'>"+((labelinfo)?name:"")+"</div></div>",
			disableAutoPan: true,
			pixelOffset: new google.maps.Size(0,0),
			position: new google.maps.LatLng(lat, lon),
			closeBoxURL: "",
			isHidden: false,
			pane: "floatPane",
			enableEventPropagation: true,
			zIndex: 9999,
			draggable: true
		};
		if (typeof labelmarkers[id] != "undefined") {
			labelmarkers[id].close();
		}
		labelmarkers[id] = new InfoBox(opts);
		labelmarkers[id].open(map);
		
		var opts = {
			map: map,
			center: new google.maps.LatLng(lat, lon),
			fillColor: color,
			fillOpacity: 0.2,
			radius: acc,
			strokeColor: color,
			strokeOpacity: 1,
			strokeWeight: 2,
			clickable: false
		};
		if (typeof labelcircles[id] != "undefined") {
			labelcircles[id].setMap(null);
			delete labelcircles[id];
		}
		labelcircles[id] = new google.maps.Circle(opts);
		
		prevstate[id] = [lat, lon, acc, color];
	}
}

function ReportPos(unitid, lat, lon) {
	$.ajax("action.php?action=setcoordunit&id="+unitid+"&lat="+lat+"&lon="+lon);
}

function UpdateLabels() {
	$.ajax({
		url: "updatelabels.php",
		cache: false,
		dataType: "script",
		success: function() {
			DrawLabels();
			if (uniteditmode == false) DrawUnits();
			setTimeout(UpdateLabels, 1000);
		}
	});
	$.ajax({url: "triangulate.php", cache: false});
}