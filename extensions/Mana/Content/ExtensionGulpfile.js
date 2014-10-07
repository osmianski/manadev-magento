module.exports = Extension.extend({
    loadTasks: function() {
        this.loadJsTasks('backend', 'backend/js', [
            'src/header.js',
            'src/Folder/ListContainer.js',
            'src/Book/Tree.js'
        ]);
    }
});
