//Testimonial widget handler

define(['modules/resize'], function(resize) {
    "use strict";

    var $ = jQuery,
        $testimonials = $('.widget_bj_testimonials');

    if(!$testimonials.length) return;

    //Init lists
    $testimonials.each(function(){
        var $testimonial = $(this),
            $list  = $testimonial.find('ul'),
            $items = $list.find('> li'),
            $li0   = $items.eq(0),
            $ctrls = $testimonial.find('.testimonials-controls'),
            $next  = $ctrls.find('.next'),
            $prev  = $ctrls.find('.previous');

        if($items.length < 2){
            $ctrls.hide();
            return;
        }

        //Init items
        $items.each(function(i){
            var $item   = $(this),
                w       = $list.width();

            $item.css({ position: 'absolute', width: w, top: 0, left: i * w, display:'block' });
        });

        $li0.addClass('current');
        $list.css({height: $li0.height()});


        $next.click(function(e){
            e.preventDefault();

            var curIndx = $items.filter('.current').index(),
                nextIndx = curIndx + 1;

            if (nextIndx >= $items.length)
                nextIndx = 0;

            GoTo(nextIndx, $list, $items);
        });

        $prev.click(function(e){
            e.preventDefault();

            var curIndx = $items.filter('.current').index(),
                nextIndx = curIndx - 1;

            if (nextIndx < 0)
                nextIndx = $items.length - 1;

            GoTo(nextIndx, $list, $items);
        });

        HandleResize($list, $items);
    });//$lists.each


    function GoTo(i, $list, $items) {
        var width     = $list.width(),
            $nextItem = $items.eq(i),
            $nextItemName = $nextItem.find('.name');

        $items.removeClass('current');
        $nextItem.addClass('current');

        //Animate list height
        $list.stop().animate({ height: $nextItem.height() }, { speed: 300 });

        $nextItemName.css({visibility:'hidden', opacity:0});

        $items.each(function (j) {
            var $item = $(this);

            $item.stop().animate({ left: (j - i) * width }, { speed: 300, complete: function(){ $nextItemName.css({visibility:'visible', opacity:1}); } });
        });
    }

    function HandleResize($list, $items)
    {
        resize(function(){
            var $curItem = $items.filter('.current'),
                curIndx  = $curItem.index();

            $items.each(function (i) {
                var $item = $(this),
                    w     = $list.width();

                $item.css({ width: w, left: (i - curIndx) * w });
            });

            //Change parent height
            $list.stop().animate({ height: $curItem.height() }, { speed: 300 });

        }, 100);

    }

});