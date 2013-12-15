/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // make JS merging easier

Mana.define('Mana/AttributePage/AttributePage/ListContainer', ['jquery', 'Mana/Admin/Container'],
function ($, Container)
{
    return Container.extend('Mana/AttributePage/AttributePage/ListContainer', {
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function () {
                    if (this.getChild('create')) this.getChild('create').on('click', this, this.create);
                })
                .on('unload', this, function () {
                    if (this.getChild('create')) this.getChild('create').off('click', this, this.create);
                });
        },
        create: function () {
            setLocation(this.getUrl('create'));
        }
    });
});

Mana.define('Mana/AttributePage/AttributePage/TabContainer', ['jquery', 'Mana/Admin/Container',
    'singleton:Mana/Core/Json'],
function ($, Container, json, undefined)
{
    return Container.extend('Mana/AttributePage/AttributePage/TabContainer', {
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function () {
                    this.updateAttributes();
                    this.updateTabs();
                });
        },
        updateAttributes: function() {
            var lastIndex = -1;
            var values = {};
            var i, field, value;
            var self = this;
            for (i = 0; i < this.getAttrCount(); i++) {
                field = this.getField('attribute_id_' + i);
                if (value = field.getValue()) {
                    lastIndex = i;
                    values[value] = field;
                }
            }
            for (i = 0; i < this.getAttrCount(); i++) {
                field = this.getField('attribute_id_' + i);
                if (i > lastIndex + 1) {
                    field.$().hide();
                }
                else {
                    field.$().show();
                }
                field.$field().find('option').each(function() {
                    if (value = $(this).val()) {
                        if (values[value] !== undefined && values[value] != field) {
                            $(this).hide();
                        }
                        else {
                            $(this).show();
                        }
                    }
                    else {
                        if (i < lastIndex) {
                            $(this).hide();
                        }
                        else {
                            $(this).show();
                        }
                    }
                });
            }
        },
        updateTabs: function() {
            var field = this.getField('attribute_id_0');
            var $tabs = $(".side-col .tabs > li:not(:first)");
            if (field.getValue()) {
                $tabs.show();
            }
            else {
                $tabs.hide();
            }
        },
        getAttrCount: function() {
            return 5;
        },
        getTitleTemplate: function() {
            if (this._titleTemplate === undefined) {
                this._titleTemplate = json.decodeAttribute(this.$().data('title-template'));
            }
            return this._titleTemplate;
        }
    });
});

Mana.define('Mana/AttributePage/AttributePage/TabContainer/Global', ['jquery', 'Mana/AttributePage/AttributePage/TabContainer',
    'singleton:Mana/Core/Layout', 'singleton:Mana/Admin/Aggregate', 'singleton:Mana/Core/StringTemplate'],
function ($, TabContainer, layout, aggregate, template)
{
    return TabContainer.extend('Mana/AttributePage/AttributePage/TabContainer/Global', {
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function () {
                    this.getField('attribute_id_0').on('change', this, this.attributeIdChange);
                    this.getField('attribute_id_1').on('change', this, this.attributeIdChange);
                    this.getField('attribute_id_2').on('change', this, this.attributeIdChange);
                    this.getField('attribute_id_3').on('change', this, this.attributeIdChange);
                    this.getField('attribute_id_4').on('change', this, this.attributeIdChange);

                    this.getField('title').on('change', this, this.titleChange);
                    this.getField('description').on('change', this, this.descriptionChange);

                    this.getField('url_key').on('change', this, this.urlKeyChange);
                    this.getField('meta_title').on('change', this, this.metaTitleChange);
                    this.getField('meta_description').on('change', this, this.metaDescriptionChange);
                    this.getField('meta_keywords').on('change', this, this.metaKeywordsChange);

                    this.getField('option_page_price_step').on('change', this, this.optionPagePriceStepChange);

                    if (this.getChild('delete')) this.getChild('delete').on('click', this, this.deleteClick);
                })
                .on('unload', this, function () {
                    this.getField('attribute_id_0').off('change', this, this.attributeIdChange);
                    this.getField('attribute_id_1').off('change', this, this.attributeIdChange);
                    this.getField('attribute_id_2').off('change', this, this.attributeIdChange);
                    this.getField('attribute_id_3').off('change', this, this.attributeIdChange);
                    this.getField('attribute_id_4').off('change', this, this.attributeIdChange);

                    this.getField('title').off('change', this, this.titleChange);
                    this.getField('description').off('change', this, this.descriptionChange);

                    this.getField('url_key').off('change', this, this.urlKeyChange);
                    this.getField('meta_title').off('change', this, this.metaTitleChange);
                    this.getField('meta_description').off('change', this, this.metaDescriptionChange);
                    this.getField('meta_keywords').off('change', this, this.metaKeywordsChange);

                    this.getField('option_page_price_step').on('change', this, this.optionPagePriceStepChange);

                    if (this.getChild('delete')) this.getChild('delete').off('click', this, this.deleteClick);
                });
        },
        attributeIdChange: function() {
            this.updateAttributes();
            this.updateTabs();
            this.updateTitle();
            this.updateUrlKey();
            this.updateMetaKeywords();
        },
        titleChange: function() {
            this.updateTitle();
            this.updateMetaTitle();
            this.updateDescription();
            this.updateMetaDescription();
        },
        descriptionChange: function() {
            this.updateDescription();
        },
        urlKeyChange: function() {
            this.updateUrlKey();
        },
        metaTitleChange: function() {
            this.updateMetaTitle();
        },
        metaDescriptionChange: function() {
            this.updateMetaDescription();
        },
        metaKeywordsChange: function() {
            this.updateMetaKeywords();
        },
        optionPagePriceStepChange: function() {
            this.updateOptionPagePriceStep();
        },
        updateTitle: function() {
            var field = this.getField('title');
            var titleExpr = aggregate.expr(this._fields, 'attribute_id_X', this.getAttrCount());
            var title = this.getTitleTemplate();
            if (field.useDefault()) {
                field.setValue(template.concat(title['template'], {
                    attribute_labels: title['last_separator']
                        ? aggregate.glue(titleExpr, title['separator'], title['last_separator'])
                        : aggregate.glue(titleExpr, title['last_separator'])
                }));
            }
        },
        updateMetaTitle: function() {
            var field = this.getField('meta_title');
            if (field.useDefault()) {
                field.setValue(this.getField('title').getText());
            }
        },
        updateDescription: function() {
            var field = this.getField('description');
            if (field.useDefault()) {
                field.setValue(this.getField('title').getText());
            }
        },
        updateUrlKey: function() {
            var field = this.getField('url_key');
            var titleExpr = aggregate.expr(this._fields, 'attribute_id_X', this.getAttrCount());
            if (field.useDefault()) {
                field.setValue(aggregate.glue(aggregate.seoify(titleExpr), '-'));
            }
        },
        updateMetaDescription: function() {
            var field = this.getField('meta_description');
            if (field.useDefault()) {
                field.setValue(this.getField('title').getText());
            }
        },
        updateMetaKeywords: function() {
            var field = this.getField('meta_keywords');
            var titleExpr = aggregate.expr(this._fields, 'attribute_id_X', this.getAttrCount());
            if (field.useDefault()) {
                field.setValue(aggregate.glue(titleExpr, ','));
            }
        },
        updateOptionPagePriceStep: function() {
            var field = this.getField('option_page_price_step');
            if (field.useDefault()) {
                field.setValue('');
            }
        },
        deleteClick: function () {
            deleteConfirm(this.getText('delete-confirm'), this.getUrl('delete'));
        }
    });
});
Mana.define('Mana/AttributePage/AttributePage/TabContainer/Store', ['jquery', 'Mana/AttributePage/AttributePage/TabContainer',
    'singleton:Mana/Core/Layout', 'singleton:Mana/Admin/Aggregate', 'singleton:Mana/Core/StringTemplate'],
function ($, TabContainer, layout, aggregate, template)
{
    return TabContainer.extend('Mana/AttributePage/AttributePage/TabContainer/Store', {
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function () {
                    this.getField('title').on('change', this, this.titleChange);
                    this.getField('description').on('change', this, this.descriptionChange);
                    this.getField('is_active').on('change', this, this.isActiveChange);
                    this.getField('include_in_menu').on('change', this, this.includeInMenuChange);

                    this.getField('url_key').on('change', this, this.urlKeyChange);
                    this.getField('meta_title').on('change', this, this.metaTitleChange);
                    this.getField('meta_description').on('change', this, this.metaDescriptionChange);
                    this.getField('meta_keywords').on('change', this, this.metaKeywordsChange);

                    this.getField('show_alphabetic_search').on('change', this, this.showAlphabeticSearchChange);
                    this.getField('page_layout').on('change', this, this.pageLayoutChange);
                    this.getField('layout_xml').on('change', this, this.layoutXmlChange);
                    this.getField('custom_design_active_from').on('change', this, this.customDesignActiveFromChange);
                    this.getField('custom_design_active_to').on('change', this, this.customDesignActiveToChange);
                    this.getField('custom_design').on('change', this, this.customDesignChange);
                    this.getField('custom_layout_xml').on('change', this, this.customLayoutXmlChange);

                    this.getField('option_page_is_active').on('change', this, this.optionPageIsActiveChange);
                    this.getField('option_page_include_in_menu').on('change', this, this.optionPageIncludeInMenuChange);

                    this.getField('option_page_include_filter_name').on('change', this, this.optionPageIncludeFilterNameChange);

                    this.getField('option_page_page_layout').on('change', this, this.optionPagePageLayoutChange);
                    this.getField('option_page_layout_xml').on('change', this, this.optionPageLayoutXmlChange);
                    this.getField('option_page_show_products').on('change', this, this.optionPageShowProductsChange);
                    this.getField('option_page_available_sort_by').on('change', this, this.optionPageAvailableSortByChange);
                    this.getField('option_page_default_sort_by').on('change', this, this.optionPageDefaultSortByChange);
                    this.getField('option_page_price_step').on('change', this, this.optionPagePriceStepChange);
                    this.getField('option_page_custom_design_active_from').on('change', this, this.optionPageCustomDesignActiveFromChange);
                    this.getField('option_page_custom_design_active_to').on('change', this, this.optionPageCustomDesignActiveToChange);
                    this.getField('option_page_custom_design').on('change', this, this.optionPageCustomDesignChange);
                    this.getField('option_page_custom_layout_xml').on('change', this, this.optionPageCustomLayoutXmlChange);
                })
                .on('unload', this, function () {
                    this.getField('title').off('change', this, this.titleChange);
                    this.getField('description').off('change', this, this.descriptionChange);
                    this.getField('is_active').off('change', this, this.isActiveChange);
                    this.getField('include_in_menu').off('change', this, this.includeInMenuChange);

                    this.getField('url_key').off('change', this, this.urlKeyChange);
                    this.getField('meta_title').off('change', this, this.metaTitleChange);
                    this.getField('meta_description').off('change', this, this.metaDescriptionChange);
                    this.getField('meta_keywords').off('change', this, this.metaKeywordsChange);

                    this.getField('show_alphabetic_search').off('change', this, this.showAlphabeticSearchChange);
                    this.getField('page_layout').off('change', this, this.pageLayoutChange);
                    this.getField('layout_xml').off('change', this, this.layoutXmlChange);
                    this.getField('custom_design_active_from').off('change', this, this.customDesignActiveFromChange);
                    this.getField('custom_design_active_to').off('change', this, this.customDesignActiveToChange);
                    this.getField('custom_design').off('change', this, this.customDesignChange);
                    this.getField('custom_layout_xml').off('change', this, this.customLayoutXmlChange);

                    this.getField('option_page_is_active').off('change', this, this.optionPageIsActiveChange);
                    this.getField('option_page_include_in_menu').off('change', this, this.optionPageIncludeInMenuChange);

                    this.getField('option_page_include_filter_name').off('change', this, this.optionPageIncludeFilterNameChange);

                    this.getField('option_page_page_layout').off('change', this, this.optionPagePageLayoutChange);
                    this.getField('option_page_layout_xml').off('change', this, this.optionPageLayoutXmlChange);
                    this.getField('option_page_show_products').off('change', this, this.optionPageShowProductsChange);
                    this.getField('option_page_available_sort_by').off('change', this, this.optionPageAvailableSortByChange);
                    this.getField('option_page_default_sort_by').off('change', this, this.optionPageDefaultSortByChange);
                    this.getField('option_page_price_step').off('change', this, this.optionPagePriceStepChange);
                    this.getField('option_page_custom_design_active_from').off('change', this, this.optionPageCustomDesignActiveFromChange);
                    this.getField('option_page_custom_design_active_to').off('change', this, this.optionPageCustomDesignActiveToChange);
                    this.getField('option_page_custom_design').off('change', this, this.optionPageCustomDesignChange);
                    this.getField('option_page_custom_layout_xml').off('change', this, this.optionPageCustomLayoutXmlChange);
                });
        },
        titleChange: function() {
            this.updateTitle();
        },
        descriptionChange: function() {
            this.updateDescription();
        },
        isActiveChange: function() {
            this.updateFromJson('is_active', 'global');
        },
        includeInMenuChange: function() {
            this.updateFromJson('include_in_menu', 'global');
        },
        urlKeyChange: function() {
            this.updateUrlKey();
        },
        metaTitleChange: function() {
            this.updateMetaTitle();
        },
        metaDescriptionChange: function() {
            this.updateMetaDescription();
        },
        metaKeywordsChange: function() {
            this.updateMetaKeywords();
        },
        showAlphabeticSearchChange: function() {
            this.updateFromJson('show_alphabetic_search', 'global');
        },
        pageLayoutChange: function() {
            this.updateFromJson('page_layout', 'global');
        },
        layoutXmlChange: function() {
            this.updateFromJson('layout_xml', 'global');
        },
        customDesignActiveFromChange: function() {
            this.updateFromJson('custom_design_active_from', 'global');
        },
        customDesignActiveToChange: function() {
            this.updateFromJson('custom_design_active_to', 'global');
        },
        customDesignChange: function() {
            this.updateFromJson('custom_design', 'global');
        },
        customLayoutXmlChange: function() {
            this.updateFromJson('custom_layout_xml', 'global');
        },
        optionPageIsActiveChange: function() {
            this.updateFromJson('option_page_is_active', 'global');
        },
        optionPageIncludeInMenuChange: function() {
            this.updateFromJson('option_page_include_in_menu', 'global');
        },
        optionPageIncludeFilterNameChange: function() {
            this.updateFromJson('option_page_include_filter_name', 'global');
        },
        optionPagePageLayoutChange: function() {
            this.updateFromJson('option_page_page_layout', 'global');
        },
        optionPageLayoutXmlChange: function() {
            this.updateFromJson('option_page_layout_xml', 'global');
        },
        optionPageShowProductsChange: function() {
            this.updateFromJson('option_page_show_products', 'global');
        },
        optionPageAvailableSortByChange: function() {
            this.updateFromJson('option_page_available_sort_by', 'global');
        },
        optionPageDefaultSortByChange: function() {
            this.updateFromJson('option_page_default_sort_by', 'global');
        },
        optionPagePriceStepChange: function() {
            this.updateFromJson('option_page_price_step', 'global');
        },
        optionPageCustomDesignActiveFromChange: function() {
            this.updateFromJson('option_page_custom_design_active_from', 'global');
        },
        optionPageCustomDesignActiveToChange: function() {
            this.updateFromJson('option_page_custom_design_active_to', 'global');
        },
        optionPageCustomDesignChange: function() {
            this.updateFromJson('option_page_custom_design', 'global');
        },
        optionPageCustomLayoutXmlChange: function () {
            this.updateFromJson('option_page_custom_layout_xml', 'global');
        },
        updateTitle: function() {
            var field = this.getField('title');
            var titleExpr = aggregate.expr(this._fields, 'attribute_id_X', this.getAttrCount());
            var title = this.getTitleTemplate();
            if (field.useDefault()) {
                if (this.getJsonData('global-is-custom', 'title')) {
                    field.setValue(this.getJsonData('global', 'title'));
                }
                else {
                    field.setValue(template.concat(title['template'], {
                        attribute_labels: title['last_separator']
                            ? aggregate.glue(titleExpr, title['separator'], title['last_separator'])
                            : aggregate.glue(titleExpr, title['last_separator'])
                    }));
                }
            }
        },
        updateDescription: function() {
            var field = this.getField('description');
            if (field.useDefault()) {
                if (this.getJsonData('global-is-custom', 'description')) {
                    field.setValue(this.getJsonData('global', 'description'));
                }
                else {
                    field.setValue(this.getField('title').getText());
                }
            }
        },
        updateUrlKey: function() {
            var field = this.getField('url_key');
            var titleExpr = aggregate.expr(this._fields, 'attribute_id_X', this.getAttrCount());
            if (field.useDefault()) {
                if (this.getJsonData('global-is-custom', 'url_key')) {
                    field.setValue(this.getJsonData('global', 'url_key'));
                }
                else {
                    field.setValue(aggregate.glue(aggregate.seoify(titleExpr), '-'));
                }
            }
        },
        updateMetaTitle: function() {
            var field = this.getField('meta_title');
            if (field.useDefault()) {
                if (this.getJsonData('global-is-custom', 'meta_title')) {
                    field.setValue(this.getJsonData('global', 'meta_title'));
                }
                else {
                    field.setValue(this.getField('title').getText());
                }
            }
        },
        updateMetaDescription: function() {
            var field = this.getField('meta_description');
            if (field.useDefault()) {
                if (this.getJsonData('global-is-custom', 'meta_description')) {
                    field.setValue(this.getJsonData('global', 'meta_description'));
                }
                else {
                    field.setValue(this.getField('title').getText());
                }
            }
        },
        updateMetaKeywords: function() {
            var field = this.getField('meta_keywords');
            var titleExpr = aggregate.expr(this._fields, 'attribute_id_X', this.getAttrCount());
            if (field.useDefault()) {
                if (this.getJsonData('global-is-custom', 'meta_keywords')) {
                    field.setValue(this.getJsonData('global', 'meta_keywords'));
                }
                else {
                    field.setValue(aggregate.glue(titleExpr, ','));
                }
            }
        }
    });
});
