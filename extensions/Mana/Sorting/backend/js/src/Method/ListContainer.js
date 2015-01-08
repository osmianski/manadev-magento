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
