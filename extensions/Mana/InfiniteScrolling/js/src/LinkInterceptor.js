Mana.require(['jquery', 'singleton:Mana/Core/Layout'], function ($, layout) {
    $(function () {
        var Engine = layout.getBlock('infinitescrolling-engine');
        if(Engine.getRecoverScrollProgressOnBack()) {
            $(document).on('click', "a.product-image, .product-name a", function(e) {
                var productImageList = $("a.product-image");
                var index = productImageList.index(productImageList.withinviewport().first());
                if(index == "-1" || index == "0") {
                    return;
                }
                location.hash = "index=" + index;
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
                    Engine.isShowMoreButtonVisible = false;
                }

                var page = Math.floor(data.index / Engine.limit);
                window.scrollTo(null, Engine.getProductListBottom());
                Engine.load(page + 1, Engine.limit, function () {
                    var topPosition = $("a.product-image").eq(data.index).offset().top - 10;
                    window.scrollTo(null, topPosition);
                    window.history.back();
                });
            }
        }
    });
});
