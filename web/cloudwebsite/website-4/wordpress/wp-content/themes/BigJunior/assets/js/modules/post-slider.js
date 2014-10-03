//Post slider shortcode handler

define(['modules/resize', 'modules/element-query'], function(resize, elementQuery) {
    "use strict";

    var $ = jQuery,
        $sliders = $('.post-slider');

    if(!$sliders.length) return;

    require(['jquery.iosslider.min'], function(){

        $sliders.each(function(){
            var $slider = $(this),
                $next   = $slider.find('.nav-next'),
                $prev   = $slider.find('.nav-prev'),
                $container = $slider.find('.slider-wrap'),
                setup   = true;

            function update()
            {
                if(setup) return;
                $container.iosSlider('update');
            }

            elementQuery([
            {operator: 'max-width', value: 724},
            {operator: 'max-width', value: 480}
            ],
            $container);

            resize(update, 100);

            $container.iosSlider({
                desktopClickDrag: true,
                snapToChildren: true,
                scrollbar: true,
                scrollbarLocation: 'bottom',
                scrollbarMargin: '0',
                scrollbarHeight: '2px',
                navNextSelector: $next,
                navPrevSelector: $prev
            });

            $next.click(function(e){e.preventDefault();});
            $prev.click(function(e){e.preventDefault();});

            setup = false;
        });

    });

});
