//Parallax image shortcode handler



define(['modules/element-visible', 'modules/resize'], function(elementVisible, resize) {

    "use strict";



    var $ = jQuery,

        $parallaxContainers = $('.parallax'),

        userAgent = window.navigator.userAgent;



    //Parallax function doesn't work properly on IOS

    if(userAgent.match(/iPad/i) || userAgent.match(/iPhone/i))

    {

        $parallaxContainers.css({backgroundAttachment: 'scroll'});

        return;

    }



    if(!$parallaxContainers.length) return;



    function hasAttr($obj, attr)

    {

        var val = $obj.attr(attr);

        return typeof val !== 'undefined' && val !== false;

    }



    //Lazy load required plugins

    require(['jquery.parallax'], function(){



        $parallaxContainers.each(function(){

            var $parallax = $(this),

                xPos      = $parallax.attr('data-xpos'),

                speed     = $parallax.attr('data-speed');



            //Run parallax script

            $parallax.parallax(xPos, speed);



            //Check for title animation attr

            if(!hasAttr($parallax, 'data-titleanimation'))

                return;



            itemAnimation($parallax);

        });



    });



    //returns elements left position (for centering horizontally)

    function getElementLeft($el, $parent)

    {

        return ($parent.width() - $el.width()) * 0.5;

    }



    function getSumHeights($items)

    {

        var sumHeights = 0;



        $items.each(function(){

            sumHeights += $(this).outerHeight(true);

        });



        return sumHeights;

    }



    function computeFinalStyles($items, $container, vOffset)

    {

        //Compute item final positions

        $items.each(function(){

            var $item = $(this),

                l     = getElementLeft($item, $container);



            $item.data('css', {top: vOffset, left: l, opacity: .95});



            vOffset += $item.outerHeight(true);

        });

    }



    function computeStartPositions($items, $container, animation, vOffset, hShift, vShift)

    {

        //Compute item final positions

        $items.each(function(){

            var $item = $(this),

                l     = getElementLeft($item, $container);



            switch(animation)

            {

                case 'from-bottom':

                {

                    $item.css({top:vOffset + vShift, left: l});

                    break;

                }

                case 'from-top':

                {

                    $item.css({top:vOffset - vShift, left: l});

                    break;

                }

                case 'from-left':

                {

                    $item.css({top:vOffset, left: l - hShift});

                    break;

                }

                case 'from-right':

                {

                    $item.css({top:vOffset, left: l + hShift});

                    break;

                }

            }



            vOffset += $item.outerHeight(true);

        });

    }



    function prepareItems($items, $container, animation, animationTime)

    {

        var containerHeight    = $container.height(),

            containerWidth     = $container.width(),

            sumHeights         = getSumHeights($items),

            vOffset            = (containerHeight - sumHeights) * 0.5,

            vShift             = containerHeight * .1,

            hShift             = containerWidth * .1;



        //Disable transitions during initialization

        $items.addClass('notransition');



        //Compute all heights and set fixed item width

        //Set transition time

        $items.each(function(){

            var $item = $(this);



            $item.css({

                width: $item.width(),

                WebkitTransitionDuration : animationTime + 's',

                MozTransitionDuration    : animationTime + 's',

                MsTransitionDuration     : animationTime + 's',

                OTransitionDuration      : animationTime + 's',

                transitionDuration       : animationTime + 's'

            });

        });



        //Compute item start positions

        computeStartPositions($items, $container, animation, vOffset, hShift, vShift);



        //Compute item final properties

        computeFinalStyles($items, $container, vOffset);



        //Reverse the animation order

        if(animation == 'from-top')

        {

            $items = $($items.get().reverse());

        }



        //Restore css3 animation function

        $items.removeClass('notransition').css({visibility: 'visible'});



        return $items;

    }



    function itemAnimation($parallax)

    {

        var titleAnimation     = $parallax.attr('data-titleanimation'),

            titleAnimationTime = parseFloat($parallax.attr('data-titleanimationtime')),

            $items             = $parallax.find('.title,hr,.subtitle'),

            inView             = false;



        //Animate when element is in view

        elementVisible($parallax, function(){

            inView    = true;



            var $list = prepareItems($items, $parallax, titleAnimation, titleAnimationTime);



            //Animate

            $list.each(function(i){

                var $item = $(this);



                //Add Delay

                if(i)

                {

                    (function($item){

                        setTimeout(function(){ $item.css($item.data('css')); }, i*220);

                    })($item);

                }

                else

                {

                    $item.css($item.data('css'));

                }

            });

        });



        //Set item positions

        resize(function(){



            if(inView)

            {

                var sumHeights = getSumHeights($items),

                    vOffset    = ($parallax.height() - sumHeights) * 0.5;



                //Compute item final positions

                computeFinalStyles($items, $parallax, vOffset);



                $items.each(function(){

                    var $item = $(this);



                    $item.css($item.data('css'));

                });

            }



        }, 100);

    }



});

