/**
 * @category    Mana
 * @package     Mana_InfiniteScrolling
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * the following function wraps code block that is executed once this javascript file is parsed. Lierally, this
 * notation says: here we define some anonymous function and call it once during file parsing. THis function has
 * one parameter which is initialized with global jQuery object. Why use such complex notation:
 *         a.     all variables defined inside of the function belong to function's local scope, that is these variables
 *            would not interfere with other global variables.
 *        b.    we use jQuery $ notation in not conflicting way (along with prototype, ext, etc.)
 */
;(function ($) {
    var _options;
    var _pages = {
        visible: 1,
        buffered: 0,
        requested: 0
    };
    var _cachedContentBounds = null;
    var _pageHeight;
    var _buffer = [];
    var _fetching = false;
    var _fetchCancelled = false;
    var _renderAsap = false;

    function _initOptions() {
        _options = $.extend({
            content:'.products-grid', /* all dynamic content is added after these elements */
            progressTemplate:'.m-infinite-scrolling-templates .please-wait-template',
            bufferSize: 1, /* in pages */
            totalPages: 5, /* passed from server */
            url: '',
            progress: true,
            debug: true,
            bufferThreshold:50, /* percent of original content height. If scrolled below this, starts loading pages into buffer */
            displayThreshold:80, /* percent of original content height. If scrolled below this, displays the next page */
            updateKey: 'mb-product-list'
        }, $.options('#m-infinite-scrolling-options'));
    }

    function _init() {
        _initOptions();
        if (_fetching) {
            _fetchCancelled = true;
        }
    }
    function _contentBounds() {
        if (!_cachedContentBounds) {
            _cachedContentBounds = {
                top: 0,
                bottom: 0
            };
            $(_options.content).each(function() {
                    var top = $(this).offset().top, bottom = top + $(this).height();

                if (!_cachedContentBounds.top || top < _cachedContentBounds.top) {
                    _cachedContentBounds.top = top;
                }
                if (!_cachedContentBounds.bottom || bottom > _cachedContentBounds.bottom) {
                    _cachedContentBounds.bottom = bottom;
                }
            });
            _cachedContentBounds.height = _cachedContentBounds.bottom - _cachedContentBounds.top;
            if (!_pageHeight) {
                _pageHeight = _cachedContentBounds.height;
            }
            _cachedContentBounds.pageHeight = _pageHeight;
        }
        return _cachedContentBounds;
    }
    function _bottomStatus() {
        var visibleBottom = $(window).scrollTop() + $(window).height();
        var content = _contentBounds();

        return {
            near: content.bottom - content.pageHeight * (100 - _options.bufferThreshold) / 100 <= visibleBottom,
            passed: content.bottom - content.pageHeight * (100 - _options.displayThreshold) / 100 <= visibleBottom
        }
    }

    function _showProgress() {
        if (_options.progress && !$(_options.content).find('.m-infinite-scrolling-please-wait-container').length) {
            $(_options.content).last().after(
                '<div class="m-infinite-scrolling-please-wait-container">' +
                    $(_options.progressTemplate).html() + '</div>');
        }
    }
    function _hideProgress() {
        $(_options.content).find('.m-infinite-scrolling-please-wait-container').remove();
    }

    function _fetchPages() {
        if (!_pages.requested) {
            return;
        }
        if (_options.debug) {
            console.log('start fetching');
        }
        _fetching = true;
        var fromPage = _pages.visible + _pages.buffered; // zero based
        var unfetchedPages = _options.totalPages - fromPage;
        $.mGetBlocksHtml(_options.url.replace('__0__', fromPage).replace('__1__', _pages.requested >= unfetchedPages ? _pages.requested : unfetchedPages), 'scroll', [], function (response) {
            if (_fetchCancelled) {
                _fetchCancelled = _renderAsap = false;
                return;
            }
            _appendToBuffer(response.update[_options.updateKey]);
            if (_renderAsap) {
                _pages.buffered--;
                _pages.visible++;
                _renderFromBuffer();
            }

            if (response.options) {
                $.options(response.options);
                _initOptions();
            }
            if (response.script) {
                $.globalEval(response.script);
            }
            if (response.title) {
                document.title = response.title;
            }
        });
    }

    function _appendToBuffer(html) {
        jQuery.each(html, function() {
            var text = '';
            $('<div>').append(this).find(_options.content).each(function() {
                text += $('<div>').append(this).html();
            });
            _buffer.push(text);
        });
    }
    function _renderFromBuffer() {
        $(_options.content).last().after(_buffer.shift());
    }

    function _scroll() {

        if (_pages.visible >= _options.totalPages) {
            return; // everything is already fetched
        }
        var bottom = _bottomStatus();
        if (bottom.passed) { // we need 1 page urgent
            if (_options.debug) {
                console.log('we need 1 page urgent');
            }
            if (_pages.buffered) {
                if (_options.debug) {
                    console.log('found in buffer - using that');
                }
                _pages.buffered--;
                _pages.visible++;
                _renderFromBuffer();
            }
            else {
                _showProgress();
                _renderAsap = true;
            }
        }
        if ((bottom.passed || bottom.near) && !_pages.requested) {
            _pages.requested = _options.bufferSize - _pages.buffered;
            _fetchPages();
        }
    }

    $(function() {
        _init();
        $(document).bind('m-ajax-after', _init);
        $(window).bind('scroll', _scroll);
    });
})(jQuery);
