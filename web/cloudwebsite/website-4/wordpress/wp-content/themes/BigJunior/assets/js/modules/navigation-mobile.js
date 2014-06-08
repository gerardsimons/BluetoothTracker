//Mobiel navigation Handler

define(['modules/resize'], function(resize) {
        "use strict";

        var $             = jQuery,
            $body         = $('body'),
            $doc          = $(document),
            $layout       = $('body > .layout'),
            $navContainer = $('.navigation-mobile'),
            $closeBtn     = $navContainer.find('.navigation-close'),
            $navBtn       = $('.navigation-button'),
            dontResize    = false,
            isTouchDevice = 'ontouchstart' in window || 'onmsgesturechange' in window;// second test works on ie10 (surface)

        //Set overflow-y on mobile devices
        if(isTouchDevice)
            $navContainer.css({overflowY: 'scroll'});

        $navBtn.click(function(e){
            e.preventDefault();
            e.stopPropagation();

            if(!$body.hasClass('pushed-left'))
            {
                $navContainer.css({display:'block', height: $layout.outerHeight()});
                $body.toggleClass('pushed-left');
            }
            else
                CloseMenu();
        });

        function CloseMenu()
        {
            if($body.hasClass('pushed-left'))
            {
                $body.removeClass('pushed-left');
                setTimeout(function(){
                    $navContainer.css({display:'none'});
                }, 330);
            }
        }

        //Prevent resize event on IOS webkit browsers
        $doc.on('touchstart', function(e){
            var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0],
                $target = $(touch.target);

            if($target.is($navContainer) || $target.parents('.navigation-mobile').length)
                dontResize = true;

        }).on('touchend', function(){
            setTimeout(function(){dontResize = false;}, 1000);
        });


        $closeBtn.click(CloseMenu);

        $navContainer.click(function(e){
            e.stopPropagation();
        });

        $doc.click(function(e){
            CloseMenu();
        });

        //Window resize
        resize(function(){
            if(dontResize) return;
            CloseMenu();
        }, 100);
    }
);