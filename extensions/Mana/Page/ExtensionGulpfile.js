module.exports = Extension.extend({
    loadTasks: function() {
        this.loadJsTasks('backend', 'backend/js', [
            'src/header.js',
            'src/Special/ListContainer.js',
            'src/Special/FormContainer.js',
            'src/Special/FormContainer/Global.js',
            'src/Special/FormContainer/Store.js'
        ]);
    }
});
