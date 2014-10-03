
//Shortcode popup handler

(function($){
    $.px = { scpopup:{} };

    var utility = {
        hasAttr: function($obj, key){
            return (typeof $obj.attr(key) != 'undefined');
        },
        parseFlags: function(flags){
            if(typeof flags == 'string')
                return flags.split(',');

            return [];
        }
    };

    $.px.scpopup.load = function(){
        //Bind events here
        //this==$wrap
        var $wrap = this,
            flags = utility.parseFlags($wrap.find('.px-sc-flags').html());

        $wrap.find('.px-modal-save').click(SaveHandler);

        //Add preview button is preview flag is set
        if(flags.indexOf('preview') >= 0)
        {
            var $previewBtn = $('<a href="#" class="px-modal-preview button button-large">Preview</a>');
            $wrap.find('.px-modal-buttons').prepend($previewBtn);
            $previewBtn.click(PreviewWindow);
        }

        //Add dropdown handler
        DropDown.call($wrap);
        //Multiselect
        MultiSelect.call($wrap);
        //Icons
        Icons.call($wrap);
    };

    function PreviewWindow(e)
    {
        e.preventDefault();

        var $wrap = $(this).parents('.px-modal-wrap'),
            sc    = GetShortcode.call($wrap),
            prevUrl = $wrap.find('.px-sc-preview').html(),
            $form    = $(document.createElement('form')),
            $scInput = $(document.createElement('input'));

        $form.attr({ "action": prevUrl, "method": "post" });
        $scInput.attr({'name': 'sc', 'value' : sc, 'type':'hidden'});
        $form.append($scInput);

        //Create new modal window
        $.pxmodal({ title: 'Preview', load: function(){
            var $prevWindow = this,
                $frame = $('<iframe class="px-preview-frame" />').load(function(){
                    $frame.contents().find('body').append($form);
                });

            $prevWindow.css({margin: 30});
            $prevWindow.find('.px-modal-content').append($frame);
            setTimeout(function(){$form.submit();}, 100);
            //Set the modal button
            $prevWindow.find('.px-modal-save').click(function(e){ e.preventDefault(); $.pxmodal('close'); }).text('Ok');
        } });

    }

    function SaveHandler(e)
    {
        e.preventDefault();

        var $wrap = $(this).parents('.px-modal-wrap'),
            sc    = GetShortcode.call($wrap);

        if (window.tinyMCE) {
		
			if(window.tinyMCE.execInstanceCommand)
				window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, sc);
			else if(window.tinyMCE.execCommand)
				parent.tinyMCE.execCommand('mceInsertContent', false, sc);
			
            $.pxmodal('close');
        }

    }

    //Generates shortcode from inputs
    function GetShortcode() {
        var $content = this.find('.px-modal-content'),
            $inputs  = $content.find('input[type="text"],input[type="hidden"],select,textarea'),
            sc       = $content.find('.px-sc-template').html(),
            attrs    = [];

        $inputs.each(function () {

            var nodeName = this.nodeName.toLowerCase(),
                $me  = $(this),
                name = $me.attr('name'),
                val  = $me.val(),
                flags    = utility.parseFlags($me.attr('data-flags')),
                isAttr   = flags.indexOf('attribute') >= 0;

            //Multiselect input
            if(nodeName == 'select' && utility.hasAttr($me, 'multiple'))
            {
                var values = [];

                $me.find(':selected').each(function(){
                    values.push($(this).val());
                });

                val = values.join(',');
            }
            //If this is a select input then find if the selected value is default one
            else if(nodeName == 'select' && isAttr)
            {
                var $option     = $me.find(':selected'),
                    optFlags    = utility.parseFlags($option.attr('data-flags'));

                if(optFlags.indexOf('default') >= 0)
                {
                    //No need to add default attribute
                    return;
                }
            }

            //Remove if empty flag
            if(flags.indexOf('empty-remove') >= 0 && !val.length)
                return;

            //Default attribute handler
            if (isAttr) {
                attrs.push(name + "='" + val + "'");
                return;//Return if its attribute input
            }

            var regex = new RegExp('{' + name + '}', 'g');
            sc = sc.replace(regex, val);
        });//Each input

        //Replace attributes
        var regex = new RegExp('{attr}', 'g');
        sc = sc.replace(regex, attrs.join(' '));

        return sc;
    }


    //Handles dropdowns
    function DropDown() {
        this.find('.px-input-select').each(function () {
            var $this    = $(this),
                $overlay = $this.find('div'),
                $select  = $this.find('select');

            $select.change(Change);

            function Change()
            {
                $overlay.html($select.find('option:selected').text());
            }

            Change();
        });
    }

    function MultiSelect() {
        if (!$.fn.chosen)
            return;

        this.find('.chosen').chosen({width: "100%"});
    }

    function Icons()
    {
        this.find('.px-input-icon').each(function(){
            var $list  = $(this),
                $input = $list.find('input'),
                $icons = $list.find('.px-icon'),
                $selected = $();

            $icons.click(function(){
                var $icon = $(this);

                if($icon.hasClass('selceted'))
                    return;

                $selected.removeClass('selected');
                $icon.addClass('selected');
                $selected = $icon;

                $input.val($icon.attr('data-name'));
            });
        });
    }

})(jQuery);

//Utility functions

//IE Fix
if (!Array.prototype.indexOf) {
    Array.prototype.indexOf = function (needle) {
        for (var i = 0; i < this.length; i++) {
            if (this[i] === needle) {
                return i;
            }
        }
        return -1;
    };
}