//Accordion/Toggle shortcode handler

define([], function() {
    "use strict";

    var $ = jQuery,
        $accordion = $('.accordion,.toggle');

    if(!$accordion.length) return;

    function ToggleTab($tab)
    {
        var $icon = $tab.find('.tab-button span'),
            $body = $tab.find('.body');

        if($tab.hasClass('closed'))
        {
            $body.slideDown(function(){$icon.attr('class', 'icon-minus');});
            $tab.removeClass('closed');
        }
        else
        {
            $body.slideUp(function(){$icon.attr('class', 'icon-plus');});
            $tab.addClass('closed');
        }
    }

    $accordion.each(function(){
        var $grp = $(this),
            $tabs= $grp.find('.tab'),
            $header=$tabs.find('.header'),
            isToggle=$grp.hasClass('toggle');

        //Close all tabs
        var keptOneOpen = false, //For accordions only
            tempList = [];
        $tabs.each(function(){
            var $tab = $(this);

            if($tab.hasClass('keep-open'))
            {
                if(isToggle)
                {
                    return;
                }
                else if(!keptOneOpen)
                {
                    keptOneOpen = true;
                    return;
                }
            }

            tempList.push($tab);
        });

        //Accordion: if none was opened open the first one
        if(!isToggle && !keptOneOpen && tempList.length)
        {
            tempList.shift();
            keptOneOpen = true;
        }

        for(var i=0; i<tempList.length; i++)
            ToggleTab(tempList[i]);

        $header.click(function(){
            var $head = $(this),
                $tab  = $head.parent();

            if(!isToggle)
            {
                //Close all other tabs
                $tabs.not($tab).each(function(){
                    if(!$(this).hasClass('closed'))
                        ToggleTab($(this));
                });
            }

            ToggleTab($tab);
        });

    });
});
