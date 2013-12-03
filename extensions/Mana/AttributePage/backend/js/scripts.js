/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // make JS merging easier

Mana.define('Mana/AttributePage/AttributePage/ListContainer', ['jquery', 'Mana/Admin/Container'],
function ($, Container)
{
    return Container.extend('Mana/AttributePage/AttributePage/ListContainer', {
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function () {
                    if (this.getChild('create')) this.getChild('create').on('click', this, this.create);
                })
                .on('unload', this, function () {
                    if (this.getChild('create')) this.getChild('create').off('click', this, this.create);
                });
        },
        create: function () {
            setLocation(this.getUrl('create'));
        }
    });
});
