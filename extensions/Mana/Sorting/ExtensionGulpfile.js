module.exports = Extension.extend({
    loadTasks: function() {
        this.loadJsTasks('backend', 'backend/js', [
            'src/header.js',
            'src/Method/ListContainer.js',
            'src/Method/TabContainer.js',
            'src/Method/TabContainer/Global.js',
            'src/Method/TabContainer/Store.js'
        ]);
    }
});
