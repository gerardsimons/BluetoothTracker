(function ($) {

    function ImageFields()
    {
        var $imageSec    = $('.section-gallery'),
            $fields      = $imageSec.find('.upload-field'),
            $dupBtn      = $('<a class="duplicate-button" href="#">Add Image</a>'),
            $remBtn      = $('<a class="remove-button" href="#">Remove</a>');

        //Click handler for remove button
        $remBtn.click(function(e){
            e.preventDefault();

            var $this = $(this);

            $this.parent().remove();

            $fields = $imageSec.find('.upload-field');

            if($fields.length < 2)
            //Remove the button
                $fields.find('.remove-button').remove();
        });


        //Add remove button if there is more than one image field
        if($fields.length > 1)
            $fields.append($remBtn.clone(true));

        //Add duplicate button after last upload field
        $fields.filter(':last').after($dupBtn);

        $dupBtn.click(function(e){
            e.preventDefault();

            //Don't try to reuse $fields var above ;)
            $fields        = $imageSec.find('.upload-field');
            var $lastField = $fields.filter(':last'),
                $clone     = $lastField.clone(true);

            //Clear the value (if any)
            $clone.find('input[type="text"]').val('');

            $lastField.after($clone);

            //Refresh
            $fields        = $imageSec.find('.upload-field');
            //Add 'remove' button to all fields
            //Rest of 'remove' buttons will get cloned
            if($fields.length == 2)
                $fields.append($remBtn.clone(true));
        });
    }

    function PostFormats()
    {
        var $formats  = $('input[name="post_format"]'),
            $metaBox  = $('.px-main'),
            $sections = $metaBox.find('.section');

        function changeHandler()
        {
            var selected = $formats.filter(':checked').val(),
                $sec     = $metaBox.find('.section-'+ selected);

            $sections.not($sec).slideUp('fast').next('hr').hide();
            $sec.slideDown('fast').next('hr').show();
        }

        $formats.change(changeHandler);
        changeHandler();
    }

    $(document).ready(function () {
        ImageFields();
        PostFormats();
    });

})(jQuery);