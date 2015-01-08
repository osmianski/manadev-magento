/**
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // for better JS merging



Mana.define('Mana/Sorting/Method/ListContainer', ['jquery', 'Mana/Admin/Container'],
function ($, Container)
{
    return Container.extend('Mana/Sorting/Method/ListContainer', {
        _subscribeToBlockEvents: function () {

            return this
                ._super()
                .on('load', this, function () {
                    if (this.getChild('create-method')) this.getChild('create-method').on('click', this,
                        this.createMethod);
                })
                .on('unload', this, function () {
                    if (this.getChild('create-method')) this.getChild('create-method').off('click', this,
                        this.createMethod);
                });
        },
        createMethod: function () {
            setLocation(this.getUrl('create-method'));
        }

    });
});


Mana.define('Mana/Sorting/Method/TabContainer', ['jquery', 'Mana/Admin/Container', 'singleton:Mana/Core'],
function ($, Container, core) {
    return Container.extend('Mana/Sorting/Method/TabContainer', {
        _subscribeToHtmlEvents: function() {
            var self = this;

            return this._super()
                .on('bind', this, function() {
                })
                .on('unbind', this, function() {
                })
        },
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function () {
                    if (this.getChild('delete')) this.getChild('delete').on('click', this, this.deleteRecord);
                })
                .on('unload', this, function () {
                    if (this.getChild('delete')) this.getChild('delete').off('click', this, this.deleteRecord);
                });
        },
        deleteRecord: function() {
            deleteConfirm(this.getText('delete-confirm'), this.getUrl('delete'));
        }
    });
});

Mana.define('Mana/Sorting/Method/TabContainer/Global',
['jquery', 'Mana/Sorting/Method/TabContainer'],
function ($, TabContainer) {
    return TabContainer.extend('Mana/Sorting/Method/TabContainer/Global', {
    });
});

Mana.define('Mana/Sorting/Method/TabContainer/Store',
['jquery', 'Mana/Sorting/Method/TabContainer'],
function ($, TabContainer) {
    return TabContainer.extend('Mana/Sorting/Method/TabContainer/Store', {
    });
});

//# sourceMappingURL=sorting.js.map