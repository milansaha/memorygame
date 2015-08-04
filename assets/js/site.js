(function ($) {
    var elements;
    var duration;

    var show= function(){
        if (elements)
            {
                $(elements).css('visibility','hidden');
                window.setTimeout(hide,duration);
            }
    }
    var hide=function(){
        if (elements)
            {
                $(elements).css('visibility','visible');
                window.setTimeout(show,duration);
            }
    }
    $.fn.blink = function (time) {
        elements=this;
        duration=time;
        hide();
    }
    $.fn.stopBlink = function () {
        elements=false;
    }
}(jQuery));
