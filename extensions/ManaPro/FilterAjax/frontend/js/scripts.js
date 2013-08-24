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
function($, ajax, config, layout)
{
    return Mana.Object.extend('Mana/LayeredNavigation/AjaxInterceptor', {
        _getBaseUrl: function(url) {
            return url.indexOf(config.getData('url.base')) == 0
                ? config.getData('url.base')
                : config.getData('url.secureBase');
        },
        _isProductListToolbarClicked: function(element) {
            return element !== undefined && (
                $(element).parents().hasClass('mb-category-products') ||
                    $(element).parents().hasClass('mb-cms-products')
                );
        },
        match: function (url, element) {
            var result = false;
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
            url = this._getBaseUrl(url) + config.getData('layeredNavigation.ajax.urlKey') +
                '/' + config.getData('ajax.currentRoute') +
                '/' + config.getData('layeredNavigation.ajax.routeSeparator') + '/' + url.substr(this._getBaseUrl(url).length);

            ajax.get(url, function (response) {
                ajax.update(response);

                if (isProductListToolbarClicked && config.getData('layeredNavigation.ajax.scrollToTop')) {
                    var offset = -1;
                    $.each(response.blocks, function (blockName) {
                        var block = layout.getBlock(blockName);
                        if (block) {
                            offset = block.$().offset().top;
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
            });
        }
    });
});
Mana.require(['jquery', 'singleton:Mana/Core/Ajax', 'singleton:Mana/LayeredNavigation/AjaxInterceptor'],
function($, ajax, layeredNavigationAjaxInterceptor)
{
    ajax.addInterceptor(layeredNavigationAjaxInterceptor);
});
