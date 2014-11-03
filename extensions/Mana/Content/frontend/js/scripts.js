/**
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // for better JS merging




Mana.define('Mana/Content/Tree', ['jquery', 'Mana/Core/Block'],
function ($, Block) {
    return Block.extend('Mana/Content/Tree', {
    });
});

Mana.define('Mana/Content/Tree/Search', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Layout', 'singleton:Mana/Core/Config'],
function ($, Block, layout, config) {
    return Block.extend('Mana/Content/Tree/Search', {
        _init: function() {
            this._super();
        },
        _subscribeToHtmlEvents: function () {
            var self = this;
            function _changed() {
                self.changed();
            }
            return this
                ._super()
                .on('bind', this, function () {
                    this.$field().on('blur', _changed);
                })
                .on('unbind', this, function () {
                    this.$field().off('blur', _changed);
                });
        },
        $field: function(){
            return this.$().find('input');
        },
        $link: function() {
            return this.$().find('a');
        },
        $tree: function(){
            return layout.getBlock('tree');
        },
        changed: function() {
            var url = config.getData('url.unfiltered');
            if(this.$field()[0].getValue().length > 0) {
                url += "?search="+ this.$field()[0].getValue();
            }
            this.$link()[0].href = url;
        }
    });
});

Mana.define('Mana/Content/AjaxInterceptor', ['jquery', 'singleton:Mana/Core/Ajax',
    'singleton:Mana/Core/Config', 'singleton:Mana/Core/Layout'],
function($, ajax, config, layout, undefined)
{
    return Mana.Object.extend('Mana/Content/AjaxInterceptor', {
        match: function (url, element) {
            if (element) {
                var ajaxContainerSelector = config.getData('mana_content.ajax.containers');
                if (ajaxContainerSelector) {
                    if (!$(ajaxContainerSelector).has(element).length) {
                        return false;
                    }
                }
            }
            return true;
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
            requesturl += '/' + config.getData('url.unfiltered').substr(config.getData('url.base').length);
            requesturl += url.substr(queryPos);

            ajax.get(requesturl, function (response) {
                ajax.update(response);
                layout.getPageBlock().resize();
                result = true;
                return true;
            }, { preventClicks: true, encode: queryPos != -1 ? { offset: 0, length: queryPos} : undefined });
            return result;
        }
    });
});
Mana.require(['jquery', 'singleton:Mana/Core/Ajax', 'singleton:Mana/Content/AjaxInterceptor'],
function($, ajax, contentAjaxInterceptor)
{
    ajax.addInterceptor(contentAjaxInterceptor);
});


//# sourceMappingURL=content.js.map