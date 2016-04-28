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

        $(document).on('change', '.m-save-on-change', function (e) {
            var id = this.parentElement.parentElement.dataset['rowId'];
            var status = $(this.parentElement.parentElement).find(".m-status").val();
            var expireDate = $(this.parentElement.parentElement).find(".m-date").val();
            Mana.require(['singleton:Mana/Core/Config', 'singleton:Mana/Core/Ajax'], function(config, ajax) {
                var params = [
                    {name: 'id', value: id},
                    {name: 'status', value: status},
                    {name: 'expireDate', value: expireDate},
                    {name: 'form_key', value: FORM_KEY}
                ];


                ajax.post(config.getData('url.saveLicense'), params, function (response) {
                    if (!(response && response.success)) {
                        return;
                    }

                    var messages = $("#messages").find("ul.messages");
                    if(messages.length == 0) {
                        $("#messages").append("<ul class='messages'></ul>");
                        var messages = $("#messages").find("ul.messages");
                    }
                    messages.append("<li class='success-msg'><ul><li><span>"+ response.message +"</span></li></ul></li>");
                });
            });
        });
    });
})(window, jQuery);
