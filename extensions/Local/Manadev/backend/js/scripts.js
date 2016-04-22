;(function (window, $) {
    $(function(){
        $(document).on('click', '.mana-multiline-show-more, .mana-multiline-show-less', function(e) {
            var self = this;
            self.siblings().each(function(item) {
                if(item.hasClassName('mana-multiline-show-more') || item.hasClassName('mana-multiline-show-less')) {
                    item.show();
                    self.hide();
                    return false;
                }
            });

            var show = self.hasClassName('mana-multiline-show-more');
            self.siblings().each(function(item) {
                if(item.hasClassName('mana-multiline')) {
                    if(show) {
                        item.show();
                    } else {
                        item.hide();
                    }
                    return false;
                }
            });
            e.preventDefault();
        });
    });
})(window, jQuery);