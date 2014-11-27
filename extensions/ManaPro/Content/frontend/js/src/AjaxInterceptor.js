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
    ajax.addInterceptor(contentAjaxInterceptor);
});
