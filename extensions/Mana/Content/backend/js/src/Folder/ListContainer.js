Mana.define('Mana/Content/Folder/ListContainer', ['jquery', 'Mana/Admin/Container'],
function ($, Container)
{
    return Container.extend('Mana/Content/Folder/ListContainer', {
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function () {
                    if (this.getChild('create-book')) this.getChild('create-book').on('click', this,
                        this.createBook);
                    if (this.getChild('create-feed')) this.getChild('create-feed').on('click', this,
                        this.createList);
                })
                .on('unload', this, function () {
                    if (this.getChild('create-book')) this.getChild('create-book').off('click', this,
                        this.createFeed);
                    if (this.getChild('create-feed')) this.getChild('create-feed').off('click', this,
                        this.createList);
                });
        },
        createBook: function () {
            setLocation(this.getUrl('create-book'));
        },
        createFeed: function () {
            setLocation(this.getUrl('create-feed'));
        }
    });
});
