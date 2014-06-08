//Generic animation handler

define(['modules/element-visible'], function(elementVisible) {
    "use strict";

    var $ = jQuery,
        $itemList = $('.animate'),
        i      = 0,
        iTimer = 0;

    elementVisible($itemList, function($el){
        if(i)
        {
            clearTimeout(iTimer);

            setTimeout(function(){
                $el.addClass('start-animation');
            }, i*150);

            //Timer to reset i
            iTimer = setTimeout(function(){
                i = 0;
            }, 200);
        }
        else
        {
            $el.addClass('start-animation');
        }

        i++;
    });
});