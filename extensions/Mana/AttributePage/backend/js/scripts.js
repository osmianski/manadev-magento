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
    'singleton:Mana/Core/Json', 'singleton:Mana/Admin/Aggregate'],
function ($, Container, json, aggregate, undefined)
{
    return Container.extend('Mana/AttributePage/AttributePage/TabContainer', {
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function () {
                    this.updateAttributes();
                    this.updateTabs();
                    if (this.getChild('view-option-pages')) this.getChild('view-option-pages').on('click', this, this.viewOptionPages);
                })
                .on('unload', this, function () {
                    if (this.getChild('view-option-pages')) this.getChild('view-option-pages').off('click', this, this.viewOptionPages);
                });
        },
        viewOptionPages: function() {
            setLocation(this.getUrl('option-page-list'));
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
        },
        getAttributePosition: function() {
            var self = this;
            var result = 0;
            var ids = aggregate.expr(this._fields, 'attribute_id_X', this.getAttrCount(), 'getValue');
            $.each(ids, function(i, id) {
                result += parseInt(self.getJsonData('attribute', id).position);
            });
            return result;
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
                    this.getField('position').on('change', this, this.positionChange);
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
                    this.getField('position').off('change', this, this.positionChange);
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
            this.updatePosition();
            this.updateUrlKey();
            this.updateMetaKeywords();
        },
        titleChange: function() {
            this.updateTitle();
            this.updateMetaTitle();
            this.updateDescription();
            this.updateMetaDescription();
        },
        positionChange: function() {
            this.updatePosition();
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
        updatePosition: function() {
            var field = this.getField('position');
            if (field.useDefault()) {
                field.setValue(this.getAttributePosition());
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
                    this.getField('position').on('change', this, this.positionChange);
                    this.getField('title').on('change', this, this.titleChange);
                    this.getField('description').on('change', this, this.descriptionChange);
                    this.getField('image').on('change', this, this.imageChange);
                    this.getField('image_width').on('change', this, this.imageWidthChange);
                    this.getField('image_height').on('change', this, this.imageHeightChange);
                    this.getField('is_active').on('change', this, this.isActiveChange);
                    this.getField('include_in_menu').on('change', this, this.includeInMenuChange);

                    this.getField('url_key').on('change', this, this.urlKeyChange);
                    this.getField('meta_title').on('change', this, this.metaTitleChange);
                    this.getField('meta_description').on('change', this, this.metaDescriptionChange);
                    this.getField('meta_keywords').on('change', this, this.metaKeywordsChange);

                    this.getField('show_alphabetic_search').on('change', this, this.showAlphabeticSearchChange);
                    this.getField('show_featured_options').on('change', this, this.showFeaturedOptionsChange);
                    this.getField('column_count').on('change', this, this.columnCountChange);

                    this.getField('page_layout').on('change', this, this.pageLayoutChange);
                    this.getField('layout_xml').on('change', this, this.layoutXmlChange);
                    this.getField('custom_design_active_from').on('change', this, this.customDesignActiveFromChange);
                    this.getField('custom_design_active_to').on('change', this, this.customDesignActiveToChange);
                    this.getField('custom_design').on('change', this, this.customDesignChange);
                    this.getField('custom_layout_xml').on('change', this, this.customLayoutXmlChange);

                    this.getField('option_page_is_active').on('change', this, this.optionPageIsActiveChange);
                    this.getField('option_page_include_in_menu').on('change', this, this.optionPageIncludeInMenuChange);
                    this.getField('option_page_is_featured').on('change', this, this.optionPageIsFeaturedChange);

                    this.getField('option_page_image').on('change', this, this.optionPageImageChange);
                    this.getField('option_page_image_width').on('change', this, this.optionPageImageWidthChange);
                    this.getField('option_page_image_height').on('change', this, this.optionPageImageHeightChange);

                    this.getField('option_page_featured_image_width').on('change', this, this.optionPageFeaturedImageWidthChange);
                    this.getField('option_page_featured_image_height').on('change', this, this.optionPageFeaturedImageHeightChange);
                    this.getField('option_page_product_image_width').on('change', this, this.optionPageProductImageWidthChange);
                    this.getField('option_page_product_image_height').on('change', this, this.optionPageProductImageHeightChange);
                    this.getField('option_page_sidebar_image_width').on('change', this, this.optionPageSidebarImageWidthChange);
                    this.getField('option_page_sidebar_image_height').on('change', this, this.optionPageSidebarImageHeightChange);

                    this.getField('option_page_include_filter_name').on('change', this, this.optionPageIncludeFilterNameChange);

                    this.getField('option_page_page_layout').on('change', this, this.optionPagePageLayoutChange);
                    this.getField('option_page_layout_xml').on('change', this, this.optionPageLayoutXmlChange);
                    this.getField('option_page_custom_design_active_from').on('change', this, this.optionPageCustomDesignActiveFromChange);
                    this.getField('option_page_custom_design_active_to').on('change', this, this.optionPageCustomDesignActiveToChange);
                    this.getField('option_page_custom_design').on('change', this, this.optionPageCustomDesignChange);
                    this.getField('option_page_custom_layout_xml').on('change', this, this.optionPageCustomLayoutXmlChange);

                    //this.getField('option_page_show_products').on('change', this, this.optionPageShowProductsChange);
                    this.getField('option_page_available_sort_by').on('change', this, this.optionPageAvailableSortByChange);
                    this.getField('option_page_default_sort_by').on('change', this, this.optionPageDefaultSortByChange);
                    this.getField('option_page_price_step').on('change', this, this.optionPagePriceStepChange);
                })
                .on('unload', this, function () {
                    this.getField('position').off('change', this, this.positionChange);
                    this.getField('title').off('change', this, this.titleChange);
                    this.getField('description').off('change', this, this.descriptionChange);
                    this.getField('image').off('change', this, this.imageChange);
                    this.getField('image_width').off('change', this, this.imageWidthChange);
                    this.getField('image_height').off('change', this, this.imageHeightChange);
                    this.getField('is_active').off('change', this, this.isActiveChange);
                    this.getField('include_in_menu').off('change', this, this.includeInMenuChange);

                    this.getField('url_key').off('change', this, this.urlKeyChange);
                    this.getField('meta_title').off('change', this, this.metaTitleChange);
                    this.getField('meta_description').off('change', this, this.metaDescriptionChange);
                    this.getField('meta_keywords').off('change', this, this.metaKeywordsChange);

                    this.getField('show_alphabetic_search').off('change', this, this.showAlphabeticSearchChange);
                    this.getField('show_featured_options').off('change', this, this.showFeaturedOptionsChange);
                    this.getField('column_count').off('change', this, this.columnCountChange);

                    this.getField('page_layout').off('change', this, this.pageLayoutChange);
                    this.getField('layout_xml').off('change', this, this.layoutXmlChange);
                    this.getField('custom_design_active_from').off('change', this, this.customDesignActiveFromChange);
                    this.getField('custom_design_active_to').off('change', this, this.customDesignActiveToChange);
                    this.getField('custom_design').off('change', this, this.customDesignChange);
                    this.getField('custom_layout_xml').off('change', this, this.customLayoutXmlChange);

                    this.getField('option_page_is_active').off('change', this, this.optionPageIsActiveChange);
                    this.getField('option_page_include_in_menu').off('change', this, this.optionPageIncludeInMenuChange);
                    this.getField('option_page_is_featured').off('change', this, this.optionPageIsFeaturedChange);

                    this.getField('option_page_image').off('change', this, this.optionPageImageChange);
                    this.getField('option_page_image_width').off('change', this, this.optionPageImageWidthChange);
                    this.getField('option_page_image_height').off('change', this, this.optionPageImageHeightChange);

                    this.getField('option_page_featured_image_width').off('change', this, this.optionPageFeaturedImageWidthChange);
                    this.getField('option_page_featured_image_height').off('change', this, this.optionPageFeaturedImageHeightChange);
                    this.getField('option_page_product_image_width').off('change', this, this.optionPageProductImageWidthChange);
                    this.getField('option_page_product_image_height').off('change', this, this.optionPageProductImageHeightChange);
                    this.getField('option_page_sidebar_image_width').off('change', this, this.optionPageSidebarImageWidthChange);
                    this.getField('option_page_sidebar_image_height').off('change', this, this.optionPageSidebarImageHeightChange);

                    this.getField('option_page_include_filter_name').off('change', this, this.optionPageIncludeFilterNameChange);

                    this.getField('option_page_page_layout').off('change', this, this.optionPagePageLayoutChange);
                    this.getField('option_page_layout_xml').off('change', this, this.optionPageLayoutXmlChange);
                    this.getField('option_page_custom_design_active_from').off('change', this, this.optionPageCustomDesignActiveFromChange);
                    this.getField('option_page_custom_design_active_to').off('change', this, this.optionPageCustomDesignActiveToChange);
                    this.getField('option_page_custom_design').off('change', this, this.optionPageCustomDesignChange);
                    this.getField('option_page_custom_layout_xml').off('change', this, this.optionPageCustomLayoutXmlChange);

                    //this.getField('option_page_show_products').off('change', this, this.optionPageShowProductsChange);
                    this.getField('option_page_available_sort_by').off('change', this, this.optionPageAvailableSortByChange);
                    this.getField('option_page_default_sort_by').off('change', this, this.optionPageDefaultSortByChange);
                    this.getField('option_page_price_step').off('change', this, this.optionPagePriceStepChange);
                });
        },
        titleChange: function() {
            this.updateTitle();
            this.updateDescription();
            this.updateMetaTitle();
            this.updateMetaDescription();
        },
        descriptionChange: function() {
            this.updateDescription();
        },
        positionChange: function () {
            this.updatePosition();
        },
        imageChange: function() {
            this.updateImageFromJson('image', 'global');
        },
        imageWidthChange: function() {
            this.updateFromJson('image_width', 'global');
        },
        imageHeightChange: function() {
            this.updateFromJson('image_height', 'global');
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
        showFeaturedOptionsChange: function() {
            this.updateFromJson('show_featured_options', 'global');
        },
        columnCountChange: function() {
            this.updateFromJson('column_count', 'global');
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
        optionPageImageChange: function() {
            this.updateImageFromJson('option_page_image', 'global');
        },
        optionPageImageWidthChange: function() {
            this.updateFromJson('option_page_image_width', 'global');
        },
        optionPageImageHeightChange: function() {
            this.updateFromJson('option_page_image_height', 'global');
        },
        optionPageFeaturedImageWidthChange: function() {
            this.updateFromJson('option_page_featured_image_width', 'global');
        },
        optionPageFeaturedImageHeightChange: function() {
            this.updateFromJson('option_page_featured_image_height', 'global');
        },
        optionPageProductImageWidthChange: function() {
            this.updateFromJson('option_page_product_image_width', 'global');
        },
        optionPageProductImageHeightChange: function() {
            this.updateFromJson('option_page_product_image_height', 'global');
        },
        optionPageSidebarImageWidthChange: function() {
            this.updateFromJson('option_page_sidebar_image_width', 'global');
        },
        optionPageSidebarImageHeightChange: function() {
            this.updateFromJson('option_page_sidebar_image_height', 'global');
        },
        optionPageIsActiveChange: function() {
            this.updateFromJson('option_page_is_active', 'global');
        },
        optionPageIsFeaturedChange: function() {
            this.updateFromJson('option_page_is_featured', 'global');
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
        updatePosition: function() {
            var field = this.getField('position');
            if (field.useDefault()) {
                if (this.getJsonData('global-is-custom', 'position')) {
                    field.setValue(this.getJsonData('global', 'position'));
                }
                else {
                    field.setValue(this.getAttributePosition());
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

Mana.define('Mana/AttributePage/OptionPage/TabContainer', ['jquery', 'Mana/Admin/Container',
    'singleton:Mana/Core/Json'],
function ($, Container, json, undefined)
{
    return Container.extend('Mana/AttributePage/OptionPage/TabContainer', {
        getAttrCount: function() {
            return 5;
        },
        getTitleTemplate: function() {
            if (this._titleTemplate === undefined) {
                this._titleTemplate = json.decodeAttribute(this.$().data('title-template'));
            }
            return this._titleTemplate;
        },
        getOptionPosition: function () {
            return this.$().data('option-position');
        }
    });
});

Mana.define('Mana/AttributePage/OptionPage/TabContainer/Global', ['jquery', 'Mana/AttributePage/OptionPage/TabContainer',
    'singleton:Mana/Core/Layout', 'singleton:Mana/Admin/Aggregate', 'singleton:Mana/Core/StringTemplate'],
function ($, TabContainer, layout, aggregate, template)
{
    return TabContainer.extend('Mana/AttributePage/OptionPage/TabContainer/Global', {
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function () {
                    this.getField('position').on('change', this, this.positionChange);
                    this.getField('title').on('change', this, this.titleChange);
                    this.getField('description').on('change', this, this.descriptionChange);
                    this.getField('image').on('change', this, this.imageChange);
                    this.getField('image_width').on('change', this, this.imageWidthChange);
                    this.getField('image_height').on('change', this, this.imageHeightChange);
                    this.getField('is_active').on('change', this, this.isActiveChange);
                    this.getField('include_in_menu').on('change', this, this.includeInMenuChange);
                    this.getField('is_featured').on('change', this, this.isFeaturedChange);

                    this.getField('featured_image').on('change', this, this.featuredImageChange);
                    this.getField('featured_image_width').on('change', this, this.featuredImageWidthChange);
                    this.getField('featured_image_height').on('change', this, this.featuredImageHeightChange);
                    this.getField('product_image').on('change', this, this.productImageChange);
                    this.getField('product_image_width').on('change', this, this.productImageWidthChange);
                    this.getField('product_image_height').on('change', this, this.productImageHeightChange);
                    this.getField('sidebar_image').on('change', this, this.sidebarImageChange);
                    this.getField('sidebar_image_width').on('change', this, this.sidebarImageWidthChange);
                    this.getField('sidebar_image_height').on('change', this, this.sidebarImageHeightChange);

                    this.getField('url_key').on('change', this, this.urlKeyChange);
                    this.getField('meta_title').on('change', this, this.metaTitleChange);
                    this.getField('meta_description').on('change', this, this.metaDescriptionChange);
                    this.getField('meta_keywords').on('change', this, this.metaKeywordsChange);

                    this.getField('page_layout').on('change', this, this.pageLayoutChange);
                    this.getField('layout_xml').on('change', this, this.layoutXmlChange);
                    this.getField('custom_design_active_from').on('change', this, this.customDesignActiveFromChange);
                    this.getField('custom_design_active_to').on('change', this, this.customDesignActiveToChange);
                    this.getField('custom_design').on('change', this, this.customDesignChange);
                    this.getField('custom_layout_xml').on('change', this, this.customLayoutXmlChange);

                    //this.getField('show_products').on('change', this, this.showProductsChange);
                    this.getField('available_sort_by').on('change', this, this.availableSortByChange);
                    this.getField('default_sort_by').on('change', this, this.defaultSortByChange);
                    this.getField('price_step').on('change', this, this.priceStepChange);
                })
                .on('unload', this, function () {
                    this.getField('position').off('change', this, this.positionChange);
                    this.getField('title').off('change', this, this.titleChange);
                    this.getField('description').off('change', this, this.descriptionChange);
                    this.getField('image').off('change', this, this.imageChange);
                    this.getField('image_width').off('change', this, this.imageWidthChange);
                    this.getField('image_height').off('change', this, this.imageHeightChange);
                    this.getField('is_active').off('change', this, this.isActiveChange);
                    this.getField('include_in_menu').off('change', this, this.includeInMenuChange);
                    this.getField('is_featured').off('change', this, this.isFeaturedChange);

                    this.getField('featured_image').off('change', this, this.featuredImageChange);
                    this.getField('featured_image_width').off('change', this, this.featuredImageWidthChange);
                    this.getField('featured_image_height').off('change', this, this.featuredImageHeightChange);
                    this.getField('product_image').off('change', this, this.productImageChange);
                    this.getField('product_image_width').off('change', this, this.productImageWidthChange);
                    this.getField('product_image_height').off('change', this, this.productImageHeightChange);
                    this.getField('sidebar_image').off('change', this, this.sidebarImageChange);
                    this.getField('sidebar_image_width').off('change', this, this.sidebarImageWidthChange);
                    this.getField('sidebar_image_height').off('change', this, this.sidebarImageHeightChange);

                    this.getField('url_key').off('change', this, this.urlKeyChange);
                    this.getField('meta_title').off('change', this, this.metaTitleChange);
                    this.getField('meta_description').off('change', this, this.metaDescriptionChange);
                    this.getField('meta_keywords').off('change', this, this.metaKeywordsChange);

                    this.getField('page_layout').off('change', this, this.pageLayoutChange);
                    this.getField('layout_xml').off('change', this, this.layoutXmlChange);
                    this.getField('custom_design_active_from').off('change', this, this.customDesignActiveFromChange);
                    this.getField('custom_design_active_to').off('change', this, this.customDesignActiveToChange);
                    this.getField('custom_design').off('change', this, this.customDesignChange);
                    this.getField('custom_layout_xml').off('change', this, this.customLayoutXmlChange);

                    //this.getField('show_products').off('change', this, this.showProductsChange);
                    this.getField('available_sort_by').off('change', this, this.availableSortByChange);
                    this.getField('default_sort_by').off('change', this, this.defaultSortByChange);
                    this.getField('price_step').off('change', this, this.priceStepChange);
                });
        },
        titleChange: function() {
            this.updateTitle();
            this.updateDescription();
            this.updateMetaTitle();
            this.updateMetaDescription();
        },
        positionChange: function () {
            this.updatePosition();
        },
        descriptionChange: function () {
            this.updateDescription();
        },
        imageChange: function () {
            this.updateImageFromJson('image', 'attribute-page', 'option_page_image');
            this.featuredImageChange();
            this.productImageChange();
            this.sidebarImageChange();
        },
        imageWidthChange: function () {
            this.updateFromJson('image_width', 'attribute-page', 'option_page_image_width');
        },
        imageHeightChange: function () {
            this.updateFromJson('image_height', 'attribute-page', 'option_page_image_height');
        },
        isActiveChange: function () {
            this.updateFromJson('is_active', 'attribute-page', 'option_page_is_active');
        },
        includeInMenuChange: function () {
            this.updateFromJson('include_in_menu', 'attribute-page', 'option_page_include_in_menu');
        },
        isFeaturedChange: function () {
            this.updateFromJson('is_featured', 'attribute-page', 'option_page_is_featured');
        },
        featuredImageChange: function () {
            this.updateImageFromJson('featured_image', 'attribute-page', 'option_page_featured_image');
        },
        featuredImageWidthChange: function () {
            this.updateFromJson('featured_image_width', 'attribute-page', 'option_page_featured_image_width');
        },
        featuredImageHeightChange: function () {
            this.updateFromJson('featured_image_height', 'attribute-page', 'option_page_featured_image_height');
        },
        productImageChange: function () {
            this.updateImageFromJson('product_image', 'attribute-page', 'option_page_product_image');
        },
        productImageWidthChange: function () {
            this.updateFromJson('product_image_width', 'attribute-page', 'option_page_product_image_width');
        },
        productImageHeightChange: function () {
            this.updateFromJson('product_image_height', 'attribute-page', 'option_page_product_image_height');
        },
        sidebarImageChange: function () {
            this.updateImageFromJson('sidebar_image', 'attribute-page', 'option_page_sidebar_image');
        },
        sidebarImageWidthChange: function () {
            this.updateFromJson('sidebar_image_width', 'attribute-page', 'option_page_sidebar_image_width');
        },
        sidebarImageHeightChange: function () {
            this.updateFromJson('sidebar_image_height', 'attribute-page', 'option_page_sidebar_image_height');
        },
        urlKeyChange: function () {
            this.updateUrlKey();
        },
        metaTitleChange: function () {
            this.updateMetaTitle();
        },
        metaDescriptionChange: function () {
            this.updateMetaDescription();
        },
        metaKeywordsChange: function () {
            this.updateMetaKeywords();
        },
        pageLayoutChange: function () {
            this.updateFromJson('page_layout', 'attribute-page', 'option_page_page_layout');
        },
        layoutXmlChange: function () {
            this.updateFromJson('layout_xml', 'attribute-page', 'option_page_layout_xml');
        },
        customDesignActiveFromChange: function () {
            this.updateFromJson('custom_design_active_from', 'attribute-page', 'option_page_custom_design_active_from');
        },
        customDesignActiveToChange: function () {
            this.updateFromJson('custom_design_active_to', 'attribute-page', 'option_page_custom_design_active_to');
        },
        customDesignChange: function () {
            this.updateFromJson('custom_design', 'attribute-page', 'option_page_custom_design');
        },
        customLayoutXmlChange: function () {
            this.updateFromJson('custom_layout_xml', 'attribute-page', 'option_page_custom_layout_xml');
        },
        showProductsChange: function () {
            this.updateFromJson('show_products', 'attribute-page', 'option_page_show_products');
        },
        availableSortByChange: function () {
            this.updateFromJson('available_sort_by', 'attribute-page', 'option_page_available_sort_by');
        },
        defaultSortByChange: function () {
            this.updateFromJson('default_sort_by', 'attribute-page', 'option_page_default_sort_by');
        },
        priceStepChange: function () {
            this.updateFromJson('price_step', 'attribute-page', 'option_page_price_step');
        },
        updateTitle: function() {
            var field = this.getField('title');
            var titleExpr = aggregate.expr(this._fields, 'option_id_X', this.getAttrCount());
            var title = this.getTitleTemplate();
            if (field.useDefault()) {
                field.setValue(template.concat(title['template'], {
                    option_labels: title['last_separator']
                        ? aggregate.glue(titleExpr, title['separator'], title['last_separator'])
                        : aggregate.glue(titleExpr, title['last_separator'])
                }));
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
            var titleExpr = aggregate.expr(this._fields, 'option_id_X', this.getAttrCount());
            var attrExpr = aggregate.expr(this._fields, 'option_id_X', this.getAttrCount(), 'getLabel');
            if (field.useDefault()) {
                if (parseInt(this.getJsonData('attribute-page', 'option_page_include_filter_name'))) {
                    if (titleExpr.length == 1) {
                        field.setValue(this.getJsonData('attribute-page', 'url_key') + '-' +
                            aggregate.glue(aggregate.seoify(titleExpr), '-'));
                    }
                    else {
                        field.setValue(aggregate.glue(aggregate.concat(
                            aggregate.seoify(attrExpr), '-', aggregate.seoify(titleExpr)), '-'));
                    }
                }
                else {
                    field.setValue(aggregate.glue(aggregate.seoify(titleExpr), '-'));
                }
            }
        },
        updatePosition: function () {
            var field = this.getField('position');
            if (field.useDefault()) {
                field.setValue(this.getOptionPosition());
            }
        },
        updateMetaTitle: function() {
            var field = this.getField('meta_title');
            if (field.useDefault()) {
                field.setValue(this.getField('title').getText());
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
            var titleExpr = aggregate.expr(this._fields, 'option_id_X', this.getAttrCount());
            if (field.useDefault()) {
                field.setValue(aggregate.glue(titleExpr, ','));
            }
        }
    });
});
Mana.define('Mana/AttributePage/OptionPage/TabContainer/Store', ['jquery', 'Mana/AttributePage/OptionPage/TabContainer',
    'singleton:Mana/Core/Layout', 'singleton:Mana/Admin/Aggregate', 'singleton:Mana/Core/StringTemplate'],
function ($, TabContainer, layout, aggregate, template)
{
    return TabContainer.extend('Mana/AttributePage/OptionPage/TabContainer/Store', {
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function () {
                    this.getField('position').on('change', this, this.positionChange);
                    this.getField('title').on('change', this, this.titleChange);
                    this.getField('description').on('change', this, this.descriptionChange);
                    this.getField('image').on('change', this, this.imageChange);
                    this.getField('image_width').on('change', this, this.imageWidthChange);
                    this.getField('image_height').on('change', this, this.imageHeightChange);
                    this.getField('is_active').on('change', this, this.isActiveChange);
                    this.getField('include_in_menu').on('change', this, this.includeInMenuChange);
                    this.getField('is_featured').on('change', this, this.isFeaturedChange);

                    this.getField('featured_image').on('change', this, this.featuredImageChange);
                    this.getField('featured_image_width').on('change', this, this.featuredImageWidthChange);
                    this.getField('featured_image_height').on('change', this, this.featuredImageHeightChange);
                    this.getField('product_image').on('change', this, this.productImageChange);
                    this.getField('product_image_width').on('change', this, this.productImageWidthChange);
                    this.getField('product_image_height').on('change', this, this.productImageHeightChange);
                    this.getField('sidebar_image').on('change', this, this.sidebarImageChange);
                    this.getField('sidebar_image_width').on('change', this, this.sidebarImageWidthChange);
                    this.getField('sidebar_image_height').on('change', this, this.sidebarImageHeightChange);

                    this.getField('url_key').on('change', this, this.urlKeyChange);
                    this.getField('meta_title').on('change', this, this.metaTitleChange);
                    this.getField('meta_description').on('change', this, this.metaDescriptionChange);
                    this.getField('meta_keywords').on('change', this, this.metaKeywordsChange);

                    this.getField('page_layout').on('change', this, this.pageLayoutChange);
                    this.getField('layout_xml').on('change', this, this.layoutXmlChange);
                    this.getField('custom_design_active_from').on('change', this, this.customDesignActiveFromChange);
                    this.getField('custom_design_active_to').on('change', this, this.customDesignActiveToChange);
                    this.getField('custom_design').on('change', this, this.customDesignChange);
                    this.getField('custom_layout_xml').on('change', this, this.customLayoutXmlChange);

                    //this.getField('show_products').on('change', this, this.showProductsChange);
                    this.getField('available_sort_by').on('change', this, this.availableSortByChange);
                    this.getField('default_sort_by').on('change', this, this.defaultSortByChange);
                    this.getField('price_step').on('change', this, this.priceStepChange);
                })
                .on('unload', this, function () {
                    this.getField('position').off('change', this, this.positionChange);
                    this.getField('title').off('change', this, this.titleChange);
                    this.getField('description').off('change', this, this.descriptionChange);
                    this.getField('image').off('change', this, this.imageChange);
                    this.getField('image_width').off('change', this, this.imageWidthChange);
                    this.getField('image_height').off('change', this, this.imageHeightChange);
                    this.getField('is_active').off('change', this, this.isActiveChange);
                    this.getField('include_in_menu').off('change', this, this.includeInMenuChange);
                    this.getField('is_featured').off('change', this, this.isFeaturedChange);

                    this.getField('featured_image').off('change', this, this.featuredImageChange);
                    this.getField('featured_image_width').off('change', this, this.featuredImageWidthChange);
                    this.getField('featured_image_height').off('change', this, this.featuredImageHeightChange);
                    this.getField('product_image').off('change', this, this.productImageChange);
                    this.getField('product_image_width').off('change', this, this.productImageWidthChange);
                    this.getField('product_image_height').off('change', this, this.productImageHeightChange);
                    this.getField('sidebar_image').off('change', this, this.sidebarImageChange);
                    this.getField('sidebar_image_width').off('change', this, this.sidebarImageWidthChange);
                    this.getField('sidebar_image_height').off('change', this, this.sidebarImageHeightChange);

                    this.getField('url_key').off('change', this, this.urlKeyChange);
                    this.getField('meta_title').off('change', this, this.metaTitleChange);
                    this.getField('meta_description').off('change', this, this.metaDescriptionChange);
                    this.getField('meta_keywords').off('change', this, this.metaKeywordsChange);

                    this.getField('page_layout').off('change', this, this.pageLayoutChange);
                    this.getField('layout_xml').off('change', this, this.layoutXmlChange);
                    this.getField('custom_design_active_from').off('change', this, this.customDesignActiveFromChange);
                    this.getField('custom_design_active_to').off('change', this, this.customDesignActiveToChange);
                    this.getField('custom_design').off('change', this, this.customDesignChange);
                    this.getField('custom_layout_xml').off('change', this, this.customLayoutXmlChange);

                    //this.getField('show_products').off('change', this, this.showProductsChange);
                    this.getField('available_sort_by').off('change', this, this.availableSortByChange);
                    this.getField('default_sort_by').off('change', this, this.defaultSortByChange);
                    this.getField('price_step').off('change', this, this.priceStepChange);
                });
        },
        titleChange: function() {
            this.updateTitle();
            this.updateDescription();
            this.updateMetaTitle();
            this.updateMetaDescription();
        },
        positionChange: function () {
            this.updatePosition();
        },
        descriptionChange: function () {
            this.updateDescription();
        },
        imageChange: function () {
            this.updateImageFromJson('image', [
                ['global', 'image', 'global-is-custom'],
                ['attribute-page', 'option_page_image']
            ]);
            this.featuredImageChange();
            this.productImageChange();
            this.sidebarImageChange();
        },
        imageWidthChange: function () {
            this.updateFromJson('image_width', [
                ['global', 'image_width', 'global-is-custom'],
                ['attribute-page', 'option_page_image_width']
            ]);
        },
        imageHeightChange: function () {
            this.updateFromJson('image_height', [
                ['global', 'image_height', 'global-is-custom'],
                ['attribute-page', 'option_page_image_height']
            ]);
        },
        featuredImageChange: function () {
            this.updateImageFromJson('featured_image', [
                ['global', 'featured_image', 'global-is-custom'],
                ['attribute-page', 'option_page_featured_image']
            ]);
        },
        featuredImageWidthChange: function () {
            this.updateFromJson('featured_image_width', [
                ['global', 'featured_image_width', 'global-is-custom'],
                ['attribute-page', 'option_page_featured_image_width']
            ]);
        },
        featuredImageHeightChange: function () {
            this.updateFromJson('featured_image_height', [
                ['global', 'featured_image_height', 'global-is-custom'],
                ['attribute-page', 'option_page_featured_image_height']
            ]);
        },
        productImageChange: function () {
            this.updateImageFromJson('product_image', [
                ['global', 'product_image', 'global-is-custom'],
                ['attribute-page', 'option_page_product_image']
            ]);
        },
        productImageWidthChange: function () {
            this.updateFromJson('product_image_width', [
                ['global', 'product_image_width', 'global-is-custom'],
                ['attribute-page', 'option_page_product_image_width']
            ]);
        },
        productImageHeightChange: function () {
            this.updateFromJson('product_image_height', [
                ['global', 'product_image_height', 'global-is-custom'],
                ['attribute-page', 'option_page_product_image_height']
            ]);
        },
        sidebarImageChange: function () {
            this.updateImageFromJson('sidebar_image', [
                ['global', 'sidebar_image', 'global-is-custom'],
                ['attribute-page', 'option_page_sidebar_image']
            ]);
        },
        sidebarImageWidthChange: function () {
            this.updateFromJson('sidebar_image_width', [
                ['global', 'sidebar_image_width', 'global-is-custom'],
                ['attribute-page', 'option_page_sidebar_image_width']
            ]);
        },
        sidebarImageHeightChange: function () {
            this.updateFromJson('sidebar_image_height', [
                ['global', 'sidebar_image_height', 'global-is-custom'],
                ['attribute-page', 'option_page_sidebar_image_height']
            ]);
        },
        isActiveChange: function () {
            this.updateFromJson('is_active', [
                ['global', 'is_active', 'global-is-custom'],
                ['attribute-page', 'option_page_is_active']
            ]);
        },
        includeInMenuChange: function () {
            this.updateFromJson('include_in_menu', [
                ['global', 'include_in_menu', 'global-is-custom'],
                ['attribute-page', 'option_page_include_in_menu']
            ]);
        },
        isFeaturedChange: function () {
            this.updateFromJson('is_featured', [
                ['global', 'is_featured', 'global-is-custom'],
                ['attribute-page', 'option_page_is_featured']
            ]);
        },
        urlKeyChange: function () {
            this.updateUrlKey();
        },
        metaTitleChange: function () {
            this.updateMetaTitle();
        },
        metaDescriptionChange: function () {
            this.updateMetaDescription();
        },
        metaKeywordsChange: function () {
            this.updateMetaKeywords();
        },
        pageLayoutChange: function () {
            this.updateFromJson('page_layout', [
                ['global', 'page_layout', 'global-is-custom'],
                ['attribute-page', 'option_page_page_layout']
            ]);
        },
        layoutXmlChange: function () {
            this.updateFromJson('layout_xml', [
                ['global', 'layout_xml', 'global-is-custom'],
                ['attribute-page', 'option_page_layout_xml']
            ]);
        },
        customDesignActiveFromChange: function () {
            this.updateFromJson('custom_design_active_from', [
                ['global', 'custom_design_active_from', 'global-is-custom'],
                ['attribute-page', 'option_page_custom_design_active_from']
            ]);
        },
        customDesignActiveToChange: function () {
            this.updateFromJson('custom_design_active_to', [
                ['global', 'custom_design_active_to', 'global-is-custom'],
                ['attribute-page', 'option_page_custom_design_active_to']
            ]);
        },
        customDesignChange: function () {
            this.updateFromJson('custom_design', [
                ['global', 'custom_design', 'global-is-custom'],
                ['attribute-page', 'option_page_custom_design']
            ]);
        },
        customLayoutXmlChange: function () {
            this.updateFromJson('custom_layout_xml', [
                ['global', 'custom_layout_xml', 'global-is-custom'],
                ['attribute-page', 'option_page_custom_layout_xml']
            ]);
        },
        showProductsChange: function () {
            this.updateFromJson('show_products', [
                ['global', 'show_products', 'global-is-custom'],
                ['attribute-page', 'option_page_show_products']
            ]);
        },
        availableSortByChange: function () {
            this.updateFromJson('available_sort_by', [
                ['global', 'available_sort_by', 'global-is-custom'],
                ['attribute-page', 'option_page_available_sort_by']
            ]);
        },
        defaultSortByChange: function () {
            this.updateFromJson('default_sort_by', [
                ['global', 'default_sort_by', 'global-is-custom'],
                ['attribute-page', 'option_page_default_sort_by']
            ]);
        },
        priceStepChange: function () {
            this.updateFromJson('price_step', [
                ['global', 'price_step', 'global-is-custom'],
                ['attribute-page', 'option_page_price_step']
            ]);
        },
        updateTitle: function() {
            var field = this.getField('title');
            var titleExpr = aggregate.expr(this._fields, 'option_id_X', this.getAttrCount());
            var title = this.getTitleTemplate();
            if (field.useDefault()) {
                if (this.getJsonData('global-is-custom', 'title')) {
                    field.setValue(this.getJsonData('global', 'title'));
                }
                else {
                    field.setValue(template.concat(title['template'], {
                        option_labels: title['last_separator']
                            ? aggregate.glue(titleExpr, title['separator'], title['last_separator'])
                            : aggregate.glue(titleExpr, title['last_separator'])
                    }));
                }
            }
        },
        updatePosition: function () {
            var field = this.getField('position');
            if (field.useDefault()) {
                if (this.getJsonData('global-is-custom', 'position')) {
                    field.setValue(this.getJsonData('global', 'position'));
                }
                else {
                    field.setValue(this.getOptionPosition());
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
            var titleExpr = aggregate.expr(this._fields, 'option_id_X', this.getAttrCount());
            var attrExpr = aggregate.expr(this._fields, 'option_id_X', this.getAttrCount(), 'getLabel');
            if (field.useDefault()) {
                if (this.getJsonData('global-is-custom', 'url_key')) {
                    field.setValue(this.getJsonData('global', 'url_key'));
                }
                else {
                    if (parseInt(this.getJsonData('attribute-page', 'option_page_include_filter_name'))) {
                        if (titleExpr.length == 1) {
                            field.setValue(this.getJsonData('attribute-page', 'url_key') + '-' +
                                aggregate.glue(aggregate.seoify(titleExpr), '-'));
                        }
                        else {
                            field.setValue(aggregate.glue(aggregate.concat(
                                aggregate.seoify(attrExpr), '-', aggregate.seoify(titleExpr)), '-'));
                        }
                    }
                    else {
                        field.setValue(aggregate.glue(aggregate.seoify(titleExpr), '-'));
                    }
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
            var titleExpr = aggregate.expr(this._fields, 'option_id_X', this.getAttrCount());
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
