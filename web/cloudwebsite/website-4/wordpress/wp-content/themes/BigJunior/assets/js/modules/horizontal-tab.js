//Horizontal tab shortcode handler

define(['modules/image-load', 'modules/element-query', 'modules/resize'], function(imagesLoaded, elementQuery, resize) {
    "use strict";

    var $              = jQuery,
        $tabContainers = $('.horizontal-tab');

    if(!$tabContainers.length) return;

    function SetPointerTop($pointer, $title)
    {
        var pHeight = $pointer.height(),
            tHeight = $title.height(),
            localVCenter = (tHeight - pHeight) * 0.5,
            tPos    = $title.position(),
            top     = tPos.top + localVCenter;

        $pointer.css({top: top});
    }

    function SetPointerLeft($pointer, $title)
    {
        var tPos    = $title.position(),
            left    = tPos.left;

        $pointer.css({left: left});
    }

    function SetPointerPosition($pointer, $title, $container, defaultPointerLeft)
    {
        //Set pointer position
        SetPointerTop($pointer, $title);

        if($container.width() < 768)
            SetPointerLeft($pointer, $title);
        else
            $pointer.css({left: defaultPointerLeft});
    }

    function Initialize()
    {
        $tabContainers.each(function(){
            var $container   = $(this),
                $titles      = $container.find('.titles-container .titles li'),
                $contents    = $container.find('.tabs-container > li'),
                $pointer     = $container.find('.titles-container .pointer'),
                pointerLeft  = $pointer.css('left');

            //Hide all contents except the first one
            $contents.not(':first-child').hide();

            //Mark the first tab as current one
            $contents.eq(0).addClass('current');

            elementQuery([{operator: 'max-width', value: 767}],
                         $container);

            //Set pointer position
            SetPointerPosition($pointer, $titles.eq(0), $container, pointerLeft);

            $titles.click(function(e){
                var $title = $(this),
                    index  = $title.index(),
                    $curContent = $contents.filter('.current');

                if(index == $curContent.index())
                    return;

                SetPointerPosition($pointer, $title, $container, pointerLeft);

                $curContent.stop().fadeOut({complete:function(){
                    $curContent.removeClass('current');
                    $contents.eq(index).addClass('current').fadeIn();
                }});

            });//

            resize(function(){
                SetPointerPosition($pointer, $titles.eq($contents.filter('.current').index()), $container, pointerLeft);
            }, 100);
        });
    }

    //Wait for images to load on page
    imagesLoaded($('img'), Initialize, 5000);
});
