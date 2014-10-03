//Horizontal tab shortcode handler

define([], function() {
    "use strict";

    var $ = jQuery,
        $tabContainers = $('.tabs');

    if(!$tabContainers.length) return;

    $tabContainers.each(function(){
        var $container = $(this),
            $titles    = $container.find('.head li'),
            $contents  = $container.find('.tab-content');

        //Hide all contents except the first one
        $contents.not(':first-child').hide();

        //Mark the first tab as current one
        $titles.eq(0).addClass('current');

        $titles.click(function(e){
            var $title = $(this),
                index  = $title.index(),
                $curTitle = $titles.filter('.current');

            if($title.hasClass('current'))
                return;

            $contents.eq($curTitle.index()).stop().fadeOut({complete:function(){
                $curTitle.removeClass('current');
                $title.addClass('current');
                $contents.eq(index).fadeIn();
            }});

        });
    });
});
