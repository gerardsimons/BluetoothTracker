(function ($) {

    function ImageFields()
    {
        var $imageSec    = $('.section-image'),
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

    function MediaType()
    {
        var $container = $('.px-main'),
            $mediaType = $container.find('select[name="media"]'),
            $sec       = $container.find('.section-image,.section-video');

        $mediaType.change(function(){
            var $selected = $mediaType.find('option:selected'),
                val = $selected.val(),
                $selected = $container.find('.section-'+val);

            $sec.not($selected).slideUp('fast').next('hr').hide();;
            $selected.slideDown('fast').next('hr').show();

        }).change();

    }

    $(document).ready(function () {
        ImageFields();
        MediaType();
    });

})(jQuery);