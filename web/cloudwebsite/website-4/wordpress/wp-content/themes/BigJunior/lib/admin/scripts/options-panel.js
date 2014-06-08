(function($){

    function ThemeStyles()
    {
        var $style = $('select[name="style-preset-color"]');

        $style.change(OnStyleChange);

        function OnStyleChange()
        {
            var $selected = $style.find(':selected'),
                cAttr     = $selected.attr('data-colors');

            if(cAttr == undefined)
                return;

            var colors    = JSON.parse(cAttr);

            for (var key in colors) {
                if (!colors.hasOwnProperty(key) || undefined == key )
                    continue;

                var color   = colors[key],
                    $input  = $('input[name="'+key+'"]'),
                    $picker = $input.parent().find('.color-selector'),
                    $pickerBg = $picker.find('div');

                $picker.ColorPickerSetColor(color);
                $pickerBg.css('backgroundColor', color);
                $input.val(color);
            }

        }

        //OnStyleChange();
    }

    jQuery(function(){
        ThemeStyles();
    });


})(jQuery);