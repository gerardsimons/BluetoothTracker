//Testimonial widget handler

define(['modules/element-query'], function(elementQuery) {
    "use strict";

    var $ = jQuery,
        $testimonial = $('.testimonial');

    if(!$testimonial.length) return;

    function responsiveHandler()
    {
        $testimonial.each(function(){
            var $item = $(this);
            elementQuery([{operator: 'max-width', value: 580}],
                           $item);
        });
    }

    responsiveHandler();

    function testimonialGroupHandler()
    {
        var $testimonials = $('.testimonials');

        //Init lists
        $testimonials.each(function()
        {
            var $testimonial = $(this),
                $items = $testimonial.find('.testimonial'),
                $ctrls = $testimonial.find('.testimonials-controls');

            if($items.length < 2)
            {
                $ctrls.hide();
                return;
            }

            //Init items
            $items.not(':first-child').hide();
            $items.eq(0).addClass('current');

            $ctrls.find('.next').click(function(e)
            {
                e.preventDefault();

                var curIndx  = $items.filter('.current').index(),
                    nextIndx = curIndx + 1;

                if (nextIndx >= $items.length)
                    nextIndx = 0;

                GoTo(nextIndx, $items);
            });

            $ctrls.find('.previous').click(function(e)
            {
                e.preventDefault();

                var curIndx = $items.filter('.current').index(),
                    nextIndx = curIndx - 1;

                if (nextIndx < 0)
                    nextIndx = $items.length - 1;

                GoTo(nextIndx, $items);
            });

        });//$lists.each
    }

    testimonialGroupHandler();

    function GoTo(i, $items) {
        var $curItem  = $items.filter('.current'),
            $nextItem = $items.eq(i);

        if($curItem.is(':animated') || $nextItem.is(':animated'))
            return;

        $curItem.fadeOut({complete: function(){
            $nextItem.fadeIn({complete: function(){
                $items.removeClass('current');
                $nextItem.addClass('current');
            }});
        }});
    }

});