Mana.define('Mana/Sorting/Method/TabContainer', ['jquery', 'Mana/Admin/Container', 'singleton:Mana/Core'],
function ($, Container, core) {
    return Container.extend('Mana/Sorting/Method/TabContainer', {
        _subscribeToHtmlEvents: function() {
            var self = this;

            return this._super()
                .on('bind', this, function() {
                })
                .on('unbind', this, function() {
                })
        },
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function () {
                    if (this.getChild('delete')) this.getChild('delete').on('click', this, this.deleteRecord);
                })
                .on('unload', this, function () {
                    if (this.getChild('delete')) this.getChild('delete').off('click', this, this.deleteRecord);
                });
        },
        deleteRecord: function() {
            deleteConfirm(this.getText('delete-confirm'), this.getUrl('delete'));
        }
    });
});