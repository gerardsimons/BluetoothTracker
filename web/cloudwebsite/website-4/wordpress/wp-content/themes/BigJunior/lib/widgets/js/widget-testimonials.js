
(function($){

    var groupsSelector = '.testimonial-group',
        remBtnSelector = '.testimonial-remove-btn',
        addBtnSelector = 'a.testimonial-add';

    function ArrangeFieldNames($groups, names){

        for(var i=0; i<$groups.length; i++)
        {
            $groups.eq(i).find('input[type="text"],textarea').each(function(j){
                var $field = $(this);
                $field.attr('name', names[j].replace('replace', i));
            });
        }

    }

    function Initialize($widgets)
    {
        $widgets.each(function(){
            var $widget = $(this),
                $groups = $widget.find(groupsSelector),
                $count  = $widget.find('input[name*="count"][type="hidden"]'),
                $addBtn = $widget.find(addBtnSelector),
                $remBtn = $groups.find(remBtnSelector),
                $widgetContent = $widget.find('.widget-content'),
                jsEnabledIndicator = '<input type="hidden" name="widget-js-enabled" />';

            $widgetContent.append(jsEnabledIndicator);

            //Scan for changes
            function HandleChanges()
            {
                $jsEnabled = $widgetContent.find('input[name="widget-js-enabled"]');

                if(!$jsEnabled.length)
                {
                    //re-initialize the handlers
                    Initialize($widget);
                    return;
                }

                setTimeout(HandleChanges, 500);
            }
            HandleChanges();

            //Find field names
            var names = $groups.eq(0).attr('data-names').split(',');

            //Set proper field names
            ArrangeFieldNames($groups, names);

            if($groups.length < 2) $remBtn.hide();

            //Remove button handler
            $remBtn.click(function(e){
                e.preventDefault();
                var $btn = $(this);

                //Remove the group
                $btn.parents().eq(1).remove();

                //Refresh
                $groups = $widget.find(groupsSelector);
                $remBtn = $groups.find(remBtnSelector);

                if($groups.length < 2) $remBtn.hide();

                //Order filed names
                ArrangeFieldNames($groups, names);
                //Set count
                $count.val($groups.length);
            });

            //Add button handler
            $addBtn.click(function(e){
                e.preventDefault();

                var $newGrp  = $groups.eq(0).clone(true);

                //Clear values
                $newGrp.find('input[type="text"]').val('');
                $newGrp.find('textarea').text('');

                $groups.filter(':last').after($newGrp);

                //Refresh
                $groups = $widget.find(groupsSelector);
                $groups.find(remBtnSelector).show();

                ArrangeFieldNames($groups, names);

                //Set count
                $count.val($groups.length);
            });


        });

    }

    function ScanWidgets()
    {
        var $holders = $('#widgets-right .widgets-holder-wrap');

        function Scan()
        {
            $testimonials = $holders.find('[id*="testimonials"].widget').not('.widget-js-enabled');

            if($testimonials.length)
            {
                Initialize($testimonials);
                $testimonials.addClass('widget-js-enabled');
            }

            setTimeout(Scan, 500);
        }

        Scan();
    }

    $(document).ready(function(){

        ScanWidgets();

    });
})(jQuery);