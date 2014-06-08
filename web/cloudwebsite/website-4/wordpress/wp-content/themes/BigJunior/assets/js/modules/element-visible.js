//Checks if the element is in viewable area and calls a callback if it gets into view once

define(['modules/resize'], function(resize) {
    "use strict";
    var $       = jQuery,
        $window = $(window);

    function elementVisible(el, callback)
    {
        var $el = el;

        if((el instanceof jQuery) == false)
            $el = $(el);

        $el.each(function(){
            var $element = $(this),
                trigger  = false;

            function update()
            {
                if(!trigger && isInView($element))
                {
                    trigger = true;
                    callback($element);
                }
            }

            //bind events
            resize(update, 100);
            $window.scroll(update);

            //Call update
            update();
        });

    }

    function isInView($el)
    {
        var docViewTop    = $window.scrollTop(),
            docViewBottom = docViewTop + $window.height(),
            elemTop       = $el.offset().top,
            elemBottom    = elemTop + $el.height();

        return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
    }

    return elementVisible;
});