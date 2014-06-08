/**
 * Element query module
 * Author    : Mohsen Heydari
 * Version   : 1.0
 * Web site  : http://devmash.net
 * Contact   : mohsenheydari@live.com
 */

define(['modules/resize'], function(resize) {
        "use strict";
        var $ = jQuery;

        function hasAttr($obj, attr)
        {
            var val = $obj.attr(attr);
            return typeof val !== 'undefined' && val !== false;
        }

        function widthOperation($object, query, op)
        {
            var match    = null,
                objW     = $object.outerWidth(),
                dataAttr = op == 'min' ? 'data-minwidth' : 'data-maxwidth' ;


            for(var i=0;i<query.length;i++)
            {
                var param = query[i];

                if('min' == op)
                {
                    if(param.value <= objW)
                    {
                        //Last match will be used
                        match = param;
                    }
                }
                else//Max
                {
                    if(param.value >= objW)
                    {
                        //Last match will be used
                        match = param;
                    }
                }
            }

            if(match)
            {
                if(hasAttr($object, dataAttr))
                {
                    //Check if already set
                    var val = parseInt($object.attr(dataAttr));

                    //No need to set it again
                    if(val == match.value) return;
                }

                $object.attr(dataAttr, match.value);
                match.callback();
            }
            else//No match found
            {
                $object.removeAttr(dataAttr);
            }
        }

        function elementQuery(query, $object)
        {
            //Sanity check
            if(false == (query instanceof Array))
                return;

            //Store resulting operations in an array
            var minWOp     = [],
                maxWOp     = [];

            for(var i=0; i<query.length; i++)
            {
                var q = query[i];

                //q should be object
                if(false == (q instanceof Object))
                    continue;

                //Check for operator and value
                if(!q.hasOwnProperty('operator') || !q.hasOwnProperty('value'))
                    continue;

                var callback = q.hasOwnProperty('callback') ? q.callback : function(){};

                switch(q.operator)
                {
                    case 'min-width':
                    {

                        (function(val, callback)
                        {
                            minWOp.push({value:val, callback:callback});
                        }(q.value, callback));

                        break;
                    }
                    case 'max-width':
                    {
                        (function(val, callback)
                        {
                            maxWOp.push({value:val, callback:callback});
                        }(q.value, callback));
                        break;
                    }
                }
            }

            function resizeHandler()
            {
                //Run
                widthOperation($object, minWOp, 'min');
                widthOperation($object, maxWOp, 'max');
            }

            //Run once
            resizeHandler();

            //Add window resize event
            resize(resizeHandler, 100);
        }

        return elementQuery;
    }
);