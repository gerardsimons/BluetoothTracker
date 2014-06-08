//Single Portfolio handler

define(['modules/loadCss', 'modules/vertical-center'], function(loadCss, vCenter) {
    "use strict";

    var $ = jQuery;

    if(!$('body.single-portfolio').length)
        return;

    //Center the overlay boxes
    var $overlay = $('.portfolio-related .image-overlay');

    if($overlay.length)
    {
        $(window).resize(function(){vCenter($overlay);});
        vCenter($overlay);
    }

    var $flexSlider = $('.flexslider');

    //Don't load flex slider for less than two slides
    if($flexSlider.find('.slides > li').length < 2)
        return;

    //Lazy load required plugins
    require(['jquery.flexslider-min'], function(){
        //Load required css on the fly
        loadCss(theme_uri.css + '/flexslider.css?bust=v2');

        $flexSlider.flexslider();
    });

});