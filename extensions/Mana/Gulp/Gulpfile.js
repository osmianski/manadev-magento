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

/**
 * Gulp application which orchestrates the whole build/watch process
 * @constructor
 */
function App() {
    this.dir = process.cwd();
    this.modules = {};
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
                var moduleClass = require(gulpfile);
                var module = new moduleClass(self, gulpfile);
                self.modules[module.name] = module;
            });
        });
    },
    loadTasks: function() {
        _.each(this.modules, function (module) {
            module.loadTasks();
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
        var scriptFilename = self.getTargetFilename(this.dir + path.sep + dir + path.sep + 'scripts.js');
        dir = path.dirname(scriptFilename);
//        var frontendJsDir = this.dir + path.sep + dir + path.sep;
//        var src = [];
//        _.each(sourceFiles, function(sourceFile) {
//            src.push(self.getTargetFilename(frontendJsDir + sourceFile));
//        });

        gulp.task(this.prefix + taskName + '_scripts', function() {
            return gulp.src(sourceFiles, { cwd: dir })
                .pipe(concat(path.basename(scriptFilename), { prefix: 5 }));
        });

//        gulp.task(this.prefix + taskName + '_min_scripts', [this.prefix + taskName + '_scripts'], function() {
//            gulp.src(frontendJsDir + 'scripts.js')
//                .pipe(rename({suffix: '.min'}))
//                .pipe(uglify({
//                    inSourceMap: frontendJsDir + 'scripts.js.map',
//                    outSourceMap: 'scripts.min.js.map',
//                    preserveComments: 'some'
//                }))
//                .pipe(gulp.dest(frontendJsDir));
//        });

//        this.app.buildTasks.push(this.prefix + taskName + '_min_scripts');
        this.app.buildTasks.push(this.prefix + taskName + '_scripts');
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