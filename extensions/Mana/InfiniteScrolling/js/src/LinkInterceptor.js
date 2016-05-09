Mana.require(['jquery', 'singleton:Mana/Core/Layout'], function ($, layout) {
    $(function () {
        var Engine = layout.getBlock('infinitescrolling-engine');
        if(!Engine) {
            return;
        }

        function isElementInViewport(el) {

            //special bonus for those using jQuery
            if (typeof jQuery === "function" && el instanceof jQuery) {
                el = el[0];
            }

            var rect = el.getBoundingClientRect();

            // If 70% of height is visible, consider it in viewport.
            return (rect.bottom / el.getHeight()) * 100 >= 70;
        }

        var selector = Engine.getItemSelector();
        if(Engine.getRecoverScrollProgressOnBack()) {
            $(document).on('click', selector, function(e) {
                var productImageList = $(selector);
                var index = 0;
                productImageList.each(function(i) {
                    if(isElementInViewport(this)) {
                        index = i;
                        return false;
                    }
                });
                location.hash = "index=" + index + "&page=" + Engine.page;
            });

            var currentUrl = location.href;


            var hash = currentUrl.split("#")[1];
            if (hash) {
                var rawDataArr = hash.split("&");
                var data = {};
                rawDataArr.each(function (rawData) {
                    var key = rawData.split("=")[0],
                        value = rawData.split("=")[1];

                    data[key] = value;
                });

                var showMoreButton = $("#m-show-more");
                if (showMoreButton) {
                    showMoreButton.remove();
                }

                window.scrollTo(null, Engine.getProductListBottom());
                Engine.load(data.page, Engine.limit, function () {
                        var m_timer = setInterval(function() {
                            var m_allImagesLoaded = true;
                            $(selector + ' img').each(function() {
                                if (!$(this).height()) {
                                    m_allImagesLoaded = false;
                                }
                            });

                            function getHeightOfFixedElements() {
                                // Default allowance
                                var result = 10;
                                var elements = document.getElementsByTagName('*');
                                for (var i in elements) {
                                    try {
                                        var position = jQuery.css(elements[i], 'position');
                                        if (position == "fixed") {
                                            var height = jQuery.css(elements[i], 'height');
                                            var display = jQuery.css(elements[i], 'display');
                                            var top = jQuery.css(elements[i], 'top');
                                            if(height != "0px" && display != "none" && top == "0px") {
                                                height = height.replace("px", "");
                                                height = parseInt(height);
                                                result = height;
                                                break;
                                            }
                                        }
                                    } catch (err) {
                                    }
                                }
                                return result;
                            }

                            if (m_allImagesLoaded) {
                                clearInterval(m_timer);

                                // DO YOUR STUFF HERE
                                var space = getHeightOfFixedElements();
                                var topPosition = $(selector).eq(data.index).offset().top - space;
                                window.scrollTo(null, topPosition);
                            }

                        }, 100); // timeout to load the images
                }, true);
            }
        }
    });
});
