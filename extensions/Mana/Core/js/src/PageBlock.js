Mana.define('Mana/Core/PageBlock', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Config'],
function ($, Block, config)
{
    return Block.extend('Mana/Core/PageBlock', {
        _init: function () {
            this._defaultOverlayFadeout = { overlayTime: 0, popupTime: 0, callback: null };
            this._overlayFadeout = this._defaultOverlayFadeout;
            this._super();
        },
        _subscribeToHtmlEvents: function() {
            var self = this;
            var inResize = false;
            function _raiseResize() {
                if (inResize) {
                    return;
                }
                inResize = true;
                self.resize();
                inResize = false;
            }


            return this
                ._super()
                .on('bind', this, function() {
                    $(window).on('load', _raiseResize);
                    $(window).on('resize', _raiseResize);
                })
                .on('unbind', this, function() {
                    $(window).off('load', _raiseResize);
                    $(window).off('resize', _raiseResize);
                });

        },
        _subscribeToBlockEvents: function() {
            return this
                ._super()
                .on('load', this, function () {
                    this.resize();
                });
        },
        resize: function () {
            this.trigger('resize', {}, false, true);
        },
        showOverlay: function(overlayClass, fadeout) {
            this._overlayFadeout = fadeout || this._defaultOverlayFadeout;
            var overlay = overlayClass ? $('<div class="m-overlay ' + overlayClass + '"></div>') : $('<div class="m-overlay"></div>');
            overlay.appendTo(this.getElement());
            overlay.css({left:0, top:0}).width($(document).width()).height($(document).height());
            return overlay;
        },
        hideOverlay: function() {
            var self = this;
            $('.m-overlay').fadeOut(this._overlayFadeout.overlayTime, function () {
                $('.m-overlay').remove();
                if (self._overlayFadeout.callback) {
                    self._overlayFadeout.callback();
                }
                self._overlayFadeout = self._defaultOverlayFadeout;
            })
            return this;
        },
        showWait: function() {
            $('#m-wait').show();
            return this;
        },
        hideWait: function() {
            $('#m-wait').hide();
            return this;
        },
        getShowDebugMessages: function() {
            return config.getData('debug');
        },
        getShowOverlay:function () {
            return config.getData('showOverlay');
        },
        getShowWait:function () {
            return config.getData('showWait');
        }
    });
});
