/**
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // make JS merging easier

Mana.define('Mana/Seo/Schema/TabContainer', ['jquery', 'Mana/Admin/Container', 'singleton:Mana/Core/Ajax',
    'singleton:Mana/Core'],
function ($, Container, ajax, core)
{
    return Container.extend('Mana/Seo/Schema/TabContainer', {
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function () {
                    if (this.getChild('duplicate')) this.getChild('duplicate').on('click', this, this.duplicate);
                    if (this.getChild('delete')) this.getChild('delete').on('click', this, this.delete);
                })
                .on('unload', this, function () {
                    if (this.getChild('duplicate')) this.getChild('duplicate').off('click', this, this.duplicate);
                    if (this.getChild('delete')) this.getChild('delete').off('click', this, this.delete);
                });
        },
        duplicate: function () {
            setLocation(this.getUrl('duplicate'));
        },
        delete: function () {
            deleteConfirm(this.getText('delete-confirm'), this.getUrl('delete'));
        },
        save: function (callback) {
            var params = this.getPostParams();
            var self = this;
            ajax.post(this.getUrl('before-save'), params, function (response) {
                if (response.messages) {
                    alert(response.messages.join("\n"));
                }
                if (response.affectsUrl) {
                    params.push({name: 'createObsoleteCopy', value: confirm(response.affectsUrl)});
                }
                ajax.post(self.getUrl('save'), params, function (response) {
                    ajax.update(response);
                    var $status = self.$().find('#mf_url_status');
                    if ($status.val() == 'active') {
                        $status.attr('disabled', 'disabled');
                    }
                    if (core.isFunction(callback)) {
                        callback.call();
                    }
                });
            });
        }
    });
});
