// Require libraries

var gulp = require('gulp');
//var source = require('vinyl-source-stream');
var _ = require('underscore');
var Backbone = require('backbone');
var glob = require("glob");
var path = require('path');
var fs = require('fs');
var xml2js = require('xml2js');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var concat = require('gulp-concat-sourcemap');
var gulpPrint = require('gulp-print');
var watch = require('gulp-watch');

function print() {
    return gulpPrint(function(filepath) {
        var d = new Date();
        return '' + d.getHours() + ':' + d.getMinutes() + ': ' + filepath;
    });
}
/**
 * Gulp application which orchestrates the whole build/watch process
 * @constructor
 */
function App() {
    this.dir = process.cwd();
    this.extensions = {};
    this.buildTasks = [];
    this.watchTasks = [];
}

// borrow class inheritance implementation for module class from Backbone Model class.
App.extend = Backbone.Model.extend;

_.extend(App.prototype, {
    loadModules: function() {
        var self = this;
        glob("vendor/**/ExtensionGulpfile.js", {sync: true}, function (er, files) {
            _.each(files, function (gulpfile) {
                gulpfile = self.dir + path.sep + gulpfile;
                var extensionClass = require(gulpfile);
                var extension = new extensionClass(self, gulpfile);
                self.extensions[extension.name] = extension;
            });
        });
    },
    loadTasks: function() {
        _.each(this.extensions, function (extension) {
            extension.loadTasks();
        });
    }
});

/**
 * Extension gulp model which provides extension specific build and watch tasks.
 * @constructor
 * @param {App} app Gulp application reference
 * @param {string} gulpfile absolute file path module specific gulpfile
 */
function Extension(app, gulpfile) {
    var self = this;
    var xml;

    this.app = app;
    this.gulpfile = gulpfile;

    // save module directory
    this.dir = path.dirname(this.gulpfile);

    // save module name
    var baseDir = path.dirname(path.dirname(this.dir));
    this.name = path.relative(baseDir, this.dir).replace(/\\/, '/');
    var nameParts = this.name.split('/');
    this.filenameTranslations = [
        { regex: /\{Extension\/Name\}/g, replacement: nameParts[0] + '/' + nameParts[1]},
        { regex: /\{Extension_Name\}/g, replacement: nameParts[0] + '_' + nameParts[1]},
        { regex: /\{extension\/name\}/g, replacement: nameParts[0].toLowerCase() + '/' + nameParts[1].toLowerCase()},
        { regex: /\{extension_name\}/g, replacement: nameParts[0].toLowerCase() + '_' + nameParts[1].toLowerCase()}
    ];
    this.prefix = this.name.replace(/\//g, '_') + '_';

    // load symlink synchronization options
    if (xml = fs.readFileSync(this.dir + path.sep + 'extension.xml')) {
        xml2js.parseString(xml, function (err, options) {
            if (err) throw err;
            self.extensionXml = options.config;
        });
    }

    // load Magento module definition
    if (xml = fs.readFileSync(this.dir + path.sep + 'module.xml')) {
        xml2js.parseString(xml, function (err, options) {
            if (err) throw err;
            self.moduleXml = options.config;
        });
    }
}

// borrow class inheritance implementation for module class from Backbone Model class.
Extension.extend = Backbone.Model.extend;

_.extend(Extension.prototype, {
    /**
     * Override this method in extensions to load extension specific tasks
     */
    loadTasks: function() {
    },
    /**
     * Call this method to register all tasks needed to build specified sourceFiles located in
     * specified directory
     * @param taskName
     * @param dir
     * @param sourceFiles
     */
    loadJsTasks: function(taskName, dir, sourceFiles) {
        var self = this;

        // gulp task names
        var buildTaskName = this.prefix + 'build_' + taskName + '_scripts';
        var buildMinTaskName = this.prefix + 'build_' + taskName + '_min_scripts';
        var watchTaskName = this.prefix + 'watch_' + taskName + '_scripts';

        var frontendJsDir = this.dir + path.sep + dir + path.sep;
        var scriptFilename = self.getTargetFilename(frontendJsDir + 'scripts.js');
//        var minScriptFilename = self.getTargetFilename(frontendJsDir + 'scripts.min.js');
//        console.log(this.dir);
//        console.log(dir);
//        console.log(scriptFilename);
//        console.log('%j', sourceFiles);
        dir = path.dirname(scriptFilename);
//        console.log(dir);
        var src = [];
        _.each(sourceFiles, function(sourceFile) {
            src.push(self.getTargetFilename(frontendJsDir + sourceFile));
        });

        gulp.task(buildTaskName, function() {
            return gulp.src(src)
                .pipe(print())
                .pipe(concat(path.basename(scriptFilename), {
                    prefix: dir.split('/').length - 1,
                    sourceRoot: '../'
                }))
                .pipe(gulp.dest(dir));
        });

        gulp.task(buildMinTaskName, [buildTaskName], function() {
            return gulp.src(scriptFilename)
                .pipe(print())
                .pipe(rename({suffix: '.min'}))
                .pipe(uglify({
                    //inSourceMap: scriptFilename + '.map',
                    //outSourceMap: minScriptFilename + '.map',
                    preserveComments: 'some'
                }))
                .pipe(gulp.dest(dir));
        });

        gulp.task(watchTaskName, function () {
            gulp.watch(src, [buildMinTaskName]);
        });


        this.app.buildTasks.push(buildMinTaskName);
        this.app.watchTasks.push(watchTaskName);
    },
    getTargetFilename: function(filename) {
        var self = this;

        var replacedFilename = path.relative(this.dir, filename).replace(/\\/g, '/');
        var replaced = false;
        if (this.extensionXml.sync) {
            _.find(this.extensionXml.sync, function(sync) {
                if (sync.$) {
                    if (sync.$['extension-dir'] && replacedFilename.indexOf(sync.$['extension-dir']) === 0) {
                        replacedFilename = sync.$['project-dir'] + replacedFilename.substr(sync.$['extension-dir'].length);
                        replacedFilename = self.translateFilename(replacedFilename);
                        replaced = true;
                        return true;
                    }
                    else if (sync.$['extension-file'] === replacedFilename) {
                        replacedFilename = sync.$['project-file'];
                        replacedFilename = self.translateFilename(replacedFilename);
                        replaced = true;
                        return true;
                    }
                }
                return false;
            });
        }
        if (!replaced) {
            replacedFilename = filename;
        }
        return replacedFilename;
    },
    translateFilename: function (filename) {
        _.each(this.filenameTranslations, function(translation) {
            filename = filename.replace(translation.regex, translation.replacement);
        });
        return filename;
    }
});
global.Extension = Extension;

// initialize Gulp application - load all tasks from all extension gulpfiles
var app = new App();
app.loadModules();
app.loadTasks();

// these tasks are available from command line:
//      gulp - invoke 'default' task for full rebuild
//      gulp watch - invoke 'watch' task for changed files and rebuilding what is needed

gulp.task('default', app.buildTasks);
gulp.task('watch', app.watchTasks);

module.exports = gulp;