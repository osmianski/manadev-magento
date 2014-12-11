module.exports = Extension.extend({
    loadTasks: function() {
        this.loadJsTasks('backend', 'backend/js', [
            'src/header.js',
            'src/Book/RelatedProductGrid.js'
        ]);
        this.loadJsTasks('frontend', 'frontend/js', [
            'src/header.js',
            'src/Filter.js',
            'src/AjaxInterceptor.js',
            'src/Tree/Search.js',
            'src/Tree/RelatedProduct.js',
            'src/Tree/Tag.js'
        ]);
    }
});
