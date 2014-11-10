Mana.define('Mana/Content/Book/RelatedProductGrid', ['jquery', 'Mana/Core/Block'],
function ($, Block, json)
{
    return Block.extend('Mana/Content/Book/RelatedProductGrid', {
        _subscribeToBlockEvents: function() {
            return this._super()
                .on('load', this, function () {

                })
                .on('unload', this, function () {

                })
        }
    });
});