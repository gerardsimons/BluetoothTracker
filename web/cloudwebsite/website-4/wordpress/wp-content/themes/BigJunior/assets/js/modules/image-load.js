//Simple check for loaded images

define([], function() {
    "use strict";
    var $ = jQuery;

    function ImagesLoaded($images, callback, timeout)
    {
        var loadedCnt = 0,
            timedOut  = false;

        //Wait for all images to load
        $images.one('load', function(){
            loadedCnt++;

            if(loadedCnt == $images.length && !timedOut)
            {
                callback();
            }

        }).each(function() {
            if(this.complete) $(this).load();
        });

        //Call the callback immediately
        if(0 == $images.length)
        {
            callback();
            return;
        }

        //If there is a defined timeout
        if(typeof timeout != 'undefined')
        {
            setTimeout(function(){
                if(loadedCnt < $images.length)
                {
                    timedOut = true;
                    callback();
                }
            }, timeout);
        }

    }

    return ImagesLoaded;
});