Mana.define('Mana/Content/Book/RelatedProductGrid', ['jquery', 'Mana/Admin/Grid', 'singleton:Mana/Core/Layout', 'singleton:Mana/Core/Ajax'],
function ($, Grid, layout, ajax)
{
    return Grid.extend('Mana/Content/Book/RelatedProductGrid', {
        _subscribeToBlockEvents: function() {
            return this._super()
                .on('load', this, function () {
                    if (this.getChild('add-related-products')) this.getChild('add-related-products').on('click', this, this.openRelatedProductGrid);
                    if (this.getChild('remove-selected')) this.getChild('remove-selected').on('click', this, this.removeSelected);
                })
                .on('unload', this, function () {
                    if (this.getChild('add-related-products')) this.getChild('add-related-products').off('click', this, this.openRelatedProductGrid);
                    if (this.getChild('remove-selected')) this.getChild('remove-selected').off('click', this, this.removeSelected);
                })
        },
        addToRelatedProductChanges: function (ids) {
            this.$tabContainer().initChangesObj().related_products = $.merge(this.$tabContainer().initChangesObj().related_products, ids);
        },
        removeFromRelatedProductChanges: function(ids) {
            var current_ids = this.$tabContainer().initChangesObj().related_products;
            $.each(ids, function(i) {
                var index = current_ids.indexOf(ids[i]);
                if(index !== -1) {
                    current_ids.splice(index, 1);
                } else {
                    if(current_ids.indexOf("-"+ids[i]) === -1) {
                        current_ids.push("-"+ids[i]);
                    }
                }
            });
        },
        removeSelected: function () {
            var rows = this.getRows();
            var ids = [];
            $.each(rows, function(i) {
                if($(rows[i].getCell(0).getElement()).find("input")[0].checked) {
                    ids.push($(rows[i].getCell(1).getElement())[0].innerText);
                }
            });
            if (rows.length > 0) {
                this.removeFromRelatedProductChanges(ids);
                this.$tabContainer()._postAction("modify");
                this._varienGrid.reload();
            }
        },
        openRelatedProductGrid: function () {
            this._updateReloadParams();
            var self = this;
            $.mChooseProducts({
                url: this.$tabContainer().getUrl('related-product-grid-selection') + "?isAjax=true",
                params:function () {
                    return {
                        'changes_related_products': self.$tabContainer().initChangesObj().related_products || {},
                        'id': self.$tabContainer().getCurrentId()
                    };
                },
                result:function (ids) {
                    if(ids) {
                        self.addToRelatedProductChanges(ids);
                        self.$tabContainer()._postAction("modify");
                        self._varienGrid.reload();
                    }
                }
            });

        },
        $tabContainer: function() {
            return layout.getBlock('container');
        },
        _updateReloadParams: function() {
            this._super();
            this._varienGrid.reloadParams.id = this.$tabContainer().getCurrentId();
            this._varienGrid.reloadParams.related_product_ids = this.$tabContainer().initChangesObj().related_products;
        }
    });
});