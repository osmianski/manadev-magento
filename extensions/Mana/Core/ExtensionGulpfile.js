module.exports = Extension.extend({
    loadTasks: function() {
        this.loadJsTasks('global', 'js', [
            'src/header.js',
            'src/Mana.js',
            'src/Object.js',
            'src/Core.js',
            'src/Config.js',
            'src/Json.js',
            'src/Utf8.js',
            'src/Base64.js',
            'src/UrlTemplate.js',
            'src/StringTemplate.js',
            'src/Layout.js',
            'src/Ajax.js',
            'src/Block.js',
            'src/PopupBlock.js',
            'src/PageBlock.js',
            'src/init.js',
            'src/rwd.js',
            'src/obsolete.js'
        ]);
    }
});
