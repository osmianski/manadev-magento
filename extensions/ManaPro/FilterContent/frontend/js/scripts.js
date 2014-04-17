/**
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // make JS merging easier

Mana.define('ManaPro/FilterContent/LayeredDescription', ['jquery', 'Mana/Core/Block'],
function($, Block) {
    return Block.extend('ManaPro/FilterContent/LayeredDescription', {
        _subscribeToBlockEvents: function() {
            return this
                ._super()
                .on('resize', this, this.resize)
        },
        $li: function() {
            return this.$().children('li');
        },
        $backgroundImage: function() {
            return this.$().find('.m-background img');
        },
        resize: function() {
            var width = this.$().width();
            this.$li().width(width);
            this.$backgroundImage().attr('width', width);

            var height = 0;
            this.$li().each(function () {
                var h = $(this).height();
                console.log(h);
                if (height < h) {
                    height = h;
                }
            });
            this.$().height(height);
        }
    });
});
