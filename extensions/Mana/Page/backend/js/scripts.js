/**
 * @category    Mana
 * @package     Mana_Page
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // for better JS merging



Mana.define('Mana/Page/Special/ListContainer', ['jquery', 'Mana/Admin/Container'],
function ($, Container)
{
    return Container.extend('Mana/Page/Special/ListContainer', {
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


Mana.define('Mana/Page/Special/FormContainer', ['jquery', 'Mana/Admin/Container'],
function ($, Container)
{
    return Container.extend('Mana/Page/Special/FormContainer', {
    });
});


Mana.define('Mana/Page/Special/FormContainer/Global', ['jquery', 'Mana/Page/Special/FormContainer'],
function ($, FormContainer)
{
    return FormContainer.extend('Mana/Page/Special/FormContainer/Global', {
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function () {
                    if (this.getChild('delete')) this.getChild('delete').on('click', this, this.deleteClick);
                })
                .on('unload', this, function () {
                    if (this.getChild('delete')) this.getChild('delete').off('click', this, this.deleteClick);
                });
        },
        deleteClick: function () {
            deleteConfirm(this.getText('delete-confirm'), this.getUrl('delete'));
        }
    });
});


Mana.define('Mana/Page/Special/FormContainer/Store', ['jquery', 'Mana/Page/Special/FormContainer'],
function ($, FormContainer)
{
    return FormContainer.extend('Mana/Page/Special/FormContainer/Store', {
    });
});


//# sourceMappingURL=page.js.map