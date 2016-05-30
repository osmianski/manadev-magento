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
            return element !== undefined && !$(element).hasClass('btn-remove') && (
                $(element).parents().hasClass('mb-category-products') ||
                    $(element).parents().hasClass('mb-cms-products') ||
                    $(element).parents().hasClass('mb-option-view') ||
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
            if (url == location.href || url == location.href + '#') {
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

                if (! (typeof ConfigurableSwatchesList === 'undefined')) {
                    ConfigurableSwatchesList.init();
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

// fix for rwd theme

(function($) {
    var openFilters = [];
    $(document).ready(function () {
    });
    $(document).bind('m-ajax-before', function (e, selectors, url, action) {
        openFilters = [];
        $('.block-layered-nav .toggle-content').children('dl:first').children('dt.current').each(function () {
            openFilters.push($(this).data('id'));
        });
    });
    $(document).bind('m-ajax-after', function (e, selectors, url, action) {
        $('.block-layered-nav .toggle-content').each(function () {
            var wrapper = jQuery(this);

            var hasTabs = wrapper.hasClass('tabs');
            var hasAccordion = wrapper.hasClass('accordion');
            var startOpen = wrapper.hasClass('open');

            var dl = wrapper.children('dl');
            var dts = dl.children('dt');
            var panes = dl.children('dd');
            var groups = new Array(dts, panes);

            //Create a ul for tabs if necessary.
            if (hasTabs) {
                var ul = jQuery('<ul class="toggle-tabs"></ul>');
                dts.each(function () {
                    var dt = jQuery(this);
                    var li = jQuery('<li></li>');
                    li.html(dt.html());
                    ul.append(li);
                });
                ul.insertBefore(dl);
                var lis = ul.children();
                groups.push(lis);
            }

            //Add "last" classes.
            var i;
            for (i = 0; i < groups.length; i++) {
                groups[i].filter(':last').addClass('last');
            }

            function toggleClasses(clickedItem, group) {
                var index = group.index(clickedItem);
                var i;
                for (i = 0; i < groups.length; i++) {
                    groups[i].removeClass('current');
                    groups[i].eq(index).addClass('current');
                }
            }

            //Toggle on tab (dt) click.
            dts.on('click', function (e) {
                //They clicked the current dt to close it. Restore the wrapper to unclicked state.
                if (jQuery(this).hasClass('current') && wrapper.hasClass('accordion-open')) {
                    wrapper.removeClass('accordion-open');
                } else {
                    //They're clicking something new. Reflect the explicit user interaction.
                    wrapper.addClass('accordion-open');
                }
                toggleClasses(jQuery(this), dts);
            });

            //Toggle on tab (li) click.
            if (hasTabs) {
                lis.on('click', function (e) {
                    toggleClasses(jQuery(this), lis);
                });
                //Open the first tab.
                lis.eq(0).trigger('click');
            }

        });
        $.each(openFilters, function(index, id) {
            $('dt[data-id="' + id + '"]').trigger('click');
        });
        openFilters = [];

        $(window).trigger('delayed-resize');
    });

})(jQuery);
