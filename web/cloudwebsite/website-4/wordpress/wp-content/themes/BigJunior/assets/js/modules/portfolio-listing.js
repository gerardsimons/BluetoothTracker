//Portfolio listing handler

define(['modules/loadCss', 'modules/element-query', 'modules/resize', 'modules/image-load'], function(loadCss, elementQuery, resize, imagesLoaded) {
    "use strict";

    var $      = jQuery,
        $lists = $('.portfolio-list');

    if(!$lists.length) return;

    var $images   = $lists.find('img');

    //Wait for all images to load
    imagesLoaded($images, onImagesLoaded);

    function onImagesLoaded()
    {
        //Fade-in portfolio images
        $images.each(function(i){
            var $img = $(this);
            setTimeout(function(){$img.addClass('start-animation');}, i*150);
        });

        //Magnific pop up
        require(['jquery.mfp.min'], function(){
            loadCss(theme_uri.css + '/magnific-popup.css');

            $lists.find('.item-view-image-icon').magnificPopup({
                type: 'image',
                closeBtnInside: false,
                removalDelay: 300,
                mainClass: 'portfolio-list-mfp'
            });
        });

        //Lazy load isotope plugin
        require(['jquery.isotope.min'], function(){
            //Load required css on the fly
            loadCss(theme_uri.css + '/isotope.css');

            $lists.each(function(){
                var $list      = $(this),
                    $isotope   = $list.find('.isotope'),
                    $filters   = $list.find('.filter a'),
                    layoutMode = $list.hasClass('.portfolio-style1') ? 'fitRows' : 'masonry';

                handleResize($list, $isotope);

                //Isotope
                $isotope.isotope({
                    // options
                    itemSelector: '.item',
                    layoutMode: layoutMode,
                    animationEngine: 'best-available'
                });


                //Handle filters
                $filters.click(function(e){
                    e.preventDefault();

                    var $link     = $(this),
                        selector  = $link.attr('data-filter');

                    $isotope.isotope({ filter: selector });
                    $filters.removeClass('current');
                    $link.addClass('current');
                });

            });//each list

        });//require isotope
    }

    function handleResize($list, $isotope)
    {
        elementQuery([{operator: 'max-width', value: 730}, {operator: 'max-width', value: 630}, {operator: 'max-width', value: 480}],
            $list);

        resize(function(){$isotope.isotope('reLayout');}, 100);
    }

});