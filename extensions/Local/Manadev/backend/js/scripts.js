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
            var rowElement = $(this.parentElement.parentElement);
            var status = rowElement.find(".m-status").val();
            var expireDate = rowElement.find(".m-date").val();
            var registeredUrl = rowElement.find(".m-registered-domain").val();
            var target = e.target;
            var insertHistory = target.hasClassName("m-registered-domain");

            var showMoreLink = $(target.parentElement).find('.mana-multiline-show-more')[0];
            var isShowMore = false;
            if(showMoreLink) {
                isShowMore = showMoreLink.style.display == "none";
            }
            Mana.require(['singleton:Mana/Core/Config', 'singleton:Mana/Core/Ajax'], function(config, ajax) {
                var params = [
                    {name: 'id', value: id},
                    {name: 'status', value: status},
                    {name: 'expireDate', value: expireDate},
                    {name: 'registeredDomain', value: registeredUrl},
                    {name: 'insertHistory', value: insertHistory},
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

                    if(insertHistory && response.m_registered_domain_history) {
                        target.parentElement.innerHTML = target.outerHTML + response.m_registered_domain_history;
                        rowElement.find(".m-registered-domain").val(registeredUrl);
                        if(isShowMore) {
                            rowElement.find(".mana-multiline-show-more").click();
                        }
                    }
                    if(response.new_status) {
                        rowElement.find(".m-status").val(response.new_status);
                    }

                });
            });
        });
    });
})(window, jQuery);
