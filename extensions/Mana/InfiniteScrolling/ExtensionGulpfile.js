module.exports = Extension.extend({
    loadTasks: function() {
        this.loadJsTasks('frontend', 'frontend/js', [
            'src/header.js',
            'src/Engine.js'
        ]);
    }
});
