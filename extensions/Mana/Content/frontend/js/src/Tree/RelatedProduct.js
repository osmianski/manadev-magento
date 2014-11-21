Mana.define('Mana/Content/Tree/RelatedProduct', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Content/Filter'],
function ($, Block, filter) {
    return Block.extend('Mana/Content/Tree/RelatedProduct', {
        setFilterUrl: function (link) {
            var productId = link.data('mProductId');
            if(filter.isProductChecked(productId)) {
                link.addClass('m-checkbox-checked');
            } else {
                link.addClass('m-checkbox-unchecked');
            }
            link[0].href = link.hasClass('m-checkbox-unchecked') ? filter.getUrlIfRelatedProductChecked(productId) : filter.getUrlIfRelatedProductUnchecked(productId);
        },
        _subscribeToBlockEvents: function () {
            var self = this;

            return this
                ._super()
                .on('load', this, function () {
                    this.$().find('a').each(function(){
                        self.setFilterUrl($(this));
                    });
                })
        }
    });
});