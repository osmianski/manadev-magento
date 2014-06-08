Mana.define('Mana/Core/Ajax', ['jquery', 'singleton:Mana/Core/Layout', 'singleton:Mana/Core/Json',
    'singleton:Mana/Core', 'singleton:Mana/Core/Config'],
function ($, layout, json, core, config, undefined)
{
    return Mana.Object.extend('Mana/Core/Ajax', {
        _init: function() {
            this._interceptors = [];
            this._matchedInterceptorCache = {};
            this._lastAjaxActionSource = undefined;
            this._oldSetLocation = undefined;
            this._preventClicks = 0;
        },
        _encodeUrl: function(url, options) {
            if (options.encode) {
                if (options.encode.offset !== undefined) {
                    if (options.encode.length === undefined) {
                        if (options.encode.offset === 0) {
                            return window.encodeURI(url.substr(options.encode.offset));
                        }
                        else {
                            return url.substr(0, options.encode.offset) +
                                window.encodeURI(url.substr(options.encode.offset));
                        }
                    }
                    else if (options.encode.length === 0) {
                        return url;
                    }
                    else {
                        if (options.encode.offset === 0) {
                            return window.encodeURI(url.substr(options.encode.offset, options.encode.length))
                                + url.substr(options.encode.offset + options.encode.length);
                        }
                        else {
                            return url.substr(0, options.encode.offset) +
                                window.encodeURI(url.substr(options.encode.offset, options.encode.length)) +
                                url.substr(options.encode.offset + options.encode.length);
                        }
                    }
                }
                else {
                    return url;
                }
            }
            else {
                return window.encodeURI(url);
            }
        },
        get:function (url, callback, options) {
            var self = this, encodedUrl;
            options = this._before(options, url);
            $.get(this._encodeUrl(url, options))
                .done(function (response) { self._done(response, callback, options, url); })
                .fail(function (error) { self._fail(error, options, url)})
                .complete(function () { self._complete(options, url); });
        },
        post:function (url, data, callback, options) {
            var self = this;
            if (data === undefined) {
                data = [];
            }
            if (callback === undefined) {
                callback = function() {};
            }
            options = this._before(options, url, data);
            $.post(window.encodeURI(url), data)
                .done(function (response) { self._done(response, callback, options, url, data); })
                .fail(function (error) { self._fail(error, options, url, data)})
                .complete(function () { self._complete(options, url, data); });
        },
        update: function(response) {
            if (response.updates) {
                $.each(response.updates, function (selector, html) {
                    $(selector).html(html);
                });
            }
            if (response.blocks) {
                $.each(response.blocks, function (blockName, sectionIndex) {
                    var block = layout.getBlock(blockName);
                    if (block) {
                        block.setContent(response.sections[sectionIndex]);
                    }
                });
            }
            if (response.config) {
                config.set(response.config);
            }
            if (response.script) {
                $.globalEval(response.script);
            }
            if (response.title) {
                document.title = response.title.replace(/&amp;/g, '&');
            }
        },
        getSectionSeparator: function() {
            return "\n91b5970cd70e2353d866806f8003c1cd56646961\n";
        },
        _before: function(options, url, data) {
            var page = layout.getPageBlock();
            options = $.extend({
                showOverlay:page.getShowOverlay(),
                showWait:page.getShowWait(),
                showDebugMessages:page.getShowDebugMessages()
            }, options);

            if (options.showOverlay) {
                page.showOverlay();
            }
            if (options.showWait) {
                page.showWait();
            }
            if (options.preventClicks) {
                this._preventClicks++;
            }
            $(document).trigger('m-ajax-before', [[], url, '', options]);
            return options;
        },
        _done:function (response, callback, options, url, data) {
            var page = layout.getPageBlock();
            if (options.showOverlay) {
                page.hideOverlay();
            }
            if (options.showWait) {
                page.hideWait();
            }
            try {
                var content = response;
                try {
                    var sections = response.split(this.getSectionSeparator());
                    response = sections.shift();
                    response = json.parse(response);
                    response.sections = sections;
                }
                catch (e) {
                    callback(content, { url:url});
                    return;
                }
                if (!response) {
                    if (options.showDebugMessages) {
                        alert('No response.');
                    }
                }
                else if (response.error && !response.customErrorDisplay) {
                    if (options.showDebugMessages) {
                        alert(response.message || response.error);
                    }
                }
                else {
                    callback(response, { url:url, data: data});
                }
            }
            catch (error) {
                if (options.showDebugMessages) {
                    var s = '';
                    if (typeof(error) == 'string') {
                        s += error;
                    }
                    else {
                        s += error.message;
                        if (error.fileName) {
                            s += "\n    in " + error.fileName + " (" + error.lineNumber + ")";
                        }
                    }
                    if (response) {
                        s += "\n\n";
                        s += typeof(response) == 'string' ? response : json.stringify(response);
                    }
                    alert(s);
                }
            }
        },
        _fail:function (error, options, url, data) {
            var page = layout.getPageBlock();
            if (options.showOverlay) {
                page.hideOverlay();
            }
            if (options.showWait) {
                page.hideWait();
            }
            if (options.showDebugMessages) {
                alert(error.status + (error.responseText ? ': ' + error.responseText : ''));
            }
        },
        _complete:function (options, url, data) {
            if (options.preventClicks) {
                this._preventClicks--;
            }
            $(document).trigger('m-ajax-after', [[], url, '', options]);
        },
        addInterceptor: function (interceptor) {
            this._interceptors.push(interceptor);
        },
        removeInterceptor: function (interceptor) {
            var index = this._interceptors.indexOf(interceptor);
            if (index != -1) {
                this._interceptors.splice(index, 1);
            }
        },
        startIntercepting: function() {
            var self = this;

            // intercept browser history changes (Back button clicks, pushing new URL in _callInterceptionCallback() method)
            if (window.History && window.History.enabled) {
                $(window).on('statechange', self._onStateChange = function () {
                    var State = window.History.getState();
                    var url = State.url; // URL encoded
                    if (self._findMatchingInterceptor(url, self._lastAjaxActionSource)) {
                        self._internalCallInterceptionCallback(url, self._lastAjaxActionSource);
                    }
                    else {
                        self._oldSetLocation(url, self._lastAjaxActionSource);
                    }
                });
            }

            // intercept Magento setLocation() calls
            if (window.setLocation) {
                this._oldSetLocation = window.setLocation;
                window.setLocation = function (url, element) {
                    self._callInterceptionCallback(url, element);
                };
            }

            // intercept all link clicks
            $(document).on('click', 'a', self._onClick = function () {
                var url = this.href; // URL encoded
                if (self._preventClicks && url == location.href + '#') {
                    return false;
                }
                if (self._findMatchingInterceptor(url, this)) {
                    return self._callInterceptionCallback(url, this);
                }
                else {
                    return true;
                }
            });
        },
        stopIntercepting: function() {
            if (window.History && window.History.enabled) {
                $(window).off('statechange', self._onStateChange);
                self._onStateChange = null;
            }
            $(document).off('click', 'a', self._onClick);
            self._onClick = null;
        },
        _internalCallInterceptionCallback: function(url, element) {
            var interceptor = this._findMatchingInterceptor(url, element);
            if (interceptor) {
                this.lastUrl = url;
                interceptor.intercept(url, element);
                return false; // prevent default link click behavior
            }
            return true;
        },
        _callInterceptionCallback: function(url, element) {
            if (this._findMatchingInterceptor(url, element)) {
                this._lastAjaxActionSource = element;
                if (window.History && window.History.enabled) {
                    //noinspection JSUnresolvedVariable
                    window.History.pushState(null, window.title, url);
                }
                else {
                    this._internalCallInterceptionCallback(url, element);
                }
            }
            else {
                this._oldSetLocation(url, element);
            }
            return false;
        },
        _findMatchingInterceptor: function(url, element) {
            if (this._matchedInterceptorCache[url] === undefined) {
                var interceptor = false;
                if (config.getData('ajax.enabled')) {
                    $.each(this._interceptors, function(index, candidateInterceptor) {
                        if (candidateInterceptor.match(url, element)) {
                            interceptor = candidateInterceptor;
                            return false;
                        }
                        else {
                            return true;
                        }
                    });
                }
                this._matchedInterceptorCache[url] = interceptor;
            }
            return this._matchedInterceptorCache[url];
        },
        getDocumentUrl: function() {
            if (this.lastUrl) {
                return this.lastUrl;
            }
            else {
                return document.URL;
            }
        }
    });
});
