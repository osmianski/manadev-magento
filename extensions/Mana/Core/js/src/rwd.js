Mana.require(['jquery'], function($) {
    var bp = {
        xsmall: 479,
        small: 599,
        medium: 770,
        large: 979,
        xlarge: 1199
    };
    Mana.rwdIsMobile = false;
    $(function() {
        if (window.enquire && window.enquire.register) {
            enquire.register('screen and (max-width: ' + bp.medium + 'px)', {
                match: function () {
                    Mana.rwdIsMobile = true;
                    $(document).trigger('m-rwd-mobile');
                },
                unmatch: function () {
                    Mana.rwdIsMobile = false;
                    $(document).trigger('m-rwd-wide');
                }
            });
        }
    });
});
