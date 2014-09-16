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
