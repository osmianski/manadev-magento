var Mana = Mana || {};

(function($, $p, undefined) {


    $.extend(Mana, {
        _singletons: {},
        _defines: { jquery: $, prototype: $p },

        /**
         * Defines JavaScript class/module
         * @param name class/module name
         * @param dependencies
         * @param callback
         */
        define:function (name, dependencies, callback) {
            var resolved = Mana._resolveDependencyNames(dependencies);
            return Mana._define(name, resolved.names, function() {
                return callback.apply(this, Mana._resolveDependencies(arguments, resolved.deps));
            });
        },

        require:function(dependencies, callback) {
            var resolved = Mana._resolveDependencyNames(dependencies);
            return Mana._define(null, resolved.names, function () {
                return callback.apply(this, Mana._resolveDependencies(arguments, resolved.deps));
            });
        },

        _define: function(name, deps, callback) {
            var args = [];
            $.each(deps, function(index, dep) {
                args.push(Mana._resolveDefine(dep));
            });

            var result = callback.apply(this, args);
            if (name) {
                Mana._defines[name] = result;
            }

            return result;
        },
        _resolveDefine: function(name) {
            if (Mana._defines[name] === undefined) {
                console.warn("'" + name + "' is not defined");
            }
            return Mana._defines[name];
        },

        requireOptional: function (dependencies, callback) {
            var resolved = Mana._resolveDependencyNames(dependencies);
            return Mana._define(null, resolved.names, function () {
                return callback.apply(this, Mana._resolveDependencies(arguments, resolved.deps));
            });
//            var resolved = Mana._resolveDependencyNames(dependencies);
//            var args = [];
//            var argResolved = [];
//            function _finishRequire() {
//                var allResolved = true;
//                $.each(argResolved, function(index, isResolved) {
//                    if (!isResolved) {
//                        allResolved = false;
//                        return false;
//                    }
//                    else {
//                        return true;
//                    }
//                });
//                if (allResolved) {
//                    return callback.apply(this, Mana._resolveDependencies(args, resolved.deps));
//                }
//            }
//            $.each(resolved.names, function () {
//                args.push(undefined);
//                argResolved.push(false);
//            });
//            $.each(resolved.names, function(index, name) {
//                require([name], function(arg) {
//                    args[index] = arg;
//                    argResolved[index] = true;
//                    return _finishRequire.apply(this);
//                }, function() {
//                    argResolved[index] = true;
//                    return _finishRequire.apply(this);
//                });
//            });
        },

        _resolveDependencyNames: function(dependencies) {
            var depNames = [];
            var deps = [];
            $.each(dependencies, function (index, dependency) {
                var pos = dependency.indexOf(':');
                var dep = { name:dependency, resolver:'' };
                if (pos != -1) {
                    dep = { name:dependency.substr(pos + 1), resolver:dependency.substr(0, pos) };
                }

                Mana._resolveDependencyName(dep);

                depNames.push(dep.name);
                deps.push(dep);
            });

            return { names: depNames, deps: deps};
        },

        _resolveDependencies:function (args, deps) {
            $.each(args, function (index, arg) {
                args[index] = Mana._resolveDependency(deps[index], arg);
            });

            return args;
        },

        _resolveDependencyName: function(dep) {
        },

        _resolveDependency:function (dep, value) {
            if (value !== undefined) {
                switch (dep.resolver) {
                    case 'singleton':
                        if (Mana._singletons[dep.name] === undefined) {
                            Mana._singletons[dep.name] = new value();
                        }
                        return Mana._singletons[dep.name];
                }
            }
            return value;
        }

    });
})(jQuery, $);

