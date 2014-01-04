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
        _init: function () {
            this._super();
            this._messages['create_seo_schema_duplicate_advice'] = 1;
        },
        _subscribeToHtmlEvents: function () {
            var self = this;

            function _updateSample() {
                self._updateSample();
            }

            return this
                ._super()
                .on('bind', this, function () {
                    $('#mf_url_query_separator').on('change', _updateSample);
                    $('#mf_url_param_separator').on('change', _updateSample);
                    $('#mf_url_first_value_separator').on('change', _updateSample);
                    $('#mf_url_multiple_value_separator').on('change', _updateSample);
                    $('#mf_url_price_separator').on('change', _updateSample);
                    $('#mf_url_category_separator').on('change', _updateSample);
                    $('#mf_url_redirect_to_subcategory').on('click', _updateSample);
                    $('#mf_url_include_filter_name').on('click', _updateSample);
                    $('#mf_url_use_range_bounds').on('click', _updateSample);
                })
                .on('unbind', this, function () {
                    $('#mf_url_query_separator').off('change', _updateSample);
                    $('#mf_url_param_separator').off('change', _updateSample);
                    $('#mf_url_first_value_separator').off('change', _updateSample);
                    $('#mf_url_multiple_value_separator').off('change', _updateSample);
                    $('#mf_url_price_separator').off('change', _updateSample);
                    $('#mf_url_category_separator').off('change', _updateSample);
                    $('#mf_url_redirect_to_subcategory').off('click', _updateSample);
                    $('#mf_url_include_filter_name').off('click', _updateSample);
                    $('#mf_url_use_range_bounds').off('click', _updateSample);
                });
        },
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function () {
                    if (this.getChild('duplicate')) this.getChild('duplicate').on('click', this, this.duplicate);
                    if (this.getChild('delete')) this.getChild('delete').on('click', this, this.deleteClick);
                })
                .on('unload', this, function () {
                    if (this.getChild('duplicate')) this.getChild('duplicate').off('click', this, this.duplicate);
                    if (this.getChild('delete')) this.getChild('delete').off('click', this, this.deleteClick);
                });
        },
        duplicate: function () {
            setLocation(this.getUrl('duplicate'));
        },
        deleteClick: function () {
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
        _updateSample: function() {
            // page URL and query separator
            var url = this.$().find('#mf_url_redirect_to_subcategory').val() == '1' ? '/electronics/computers/monitors' : '/electronics';
            url += this.$().find('#mf_url_query_separator').val();

            // category filter
            if (this.$().find('#mf_url_redirect_to_subcategory').val() != '1') {
                url += 'category';
                url += this.$().find('#mf_url_first_value_separator').val();
                url += 'computers';
                url += this.$().find('#mf_url_category_separator').val();
                url += 'monitors';
                url += this.$().find('#mf_url_param_separator').val();
            }

            // attribute filter
            if (this.$().find('#mf_url_include_filter_name').val() == '1') {
                url += 'color';
                url += this.$().find('#mf_url_first_value_separator').val();
            }
            url += 'red';
            url += this.$().find('#mf_url_multiple_value_separator').val();
            url += 'green';

            // price filter
            url += this.$().find('#mf_url_param_separator').val();
            url += 'price';
            url += this.$().find('#mf_url_first_value_separator').val();
            url += this.$().find('#mf_url_use_range_bounds').val() == '1' ? '200' : '2';
            url += this.$().find('#mf_url_price_separator').val();
            url += this.$().find('#mf_url_use_range_bounds').val() == '1' ? '300' : '100';

            // toolbar parameter
            url += this.$().find('#mf_url_param_separator').val();
            url += 'mode';
            url += this.$().find('#mf_url_first_value_separator').val();
            url += 'grid';

            url += '.html';
            this.$().find('#mf_url_tr_sample .value strong').html(url);
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
