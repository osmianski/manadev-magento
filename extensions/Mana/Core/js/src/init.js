Mana.require(['jquery', 'singleton:Mana/Core/Layout', 'singleton:Mana/Core/Ajax'], function($, layout, ajax) {
    function _generateBlocks() {
        var vars = layout.beginGeneratingBlocks();
        layout.endGeneratingBlocks(vars);
    }
    $(function() {
        _generateBlocks();
        ajax.startIntercepting();
    });
});
