//Responsive video iframe handler

define(['modules/resize'], function(resize) {
    "use strict";

    var $ = jQuery,
        $videoFrame = $('iframe[src^="http://www.youtube.com"],iframe[src^="http://player.vimeo.com"]');

    $videoFrame.each(function(){
        $(this).data('aspectRatio', this.height / this.width)
            .removeAttr('height')
            .removeAttr('width');
    });

    function Update()
    {
        $videoFrame.each(function(){
            var $vid = $(this),
                $parent = $vid.parent(),
                width = $parent.width();

            $vid.css({width: width, height: width * $vid.data('aspectRatio')});
        });
    }

    resize(Update, 100);
    Update();
});