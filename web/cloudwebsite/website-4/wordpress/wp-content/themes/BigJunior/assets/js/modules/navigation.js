//Top navigation Handler

define(['../superfish', 'modules/resize', 'modules/image-load'], function(superfish, resize, imageLoad) {
    "use strict";

    var $            = jQuery,
        $window      = $(window),
        scrlStart    = 30,
        headerMinH   = 50,
        $header      = $('header'),
        $logo        = $header.find('.logo img'),
        $container   = $header.find('> .container'),
        $headerNav   = $header.find('.navigation'),
        $navList     = $headerNav.find('> ul'),
        $topLinks    = $navList.find('> li > a'),
        headerHeight = $header.height(),
        htmlMargin   = parseInt($('html').css('marginTop')),
        $searchItem  = $navList.find('#menu-item-search'),
        $searchTemplate = $searchItem.find('.search-template'),
        dontScroll   = false,
        minimized    = false;

    //Run superfish plugin
    $navList.superfish({delay: 100});
    //Add a class to indicate parent menu
    $navList.find('.sub-menu .sub-menu').parent().addClass('menu-item-parent');

    //Adjust menu items height
    $header.css({width: '100%', top: htmlMargin, zIndex: 200});
    $('body > .layout').css({paddingTop: headerHeight + htmlMargin});


    function UpdateHeader(forceUpdate)
    {
        if(dontScroll) return;

        var scrlPos   = $window.scrollTop(),
            update    = false;

        if(typeof forceUpdate != 'undefined' && forceUpdate === true)
        {
            update = true;
        }

        if(scrlPos > scrlStart && (!minimized || update))
        {
            $container.css({height: headerMinH});
            $logo.css({maxHeight: headerMinH});
            $topLinks.css({height: headerMinH, lineHeight: headerMinH + 'px'});

            minimized = true;
        }
        else if(scrlPos < scrlStart && (minimized || update))
        {
            $container.css({height: headerHeight});
            $logo.css({maxHeight: headerHeight});
            $topLinks.css({height: headerHeight, lineHeight: headerHeight + 'px'});

            minimized = false;
        }

    }

    //Add scroll handler
    $window.scroll(UpdateHeader);

    //Check logo size
    imageLoad($logo, function(){

        //if logo is bigger in height, set the header height to logo height
        if($logo.height() > headerHeight)
        {
            headerHeight = $logo.height();
            UpdateHeader(true);
        }

    }, 5000);

    function CheckWindowSize()
    {
        var pos = 'fixed';

        if($window.width() < 980)
        {
            pos = 'absolute';
            dontScroll = true;
        }
        else
        {
            dontScroll = false;
        }

        $header.css({position:pos});
    }

    //Resize event
    resize(CheckWindowSize, 100);
    CheckWindowSize();

    //Search form handlers
    $searchItem.find('> a').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        $searchTemplate.toggleClass('visible');
    });

    $searchTemplate.click(function(e){
        e.stopPropagation();
    });

    $(document).click(function(){
        $searchTemplate.removeClass('visible');
    });

}
);