/**
 * @category    Mana
 * @package     ManaPro_Video
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
;(function($){
    $(function () {
        $("#m-video-tabs").tabs({selected:$("#m-video-tabs").first().children().length - 1});
    });

    $(document).on('click', '.m-video-thumbnail, .m-image-thumbnail', function() {
        if ($.options('#m-video').progress) {
            $('#m-wait').show();
        }
        $.get($.options('#m-video').url.replace('__', this.id))
            .done(function (response) {
                try {
                    response = $.parseJSON(response);
                    if (!response) {
                        if ($.options('#m-video').debug) {
                            alert('No response.');
                        }
                    }
                    else if (response.error) {
                        if ($.options('#m-video').debug) {
                            alert(response.error);
                        }
                    }
                    else {
                        var overlay = $('<div class="m-popup-overlay"> </div>');
                        overlay.appendTo(document.body);
                        overlay.css({left:0, top:0}).width($(document).width()).height($(document).height());
                        overlay.animate({ opacity: 0.5 }, 800, function() {
                            $('#m-popup')
                                .html(response.html)
                                .addClass('m-video-popup')
                                .css("top", (($(window).height() - $('#m-popup').outerHeight()) / 2) + $(window).scrollTop() + "px")
                                .css("left", (($(window).width() - $('#m-popup').outerWidth()) / 2) + $(window).scrollLeft() + "px")
                            var css = {
                                left:$('#m-popup').css('left'),
                                top:$('#m-popup').css('top'),
                                width:$('#m-popup').width() + "px",
                                height:$('#m-popup').height() + "px"
                            };
                            $('#m-popup').children().each(function () {
                                $(this).css({
                                    width:$('#m-popup').width() + "px",
                                    height:$('#m-popup').height() + "px"
                                });
                            });
                            $('#m-popup')
                                .css({
                                    top:($(window).height() / 2) + $(window).scrollTop() + "px",
                                    left:($(window).width() / 2) + $(window).scrollLeft() + "px",
                                    width:0 + "px",
                                    height:0 + "px"
                                })
                                .show()
                                .animate(css, 500);
                        });
                    }
                }
                catch (error) {
                    if ($.options('#m-video').debug) {
                        alert(response && typeof(response) == 'string' ? response : error);
                    }
                }
            })
            .fail(function (error) {
                if ($.options('#m-video').debug) {
                    alert(error.status + (error.responseText ? ': ' + error.responseText : ''));
                }
            })
            .complete(function () {
                if ($.options('#m-video').progress) {
                    $('#m-wait').hide();
                }
            });
        return false;
    });
    $(document).on('click', '.m-popup-video-thumbnail, .m-popup-image-thumbnail', function () {
        if (!$('#' + this.id + '-large').is(":visible")) {
            $('.m-video-popup .product-image').fadeOut($.options('#m-video').fadeOut);
            $('#' + this.id + '-large').fadeIn($.options('#m-video').fadeIn);
        }
        return false;
    });
    function _previous () {
        var current = $('.m-video-popup .product-image:visible');
        current.fadeOut($.options('#m-video').fadeOut);
        if (current.prev().length) {
            current.prev().fadeIn($.options('#m-video').fadeIn);
        }
        else {
            current.parent().children().last().fadeIn($.options('#m-video').fadeIn);
        }
        return false;
    }

    function _next() {
        var current = $('.m-video-popup .product-image:visible');
        current.fadeOut($.options('#m-video').fadeOut);
        if (current.next().length) {
            current.next().fadeIn($.options('#m-video').fadeIn);
        }
        else {
            current.parent().children().first().fadeIn($.options('#m-video').fadeIn);
        }
        return false;
    }
    function _close() {
        $('.m-popup-overlay').fadeOut(500, function () {
            $('.m-video-popup .product-image iframe.m-vimeo').each(function() {
                var player = $f(this);
                player.api('pause');

            });
            $('.m-popup-overlay').remove();
            $('#m-popup').fadeOut(1000);
        });
        return false;
    }

    $(document).on('click', '.m-video-popup .m-btn-previous', _previous);
    $(document).on('click', '.m-video-popup .m-btn-next', _next);

    $(document).on('click', '.m-video-popup .m-btn-close', _close);
    $(document).keydown(function (e) {
        if ($('.m-popup-overlay').length) {
            if (e.keyCode == 37) {
                return _previous();
            }
            else if (e.keyCode == 39) {
                return _next();
            }
        }
    });
})(jQuery);