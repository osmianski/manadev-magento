Mana.define('Mana/Core/PopupBlock', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Layout'], function ($, Block, layout) {
    return Block.extend('Mana/Core/PopupBlock', {
        prepare: function(options) {
            var self = this;
            this._host = options.host;

            this.$().find('.btn-close').on('click', function() { return self._close(); });
        },
        _close: function() {
            layout.hidePopup();
            return false;
        }
    });
});
