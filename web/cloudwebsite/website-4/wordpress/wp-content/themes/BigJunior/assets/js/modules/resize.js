//Smart window resize handler

define([], function() {
    "use strict";

    function smartResize(handler, delay)
    {
        var resizeTimer = 0;
        return jQuery(window).resize(function(){
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(handler, delay);
        });
    }

    return smartResize;
});