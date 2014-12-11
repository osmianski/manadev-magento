/**
 * @category    Mana
 * @package     ManaPro_FilterAjax
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
// the following function wraps code block that is executed once this javascript file is parsed. Lierally, this 
// notation says: here we define some anonymous function and call it once during file parsing. THis function has
// one parameter which is initialized with global jQuery object. Why use such complex notation: 
// 		a. 	all variables defined inside of the function belong to function's local scope, that is these variables
//			would not interfere with other global variables.
//		b.	we use jQuery $ notation in not conflicting way (along with prototype, ext, etc.)
;
Mana.define('Mana/LayeredNavigation/AjaxInterceptor', ['jquery', 'singleton:Mana/Core/Ajax',
    'singleton:Mana/Core/Config', 'singleton:Mana/Core/Layout'],
function($, ajax, config, layout, undefined)
{
    return Mana.Object.extend('Mana/LayeredNavigation/AjaxInterceptor', {
        _getBaseUrl: function(url) {
            return config.getBaseUrl(url);
        },
        _isProductListToolbarClicked: function (element) {
            return element !== undefined && (
                $(element).parents().hasClass('mb-category-products') ||
                    $(element).parents().hasClass('mb-cms-products') ||
                    $(element).parents().hasClass('mb-search-result')
                );
        },
        match: function (url, element) {
            if (element) {
                var ajaxContainerSelector = config.getData('layeredNavigation.ajax.containers');
                if (ajaxContainerSelector) {
                    if (!$(ajaxContainerSelector).has(element).length) {
                        return false;
                    }
                }
            }
            var result = false;
            var exception = false;
            if (url == location.href + '#') {
                return result;
            }
            var decodedUrl = decodeURIComponent(url);
            var pattern = config.getData('layeredNavigation.ajax.exceptionPatterns');
            if (pattern) {
                pattern = new RegExp(pattern);
                if (pattern.test(decodedUrl)) {
                    return result;
                }
            }
            $.each(config.getData('layeredNavigation.ajax.exceptions'), function (key, exceptionUrl) {
                if (url.indexOf(exceptionUrl) != 0) {
                    return true;
                }

                var suffixPos = false;
                if (config.getData('url.suffix')) {
                    suffixPos = url.indexOf(config.getData('url.suffix'), exceptionUrl.length);
                    if (suffixPos === -1) {
                        return true;
                    }
                }

                exception = true;
                return false;
            });
            if (exception) {
                return result;
            }
            $.each(config.getData('url.unfiltered'), function(key, unfilteredUrl) {
                if (url.indexOf(unfilteredUrl) != 0) {
                    return true;
                }

                var suffixPos = false;
                if (config.getData('url.suffix')) {
                    suffixPos = url.indexOf(config.getData('url.suffix'), unfilteredUrl.length);
                    if (suffixPos === -1) {
                        return true;
                    }
                }

                result = true;
                return false;
            });
            return result;
        },
        intercept: function (url, element) {
            var isProductListToolbarClicked = this._isProductListToolbarClicked(element);
            var parser = document.createElement('a');
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

            url = this._getBaseUrl(url) + config.getData('layeredNavigation.ajax.urlKey') +
                '/' + config.getData('ajax.currentRoute') +
                '/' + config.getData('layeredNavigation.ajax.routeSeparator') + '/' +
                url.substr(this._getBaseUrl(url).length);

            ajax.get(url, function (response) {
                ajax.update(response);
                layout.getPageBlock().resize();

                if ($('#nav') && typeof mainNav != 'undefined') {
                    mainNav("nav", {"show_delay": "100", "hide_delay": "100"});
                }

                if (isProductListToolbarClicked && config.getData('layeredNavigation.ajax.scrollToTop')) {
                    var offset = -1;
                    $.each(response.blocks, function (blockName) {
                        var block = layout.getBlock(blockName);
                        if (block) {
                            var blockOffset = block.$().offset().top;
                            if (offset == -1 || offset >= blockOffset) {
                                offset = blockOffset;
                            }
                        }
                    });
                    if (offset >= 0) {
                        offset -= 10;
                        if (offset < 0) {
                            offset = 0;
                        }
                        //noinspection JSUnresolvedFunction
                        scroll(0, offset);
                    }
                }
            }, { preventClicks: true, encode: queryPos != -1 ? { offset: 0, length : queryPos} : undefined });
        }
    });
});
Mana.require(['jquery', 'singleton:Mana/Core/Ajax', 'singleton:Mana/LayeredNavigation/AjaxInterceptor'],
function($, ajax, layeredNavigationAjaxInterceptor)
{
    ajax.addInterceptor(layeredNavigationAjaxInterceptor);
});
