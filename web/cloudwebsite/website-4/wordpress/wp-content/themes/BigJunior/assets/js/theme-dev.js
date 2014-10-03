/**
 * Theme UI Handlers
 * Author    : Mohsen Heydari
 * Version   : 1.0
 * Web site  : http://devmash.net
 * Contact   : mohsenheydari@live.com
 */

/*
require.config({
    //urlArgs: "bust=" + (new Date()).getTime(),
    urlArgs: "bust=v1"
});
*/

//Theme entry point
jQuery(function($){//$(document).ready
    "use strict";
    var $body = $('body');

    //Load modules
    require(['retina',
             'modules/translate3d-support',
             'modules/navigation',
             'modules/navigation-mobile',
             'modules/portfolio-listing',
             'modules/portfolio',
             'modules/responsive-media',
             'modules/comment-respond',
             'modules/widget-testimonials',
             'modules/testimonials',
             'modules/image-carousel',
             'modules/accordion',
             'modules/post-slider',
             'modules/post-gallery',
             'modules/horizontal-tab',
             'modules/tab',
             'modules/parallax-background',
             'modules/gmap',
             'modules/animation',
             'modules/progressbar'
            ],
    function(retina, translate3dSupport){

        if(translate3dSupport())
            $body.addClass('px-translate3d');

    });
});