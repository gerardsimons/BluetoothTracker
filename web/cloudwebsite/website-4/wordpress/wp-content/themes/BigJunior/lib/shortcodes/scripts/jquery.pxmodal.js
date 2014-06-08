/*
 * Plugin Name: PX Modal
 * Description: Custom modal window plugin
 * Version: 1.0
 * Author: Mohsen Heydari
 * Author URI: http://devmash.net
 */

(function($){


    $.pxmodal = function(settings, params){

        if($body == null) $body   = $('body');
        if(zIndex == null) zIndex = GetTopZIndex('*');

        //Check if settings param is object
        if(typeof settings == 'object'){
            var options = $.extend({}, defaults, settings);

            return Initialize(options);
        }
        else{
            if(settings == 'close') {
                Close(instances[instances.length-1]);
            }
        }

    };

    function GetTopZIndex(selector)
    {
        var max = 0;

        $(selector).each(function(){
            var $this = $(this);

            if($this.css('position') == 'static') return;

            var cur = parseInt($this.css('z-index'));

            if(isNaN(cur)) return;
            max = cur > max ? cur : max;
        });

        return max;
    }

    function Initialize(options)
    {
        var $wrap     = $(modalTemplate),
            $backdrop = $('<div class="px-modal-backdrop"></div>');

        //Set z-indexes
        zIndex++;
        $backdrop.css('z-index', zIndex);
        zIndex++;
        $wrap.css('z-index', zIndex);

        //Set the title
        $wrap.find('.px-modal-title').text(options.title);

        if(!$body.hasClass('px-modal-open')) $body.addClass('px-modal-open');

        //Append the elements to the body
        $body.append($backdrop).append($wrap);

        //Add the elements to instances
        var instance = {wrap:$wrap, backdrop:$backdrop, options: options};
        instances.push(instance);

        //Set handlers
        $backdrop.click(function(){
            //Call the close callback
            options.close.call($wrap, {target:this});
            Close(instance);
        });

        $wrap.find('.px-modal-close').click(function(e){
            e.preventDefault();

            //Call the close callback
            var close = options.close.call($wrap, {target:this});

            if(typeof close != 'undefined' && !close)
                return;

            Close(instance);
        });

        //Load content
        LoadContent(instance);

        return instances.length;
    }

    function LoadContent(instance)
    {
        if(!instance.options.url.length)
        {
            //call the load method immediately
            instance.options.load.call(instance.wrap);
            return;
        }

        $.get(instance.options.url, function(data){
            instance.wrap.find('.px-modal-content').append(data);
            instance.options.load.call(instance.wrap);
        });
    }

    function Close(instance)
    {
        instance.backdrop.remove();
        instance.wrap.remove();
        instances.pop();

        zIndex-=2;

        if(!instances.length) $body.removeClass('px-modal-open');
    }

    var defaults = {
            title:'',
            url: '',
            close: function(){ return true; },
            load: function(){}
        },
        $body     = null,
        zIndex    = null,
        instances = [],
        modalTemplate =
        '<div class="px-modal-wrap">'+
            '<div class="px-modal-header">'+
                '<h4 class="px-modal-title"></h4>'+
                '<a class="px-modal-close" href="#">'+
                    '<span class="px-modal-icon"></span>'+
                '</a>'+
            '</div>'+//End Header
            '<div class="px-modal-content">'+
            '</div>'+
            '<div class="px-modal-footer">'+
                '<div class="px-modal-buttons">'+
                    '<a href="#" class="px-modal-save button button-primary button-large">Save</a>'+
                '</div>'+
            '</div>'+
        '</div>';

})(jQuery);