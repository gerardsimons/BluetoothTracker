
define([], function() {
    "use strict";

    var $ = jQuery;
    return function($element)
    {
        //Adjust the item vertical position
        $element.each(function(){
            var $box  = $(this),
                $item = $box.parent(),
                top   = ($item.height() - $box.outerHeight()) * .5,
                left  = ($item.width() - $box.outerWidth()) * .5;

            $box.css({top: top, left: left});
        });
    }
});
