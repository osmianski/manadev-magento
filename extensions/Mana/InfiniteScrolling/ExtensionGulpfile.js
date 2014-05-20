// Require libraries
var gulp = require('gulp');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat-sourcemap');
var path = require('path');

// these functions are called from main gulp file
module.exports = {
    /**
     * this function is called when we rebuild everything
     */
    tasks: function() {
        var frontendJsDir = this.dir + path.sep + 'frontend/js' + path.sep;
        var src = [
            frontendJsDir + 'src/header.js',
            frontendJsDir + 'src/Loader.js'
        ];

        gulp.task(this.prefix + 'frontend_scripts', function() {
            gulp.src(src)
                .pipe(concat('scripts.js'))
                .pipe(gulp.dest(frontendJsDir));
        });

        gulp.task(this.prefix + 'frontend_min_scripts', [this.prefix + 'frontend_scripts'], function() {
            gulp.src(frontendJsDir + 'scripts.js')
                .pipe(uglify({
                    inSourceMap: 'scripts.js.map',
                    outSourceMap: 'scripts.min.js.map',
                    preserveComments: 'some'
                }))
                .pipe(gulp.dest(frontendJsDir));
        });
    },
    build: function() {
        return [
            this.prefix + 'frontend_min_scripts'
        ];
    }
};