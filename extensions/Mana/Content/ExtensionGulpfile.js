module.exports = Extension.extend({
    loadTasks: function() {
        this.loadJsTasks('backend', 'backend/js', [
            'src/header.js',
            'src/Folder/ListContainer.js',
            'src/Book/Tree.js',
            'src/Wysiwyg.js',
            'src/Book/TabContainer.js',
            'src/Book/TabContainer/Global.js',
            'src/Book/TabContainer/Store.js'
        ]);
        this.loadJsTasks('frontend', 'frontend/js', [
            'src/header.js',
            'src/Tree.js',
            'src/Tree/Search.js',
            'src/AjaxInterceptor.js'
        ]);
    }
});
