(function ($) {

    function PageTemplateSections()
    {
        var $templates  = $('select#page_template'),
            $metaBox    = $('.px-main'),
            $sidebarSec = $metaBox.find('.section-sidebar');

        function changeHandler()
        {
            var selected = $templates.find(':selected').val();

            if('full-width.php' == selected)
            {
                $sidebarSec.slideUp('fast').next('hr').hide();
            }
            else{
                $sidebarSec.slideDown('fast').next('hr').show();
            }
        }

        $templates.change(changeHandler);
        changeHandler();
    }

    $(document).ready(function () {
        PageTemplateSections();
    });

})(jQuery);