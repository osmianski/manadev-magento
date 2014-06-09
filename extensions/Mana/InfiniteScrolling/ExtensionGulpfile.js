module.exports = Extension.extend({
    loadTasks: function() {
        this.loadJsTasks('global', 'js', [
            'src/header.js',
            'src/Engine.js',
            'src/ModeHandler.js'
        ]);
        this.loadJsTasks('frontend', 'frontend/js', [
            'src/header.js',
            'src/ListMode.js',
            'src/GridMode.js',
            'src/ResponsiveGridMode.js'
        ]);
    }
});
