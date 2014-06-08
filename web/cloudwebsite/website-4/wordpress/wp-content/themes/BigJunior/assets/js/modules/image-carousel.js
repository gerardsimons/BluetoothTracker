

define(['modules/image-load'], function(imagesLoaded) {
    "use strict";

    var $ = jQuery,
        $carousel = $('.image-carousel');

    if(!$carousel.length) return;

    //Lazy load required plugins
    require(['jquery.flexisel'], function(){


        $carousel.each(function(){
            var $list = $(this),
                $imgs = $list.find('img'),
                items = $list.attr('data-items');

            //Wait for all images to load
            imagesLoaded($imgs, function(){
                $list.flexisel({visibleItems: items});
            });

        });

    });

});
