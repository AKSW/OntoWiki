(function ($) {
    $.fn.cycle = function (i) {
        var interval = i || 15000;
        var number   = this.length;
        var current  = Math.floor(Math.random() * (number - 1));
        var maxHeight = 0;
        var results  = this;
        
        results.each(function (index) {
            if ($(this).height() > maxHeight) {
                maxHeight = $(this).height();
            }

            if (index !== current) {
                $(this).hide();
            }
        });
        
        results.height(maxHeight);
        
        window.setInterval(function () {
            var next = (current + 1) % number;
            results.eq(current).fadeOut(function () {
                results.eq(next).fadeIn(function () {
                    current = next;
                });
            });
        }, interval);
        
        return this;
    }
})(jQuery);