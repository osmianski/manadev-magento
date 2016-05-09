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

                            if (m_allImagesLoaded) {
                                clearInterval(m_timer);

                                // DO YOUR STUFF HERE

                                var topPosition = $(selector).eq(data.index).offset().top - 10;
                                window.scrollTo(null, topPosition);
                            }

                        }, 100); // timeout to load the images
                }, true);
            }
        }
    });
});
