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
        _subscribeToHtmlEvents: function () {
            var self = this;

            function _hideCreateDuplicateAdvice() {
                self._hideCreateDuplicateAdvice(this);
            }

            return this
                ._super()
                .on('bind', this, function () {
                    $('.hide-create-duplicate-advice').on('click', _hideCreateDuplicateAdvice);
                })
                .on('unbind', this, function () {
                    $('.hide-create-duplicate-advice').off('click', _hideCreateDuplicateAdvice);
                });
        },
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
//            ajax.post(this.getUrl('before-save'), params, function (response) {
//                if (response.messages) {
//                    alert(response.messages.join("\n"));
//                }
//                //noinspection JSUnresolvedVariable
//                if (response.affectsUrl) {
//                    //noinspection JSUnresolvedVariable
//                    params.push({name: 'createObsoleteCopy', value: confirm(response.affectsUrl)});
//                }
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
//            });
        },
        _hideCreateDuplicateAdvice: function(a) {
            //noinspection JSCheckFunctionSignatures
            var $li = $(a).parent();
            $li.hide();
            //noinspection JSCheckFunctionSignatures
            for (var $parent = $li.parent(); $parent.length && $parent[0].id != 'messages'; $parent = $parent.parent()) {
                if ($parent.children(':visible').length) {
                    break;
                }
                $parent.hide();
            }
            ajax.post(this.getUrl('hide-create-duplicate-advice'), [{name: 'form_key', value: FORM_KEY}]);
        }
    });
});

Mana.define('Mana/Seo/Url/FormContainer', ['jquery', 'Mana/Admin/Container'],
function ($, Container) {
    return Container.extend('Mana/Seo/Url/FormContainer', {
        _subscribeToHtmlEvents:function () {
            var self = this;

            function _urlKeyChange() {
                self._onManualValueChange('final_url_key', 'url_key', $(this).val(), $(this).val());
            }

            function _includeFilterNameChange() {
                self._onManualValueChange('final_include_filter_name', 'include_filter_name', $(this).val(),
                    $(this).find('option:selected').text());
            }

            return this
                ._super()
                .on('bind', this, function () {
                    this.$().find('#mf_form_manual_url_key').on('change', _urlKeyChange);
                    this.$().find('#mf_form_force_include_filter_name').on('change', _includeFilterNameChange);
                })
                .on('unbind', this, function () {
                    this.$().find('#mf_form_manual_url_key').off('change', _urlKeyChange);
                });
        },
        _onManualValueChange: function(targetField, defaultField, value, label) {
                this.$().find('#mf_form_tr_' + targetField + ' .value strong').html(value !== ''
                    ? label
                    : this.$().find('#mf_form_tr_' + defaultField + ' .value strong').html());
        }
    });
});
