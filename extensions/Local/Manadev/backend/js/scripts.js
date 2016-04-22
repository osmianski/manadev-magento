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

Mana.define('Local/Manadev/SalesItem/DownloadableName', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Ajax'],
function ($, Block, ajax) {
    return Block.extend('Local/Manadev/SalesItem/DownloadableName', {
        _subscribeToHtmlEvents:function () {
            var self = this;
            function _disableDownloads() {
                self.disableDownloads();
            }
            function _enableDownloads() {
                self.enableDownloads();
            }

            return this
                ._super()
                .on('bind', this, function () {
                    this.$().on('click', '.m-download-action.disable-downloads', _disableDownloads);
                    this.$().on('click', '.m-download-action.enable-downloads', _enableDownloads);
                })
                .on('unbind', this, function () {
                    this.$().off('click', '.m-download-action.disable-downloads', _disableDownloads);
                    this.$().off('click', '.m-download-action.enable-downloads', _enableDownloads);
                });
        },
        disableDownloads: function() {
            this.changeStatus('expired');
            return false;
        },
        enableDownloads: function() {
            this.changeStatus('available');
            return false;
        },
        getLinkId: function() {
            if (this._linkId === undefined) {
                this._linkId = this.$().data('link-id');
            }
            return this._linkId;
        },
        getUrl: function() {
            if (this._url === undefined) {
                this._url = this.$().data('url');
            }
            return this._url;
        },

        changeStatus: function(newStatus) {
            var params = [
                { name: 'id', value: this.getLinkId()},
                { name: 'status', value: newStatus},
                { name: 'form_key', value: FORM_KEY}
            ];
            var self = this;

            ajax.post(this.getUrl(), params, function(response) {
                if (!(response && response.success)) {
                    return;
                }

                ajax.update(response);

                var commentText;
                if (newStatus == 'available') {
                    self.$().find('.m-download-action')
                        .removeClass('enable-downloads')
                        .addClass('disable-downloads')
                        .html('Disable downloads');
                    self.$().find('.m-download-status')
                        .removeClass('not-available')
                        .addClass('available')
                        .html('Available');
                }
                else {
                    self.$().find('.m-download-action')
                        .removeClass('disable-downloads')
                        .addClass('enable-downloads')
                        .html('Enable downloads');
                    self.$().find('.m-download-status')
                        .removeClass('available')
                        .addClass('not-available')
                        .html('Not Available');
                }
            });
        }
    });
});
