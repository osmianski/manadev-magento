Mana.define('Mana/Content/Tree', ['jquery', 'Mana/Core/Block'],
function ($, Block) {
    return Block.extend('Mana/Content/Tree', {
        setCustomContent: function(content) {
            this.setContent(content);
        }
    });
});