(function($){
var passwordStrength = new function() {
    this.getStrength = function(pw) {
        var pwlength=(pw.length);
        if(pwlength>5)pwlength=5;
        var numnumeric=pw.replace(/[0-9]/g,"");
        var numeric=(pw.length-numnumeric.length);
        if(numeric>3)numeric=3;
        var symbols=pw.replace(/\W/g,"");
        var numsymbols=(pw.length-symbols.length);
        if(numsymbols>3)numsymbols=3;
        var numupper=pw.replace(/[A-Z]/g,"");
        var upper=(pw.length-numupper.length);
        if(upper>3)upper=3;
        var pwstrength=((pwlength*10)-20)+(numeric*10)+(numsymbols*15)+(upper*10);
        if(pwstrength<0){pwstrength=0}
        if(pwstrength>100){pwstrength=100}
        return pwstrength;
    }

    this.getStrengthLevel = function(val) {
        return this.getStrength(val);
    }
}

$.fn.password_strength = function(options)
{
    var settings = $.extend({
        'container' : null,
    }, options);

    return this.each(function(){
        if (settings.container) {
            var container = $(settings.container);
        } else {
            var container = $('<span/>').attr('class', 'password_strength');
            $(this).after(container);
        }

        $(this).focus(function() {
            container.slideDown();
        });

        $(this).keyup(function() {
            var val = $(this).val();
            if (val.length > 0) {
                var level = passwordStrength.getStrengthLevel(val);
            } else {
                var level = 0
            }
            container.children("div").css("background-position", level+"% 50%");
        });
    });
};
})(jQuery);