// Require libraries

var gulp = require('gulp');
//var source = require('vinyl-source-stream');
var _ = require('underscore');
var glob = require("glob");
var path = require('path');
var fs = require('fs');
var xml2js = require('xml2js');
var modules = {};
var buildTasks;
var watchTasks;

function Module(gulpfile) {
}

_.extend(Module.prototype, {
});

// Require Gulpfiles of all the modules
function loadModules() {
    // only load modules once
    if (modules) {
        return;
    }
    modules = {};

    glob("vendor/**/ExtensionGulpfile.js", {sync: true}, function (er, files) {
        _.each(files, function (gulpfile) {
            gulpfile = process.cwd() + path.sep + gulpfile;

            // load module-level gulp file
            var module = require(gulpfile);
            var xml;

            // save module directory
            module.dir = path.dirname(gulpfile);

            // save module name
            var baseDir = path.dirname(path.dirname(module.dir));
            module.name = path.relative(baseDir, module.dir).replace(/\\/, '/');
            module.prefix = module.name.replace(/\//, '_') + '_';

            // load symlink synchronization options
            if (xml = fs.readFileSync(module.dir + path.sep + 'extension.xml')) {
                xml2js.parseString(xml, function (err, options) {
                    if (err) throw err;
                    module.extensionXml = options.config;
                });
            }

            // load Magento module definition
            if (xml = fs.readFileSync(module.dir + path.sep + 'module.xml')) {
                xml2js.parseString(xml, function (err, options) {
                    if (err) throw err;
                    module.moduleXml = options.config;
                });
            }

            modules[module.name] = new Module(gulpfile);
        });
    });
}

loadModules();

_.each(modules, function (module) {
    if (_.isFunction(module.tasks)) {
        module.tasks();
    }
});

// full rebuild of each module
function build() {
    _.each(modules, function(module) {
        if (_.isFunction(module.build)) {
            module.build();
        }
    });
}

// watching files and rebuilding on the fly
function watch() {
}

// these tasks are available from command line:
//      gulp - invoke 'default' task for full rebuild
//      gulp watch - invoke 'watch' task for changed files and rebuilding what is needed

gulp.task('default', build);
gulp.task('watch', watch);