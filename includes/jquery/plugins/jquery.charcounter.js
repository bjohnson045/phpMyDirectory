(function($) {
    $.fn.charCounter = function (max, settings) {
        max = max || 100;
        settings = $.extend({
            container: "counter",
            classname: "counter",
            container_template: "<span></span>",
            format: "%1 / "+max,
            pulse: true,
            delay: 100
        }, settings);
        var p, timeout;

        function count(el, container) {
            el = $(el);
            if (el.val().length > max) {
                el.val(el.val().substring(0, max));
                if (settings.pulse && !p) {
                    pulse(container, true);
                }
            }
            if (settings.delay > 0) {
                if (timeout) {
                    window.clearTimeout(timeout);
                }
                timeout = window.setTimeout(function () {
                    container.html(settings.format.replace(/%1/, (el.val().length)));
                }, settings.delay);
            } else {
                container.html(settings.format.replace(/%1/, (el.val().length)));
            }
        }

        function pulse(el, again) {
            if (p) {
                window.clearTimeout(p);
                p = null;
            }
            el.animate({ opacity: 0.1 }, 100, function () {
                $(this).animate({ opacity: 1.0 }, 100);
            });
            if (again) {
                p = window.setTimeout(function () { pulse(el) }, 200);
            }
        }

        return this.each(function () {
            var container;
            if($(this).next("#" + settings.classname).length) {
                container = $(this+" + ."+settings.container);
            } else if($(this).next("." + settings.classname).length) {
                container = $(this).next("." + settings.classname);
            } else {
                container = $(settings.container_template)
                                .insertAfter(this)
                                .addClass(settings.classname);
            }
            $(this)
                .off(".charCounter")
                .on("input.charCounter", function() { count(this, container); })
                .on("keydown.charCounter", function () { count(this, container); })
                .on("keypress.charCounter", function () { count(this, container); })
                .on("keyup.charCounter", function () { count(this, container); })
                .on("focus.charCounter", function () { count(this, container); })
                .on("mouseover.charCounter", function () { count(this, container); })
                .on("mouseout.charCounter", function () { count(this, container); })
                .on("paste.charCounter", function () {
                    var me = this;
                    setTimeout(function () { count(me, container); }, 10);
                });
            count(this, container);
        });
    };
})(jQuery);
