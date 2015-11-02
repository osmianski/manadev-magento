Mana.require(['jquery', 'singleton:Mana/Core/Layout'], function ($, layout) {
    $(function () {
        var Engine = layout.getBlock('infinitescrolling-engine');
        var selector = ".category-products li." + Engine.getLiClass();
        if(Engine.getRecoverScrollProgressOnBack()) {
            $(document).on('click', selector, function(e) {
                var productImageList = $(selector);
                var index = productImageList.index(productImageList.withinviewport().first());
                if(index == "-1" || index == "0") {
                    index = 0;
                }
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
                    var topPosition = $(selector).eq(data.index).offset().top - 10;
                    window.scrollTo(null, topPosition);
                }, true);
            }
        }
    });
});
