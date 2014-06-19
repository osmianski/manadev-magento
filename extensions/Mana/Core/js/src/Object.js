/* Simple JavaScript Inheritance
 * By John Resig http://ejohn.org/
 * MIT Licensed.
 */
// Inspired by base2 and Prototype
var m_object_initializing = false;
(function (undefined) {
    var fnTest = /xyz/.test(function () { xyz;}) ? /\b_super\b/ : /.*/;

    // The base Class implementation (does nothing)
    Mana.Object = function () {
    };

    // Create a new Class that inherits from this class
    Mana.Object.extend = function (className, prop) {
        if (prop === undefined) {
            prop = className;
            className = undefined;
        }
        var _super = this.prototype;

        // Instantiate a base class (but only create the instance,
        // don't run the init constructor)
        m_object_initializing = true;
        var prototype = new this();
        m_object_initializing = false;

        // Copy the properties over onto the new prototype
        for (var name in prop) {
            // Check if we're overwriting an existing function
            //noinspection JSUnfilteredForInLoop
            prototype[name] = typeof prop[name] == "function" &&
                typeof _super[name] == "function" && fnTest.test(prop[name]) ?
                (function (name, fn) {
                    return function () {
                        var tmp = this._super;

                        // Add a new ._super() method that is the same method
                        // but on the super-class
                        //noinspection JSUnfilteredForInLoop
                        this._super = _super[name];

                        // The method only need to be bound temporarily, so we
                        // remove it when we're done executing
                        var ret = fn.apply(this, arguments);
                        this._super = tmp;

                        return ret;
                    };
                })(name, prop[name]) :
                prop[name];
        }

        // The dummy class constructor
        var Object;
        if (className === undefined) {
            // All construction is actually done in the init method
            Object = function Object() { if (!m_object_initializing && this._init) this._init.apply(this, arguments); };
        }
        else {
            // give constructor a meaningful name for easier debugging
            eval("Object = function " + className.replace(/\//g, '_') + "() { if (!m_object_initializing && this._init) this._init.apply(this, arguments); };");
        }

        // Populate our constructed prototype object
        Object.prototype = prototype;

        // Enforce the constructor to be what we expect
        Object.prototype.constructor = Object;

        // And make this class extendable
        Object.extend = arguments.callee;

        return Object;
    };
})();
