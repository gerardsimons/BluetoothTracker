//Google maps handler

define([], function() {
    "use strict";

    var $ = jQuery,
        $gmapContainers = $('.gmap');

    if(!$gmapContainers.length) return;

    var gmapUri = 'async!http://maps.google.com/maps/api/js?key='+ gkey +'&sensor=false&language=en';

    //Lazy load required plugins
    require([gmapUri, 'gmap3.min'], function(){

        $gmapContainers.each(function(){
            var $map = $(this),
                $markers = $map.find('.gmap-marker'),
                zoom = parseInt($map.attr('data-zoom')),
                addr = $map.attr('data-address'),
                ctrl = $map.attr('data-controls') === 'true',
                lat  = $map.attr('data-lat'),
                lng  = $map.attr('data-lng'),
                markers = [];

            //Get marker data
            $markers.each(function(){
                var $marker = $(this),
                    mlat    = $marker.attr('data-lat'),
                    mlng    = $marker.attr('data-lng'),
                    maddr   = $marker.attr('data-address'),
                    icon    = $marker.attr('data-icon'),
                    marker  = {};

                if(mlat.length && mlng.length)
                {
                    mlat = parseFloat(mlat);
                    mlng = parseFloat(mlng);
                    marker['latLng'] = [mlat, mlng];
                }
                else if(addr.length)
                {
                    marker['address'] = maddr;
                }
                else
                    return;

                //Set icon
                if(icon.length)
                    marker['options'] = {icon: icon};
                else
                    marker['options'] = {icon: theme_uri.img + '/gmap-marker.png'};


                markers.push(marker);
            });


            var settings = {
                map:{
                    options:{
                        zoom:zoom,
                        disableDefaultUI: !ctrl,
						scrollwheel: false,
                        draggable: false
                    }
                }
            };

            //Prefer lat/lng over address
            if(lat.length && lng.length)
            {
                lat = parseFloat(lat);
                lng = parseFloat(lng);
                settings.map.options['center'] = [lat, lng];
            }
            else if(addr.length)
            {
                settings.map['address'] = addr;
            }
            else
            {
                //Default location
                settings.map.options['center'] = [29.697421,52.470375];
            }

            //Add markers
            if(markers.length)
            {
                settings['marker'] = {values: markers};
            }

            $map.gmap3(settings);
        });

    });

});