//Post Gallery handler

define(['modules/loadCss'], function(loadCss) {
    "use strict";

    var $ = jQuery;

    if(!$('body.blog,body.single-post,body.archive').length) return;

    var $flexSlider = $('.flexslider');

    //Don't load flex slider for less than two slides
    if($flexSlider.find('.slides > li').length < 2)
        return;

    //Lazy load required plugins
    require(['jquery.flexslider-min'], function(){
        //Load required css on the fly
        loadCss(theme_uri.css + '/flexslider.css');

        $flexSlider.flexslider();
    });

});