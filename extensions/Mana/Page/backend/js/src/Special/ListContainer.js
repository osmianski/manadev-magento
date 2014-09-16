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
