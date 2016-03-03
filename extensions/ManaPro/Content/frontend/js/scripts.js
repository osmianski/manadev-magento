/**
 * @category    Mana
 * @package     ManaPro_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // for better JS merging




Mana.define('ManaPro/Content/Filter', ['jquery', 'singleton:Mana/Core/Config'],
function ($, config) {
    return Mana.Object.extend('ManaPro/Content/Filter', {
        _init: function(){
            this.loadFilterFromUrl();
        },
        loadFilterFromUrl: function () {
            this._searchValue = "";
            this._relatedProducts = [];
            this._tags = [];
            var queryString = window.location.search;
            queryString = queryString.substr(1, queryString.length - 1);
            queryString = queryString.split('&');
            var param;
            for(var i = 0; i <queryString.length; i++) {
                param = queryString[i].split('=');
                switch(param[0]) {
                    case 'search':
                        this._searchValue = param[1];
                        break;
                    case 'related_products':
                        this._relatedProducts = param[1].split(',').map(Number);
                        break;
                    case 'tags':
                        this._tags = param[1].split(',').map(Number);
                        break;
                }
            }
        },
        setSearch: function(searchValue) {
            this._searchValue = searchValue;
            return this.constructUrl();
        },
        getUrlIfTagSelected: function(tagId) {
            var url = this.addTag(tagId);
            this.removeTag(tagId);
            return url;
        },
        getUrlIfTagNotSelected: function(tagId) {
            var url = this.removeTag(tagId);
            this.addTag(tagId);
            return url;
        },
        addTag: function (tagId) {
            if(!this.isTagSelected(tagId)) {
                this._tags.push(tagId);
            }
            return this.constructUrl();
        },
        removeTag: function (tagId) {
            var self = this;
            $.each(this._tags, function(i) {
                if(self._tags[i] == tagId) {
                    self._tags.splice(i, 1);
                    return;
                }
            });
            return this.constructUrl();
        },
        isTagSelected: function (tagId) {
            return $.inArray(tagId, this._tags) !== -1;
        },
        getUrlIfRelatedProductChecked: function(productId) {
            var url = this.addToRelatedProducts(productId);
            this.removeFromRelatedProduct(productId);
            return url;
        },
        getUrlIfRelatedProductUnchecked: function(productId) {
            var url = this.removeFromRelatedProduct(productId);
            this.addToRelatedProducts(productId);
            return url;
        },
        setRelatedProducts: function(productIds) {
            this._relatedProducts = productIds;
            return this.constructUrl();
        },
        addToRelatedProducts: function(productId) {
            if(!this.isProductChecked(productId)) {
                this._relatedProducts.push(productId);
            }
            return this.constructUrl();
        },
        removeFromRelatedProduct: function(productId) {
            var self = this;
            $.each(this._relatedProducts, function(i) {
                if(self._relatedProducts[i] == productId) {
                    self._relatedProducts.splice(i, 1);
                    return;
                }
            });
            return this.constructUrl();
        },
        isProductChecked: function (productId) {
            return $.inArray(productId, this._relatedProducts) !== -1;
        },
        constructUrl: function() {
            var url = config.getData('mana_content.url');
            var params = {};
            if (this._searchValue.length > 0) {
                params.search = this._searchValue;
            }
            if(this._relatedProducts.length > 0) {
                params.related_products = this._relatedProducts;
            }
            if (this._tags.length > 0) {
                params.tags = this._tags;
            }
            if(params.search || params.related_products || params.tags) {
                url += "?";
                var skipAnd = true;
                $.each(params, function(i) {
                    if(skipAnd) {
                        skipAnd = false;
                    } else {
                        url += "&";
                    }
                    url += i + "=" + params[i];
                });
            }
            return url;
        }
    });
});

Mana.define('ManaPro/Content/AjaxInterceptor', ['jquery', 'singleton:Mana/Core/Ajax',
    'singleton:Mana/Core/Config', 'singleton:Mana/Core/Layout', 'singleton:ManaPro/Content/Filter'],
function($, ajax, config, layout, filter, undefined)
{
    return Mana.Object.extend('ManaPro/Content/AjaxInterceptor', {
        match: function (url, element) {
            if (element) {
                var ajaxContainerSelector = config.getData('mana_content.ajax.containers');
                if (ajaxContainerSelector) {
                    if ($(ajaxContainerSelector).has(element).length > 0) {
                        return true;
                    }
                }
            }
            return false;
        },
        intercept: function (url, element) {
            var parser = document.createElement('a');
            var result = false;
            parser.href = url;
            if (window._gaq !== undefined) {
                window._gaq.push(['_setAccount', config.getData('ga.account')]);
                window._gaq.push(['_trackPageview', url.substring(parser.protocol.length + parser.hostname.length + 2)]);
            }
            if (window.ga !== undefined) {
                window.ga('send', 'pageview', {'page': url.substring(parser.protocol.length + parser.hostname.length + 2)});
            }

            var encodedUrl = url;
            url = decodeURIComponent(url);

            var encodedQueryPos = encodedUrl.indexOf('?'), queryPos = url.indexOf('?');
            if (encodedQueryPos != -1 && queryPos != -1) {
                url = url.substr(0, queryPos) + '?' + encodedUrl.substr(encodedQueryPos + 1);
            }



            var requesturl = config.getData('url.base');
            requesturl += config.getData('mana_content.ajax.urlKey');
            requesturl += '/' + config.getData('ajax.currentRoute');
            requesturl += '/' + config.getData('mana_content.ajax.routeSeparator');
            requesturl += '/' + url.substr(config.getData('url.base').length);

            ajax.get(requesturl, function (response) {
                filter.loadFilterFromUrl();
                ajax.update(response);
                layout.getPageBlock().resize();
                result = true;
                return true;
            }, { preventClicks: true, encode: queryPos != -1 ? { offset: 0, length: queryPos} : undefined });
            return result;
        }
    });
});
Mana.require(['jquery', 'singleton:Mana/Core/Ajax', 'singleton:ManaPro/Content/AjaxInterceptor'],
function($, ajax, contentAjaxInterceptor)
{
    // disabled as it doesn't work anymore after upgrade to 1.9
    //ajax.addInterceptor(contentAjaxInterceptor);
});


Mana.define('ManaPro/Content/Tree/Search', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Layout', 'singleton:Mana/Core/Config', 'singleton:ManaPro/Content/Filter'],
function ($, Block, layout, config, filter) {
    return Block.extend('ManaPro/Content/Tree/Search', {
        _subscribeToHtmlEvents: function () {
            var self = this;

            function _submit(e) {
                self.changed();
                self.submit();
                e.preventDefault();
            }

            return this
                ._super()
                .on('bind', this, function () {
                    this.$form().on('submit', _submit);
                })
                .on('unbind', this, function () {
                    this.$form().off('submit', _submit);
                });
        },
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function(){
                    this.$field()[0].setValue(filter._searchValue);
                })
        },
        submit: function() {
            this.$link()[0].href = this.$form()[0].action;
            this.$link().click();
        },
        $field: function(){
            return this.$().find('input');
        },
        $form: function () {
            return this.$().find('form');
        },
        $link: function() {
            return this.$().find('a');
        },
        $tree: function(){
            return layout.getBlock('tree');
        },
        changed: function() {
            this.$form()[0].action = filter.setSearch(this.$field()[0].getValue());
        }
    });
});

Mana.define('ManaPro/Content/Tree/RelatedProduct', ['jquery', 'Mana/Core/Block', 'singleton:ManaPro/Content/Filter'],
function ($, Block, filter) {
    return Block.extend('ManaPro/Content/Tree/RelatedProduct', {
        setFilterUrl: function (link) {
            var productId = link.data('mProductId');
            if(filter.isProductChecked(productId)) {
                link.addClass('m-checkbox-checked');
            } else {
                link.addClass('m-checkbox-unchecked');
            }
            link[0].href = link.hasClass('m-checkbox-unchecked') ? filter.getUrlIfRelatedProductChecked(productId) : filter.getUrlIfRelatedProductUnchecked(productId);
        },
        _subscribeToBlockEvents: function () {
            var self = this;

            return this
                ._super()
                .on('load', this, function () {
                    this.$().find('a').each(function(){
                        self.setFilterUrl($(this));
                    });
                })
        }
    });
});

Mana.define('ManaPro/Content/Tree/Tag', ['jquery', 'Mana/Core/Block', 'singleton:ManaPro/Content/Filter'],
function ($, Block, filter) {
    return Block.extend('ManaPro/Content/Tree/Tag', {
        setFilterUrl: function (link) {
            var tagId = link.data('mTagId');
            if(filter.isTagSelected(tagId)) {
                link.addClass('m-tag-selected');
            }
            link[0].href = !link.hasClass('m-tag-selected') ? filter.getUrlIfTagSelected(tagId) : filter.getUrlIfTagNotSelected(tagId);
        },
        _subscribeToBlockEvents: function () {
            var self = this;

            return this
                ._super()
                .on('load', this, function () {
                    this.$().find('a').each(function(){
                        self.setFilterUrl($(this));
                    });
                })
        }
    });
});

//# sourceMappingURL=content.js.map