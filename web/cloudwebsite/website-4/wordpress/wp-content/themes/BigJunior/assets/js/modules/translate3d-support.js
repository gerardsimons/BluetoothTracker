//Detect support for 3d translate

define([], function() {
    "use strict";

    //Quick hack
    function Has3DSupport()
    {
        var sTranslate3D = "translate3d(0px, 0px, 0px)",
            eTemp        = document.createElement("div");

        eTemp.style.cssText =
            "  -moz-transform:"    + sTranslate3D +
            "; -ms-transform:"     + sTranslate3D +
            "; -o-transform:"      + sTranslate3D +
            "; -webkit-transform:" + sTranslate3D +
            "; transform:"         + sTranslate3D;

        var rxTranslate = /translate3d\(0px, 0px, 0px\)/g,
            asSupport   = eTemp.style.cssText.match(rxTranslate);

        return (asSupport !== null && asSupport.length == 1);
    } // Has3DSupport


    return Has3DSupport;

});