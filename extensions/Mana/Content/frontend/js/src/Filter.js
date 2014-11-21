Mana.define('Mana/Content/Filter', ['jquery', 'singleton:Mana/Core/Config'],
function ($, config) {
    return Mana.Object.extend('Mana/Content/Filter', {
        _init: function(){
            this.loadFilterFromUrl();
        },
        loadFilterFromUrl: function () {
            this._searchValue = "";
            this._relatedProducts = [];
            var queryString = window.location.search;
            queryString = queryString.substr(1, queryString.length - 1);
            queryString = queryString.split('&');
            var param;
            for(var i = 0; i <queryString.length; i++) {
                param = queryString[i].split('=');
                switch(param[0]) {
                    case 'search':
                        this._searchValue = param[1];
                        break;
                    case 'related_products':
                        this._relatedProducts = param[1].split(',').map(Number);
                        break;
                }
            }
        },
        setSearch: function(searchValue) {
            this._searchValue = searchValue;
            return this.constructUrl();
        },
        getUrlIfRelatedProductChecked: function(productId) {
            var url = this.addToRelatedProducts(productId);
            this.removeFromRelatedProduct(productId);
            return url;
        },
        getUrlIfRelatedProductUnchecked: function(productId) {
            var url = this.removeFromRelatedProduct(productId);
            this.addToRelatedProducts(productId);
            return url;
        },
        setRelatedProducts: function(productIds) {
            this._relatedProducts = productIds;
            return this.constructUrl();
        },
        addToRelatedProducts: function(productId) {
            if(!this.isProductChecked(productId)) {
                this._relatedProducts.push(productId);
            }
            return this.constructUrl();
        },
        removeFromRelatedProduct: function(productId) {
            var self = this;
            $.each(this._relatedProducts, function(i) {
                if(self._relatedProducts[i] == productId) {
                    self._relatedProducts.splice(i, 1);
                    return;
                }
            });
            return this.constructUrl();
        },
        isProductChecked: function (productId) {
            return $.inArray(productId, this._relatedProducts) !== -1;
        },
        constructUrl: function() {
            var url = config.getData('url.unfiltered');
            var params = {};
            if (this._searchValue.length > 0) {
                params.search = this._searchValue;
            }
            if(this._relatedProducts.length > 0) {
                params.related_products = this._relatedProducts;
            }
            if(params.search || params.related_products) {
                url += "?";
                var skipAnd = true;
                $.each(params, function(i) {
                    if(skipAnd) {
                        skipAnd = false;
                    } else {
                        url += "&";
                    }
                    url += i + "=" + params[i];
                });
            }
            return url;
        }
    });
});