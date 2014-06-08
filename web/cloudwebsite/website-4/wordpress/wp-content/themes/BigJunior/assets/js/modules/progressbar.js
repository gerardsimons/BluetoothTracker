//Progressbar animation

define([], function() {
    "use strict";

    var $ = jQuery,
        $itemList = $('.progressbar.animate');

    $itemList.each(function(){
        var $progress = $(this),
            $inner    = $progress.find('.progress-inner');

        $inner.css('left', -$inner.width());
    });
});