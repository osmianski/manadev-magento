/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // make JS merging easier



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


Mana.define('Mana/Core', ['jquery'], function ($) {
    return Mana.Object.extend('Mana/Core', {
        getClasses: function(element) { 
            return element.className && element.className.split ? element.className.split(/\s+/) : [];
        },
        getPrefixedClass: function(element, prefix) {
            var result = '';
            //noinspection FunctionWithInconsistentReturnsJS
            $.each(this.getClasses(element), function(key, value) {
                if (value.indexOf(prefix) == 0) {
                    result = value.substr(prefix.length);
                    return false;
                }
            });

            return result;
        },
        // Array Remove - By John Resig (MIT Licensed)
        arrayRemove:function (array, from, to) {
            var rest = array.slice((to || from) + 1 || array.length);
            array.length = from < 0 ? array.length + from : from;
            return array.push.apply(array, rest);
        },
        getBlockAlias: function(parentId, childId) {
            var pos;
            if ((pos = childId.indexOf(parentId + '-')) === 0) {
                return childId.substr((parentId + '-').length);
            }
            else {
                return childId;
            }
        },
        count: function(obj) {
            var result = 0;
            $.each(obj, function() {
                result++;
            });
            return result;
        },
        // from underscore.js
        isFunction: function(obj) {
            return !!(obj && obj.constructor && obj.call && obj.apply);
        },
        isString: function (obj) {
            return Object.prototype.toString.call(obj) == '[object String]';
        }
    });
});


Mana.define('Mana/Core/Config', ['jquery'], function ($) {
    return Mana.Object.extend('Mana/Core/Config', {
        _init: function () {
            this._data = {
                debug: false,
                showOverlay: true,
                showWait: true
            };
        },
        getData: function (key) {
            return this._data[key];
        },
        setData: function (key, value) {
            this._data[key] = value;
            return this;
        },
        set: function (data) {
            $.extend(this._data, data);
            return this;
        },
        getBaseUrl: function (url) {
            return url.indexOf(this.getData('url.base')) == 0
                ? this.getData('url.base')
                : this.getData('url.secureBase');
        },

    });
});


Mana.define('Mana/Core/Json', ['jquery', 'singleton:Mana/Core'], function ($, core) {
    return Mana.Object.extend('Mana/Core/Json', {
        parse: function (what) {
            return $.parseJSON(what);
        },
        stringify: function (what) {
            return Object.toJSON(what);
        },
        decodeAttribute: function (what) {
            if (core.isString(what)) {
                var encoded = what.split("\"");
                var decoded = [];
                $.each(encoded, function (key, value) {
                    decoded.push(value.replace(/'/g, "\""));
                });
                var result = decoded.join("'");
                return this.parse(result);
            }
            else {
                return what;
            }
        }
    });
});


Mana.define('Mana/Core/Utf8', [], function () {
    return Mana.Object.extend('Mana/Core/Utf8', {
        decode: function (str_data) {
            // Converts a UTF-8 encoded string to ISO-8859-1
            //
            // version: 1109.2015
            // discuss at: http://phpjs.org/functions/utf8_decode
            // +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
            // +      input by: Aman Gupta
            // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
            // +   improved by: Norman "zEh" Fuchs
            // +   bugfixed by: hitwork
            // +   bugfixed by: Onno Marsman
            // +      input by: Brett Zamir (http://brett-zamir.me)
            // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
            // *     example 1: utf8_decode('Kevin van Zonneveld');
            // *     returns 1: 'Kevin van Zonneveld'
            var tmp_arr = [],
                i = 0,
                ac = 0,
                c1 = 0,
                c2 = 0,
                c3 = 0;

            str_data += '';

            while (i < str_data.length) {
                c1 = str_data.charCodeAt(i);
                if (c1 < 128) {
                    tmp_arr[ac++] = String.fromCharCode(c1);
                    i++;
                } else if (c1 > 191 && c1 < 224) {
                    c2 = str_data.charCodeAt(i + 1);
                    tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
                    i += 2;
                } else {
                    c2 = str_data.charCodeAt(i + 1);
                    c3 = str_data.charCodeAt(i + 2);
                    tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                    i += 3;
                }
            }

            return tmp_arr.join('');
        }
    });
});


Mana.define('Mana/Core/Base64', ['singleton:Mana/Core/Utf8'], function (utf8) {
    return Mana.Object.extend('Mana/Core/Base64', {
        encode: function (what) {
            /*
             * Caudium - An extensible World Wide Web server
             * Copyright C 2002 The Caudium Group
             *
             * This program is free software; you can redistribute it and/or
             * modify it under the terms of the GNU General Public License as
             * published by the Free Software Foundation; either version 2 of the
             * License, or (at your option) any later version.
             *
             * This program is distributed in the hope that it will be useful, but
             * WITHOUT ANY WARRANTY; without even the implied warranty of
             * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
             * General Public License for more details.
             *
             * You should have received a copy of the GNU General Public License
             * along with this program; if not, write to the Free Software
             * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
             *
             */

            /*
             * base64.js - a JavaScript implementation of the base64 algorithm,
             *             (mostly) as defined in RFC 2045.
             *
             * This is a direct JavaScript reimplementation of the original C code
             * as found in the Exim mail transport agent, by Philip Hazel.
             *
             */
            var base64_encodetable = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
            var result = "";
            var len = what.length;
            var x, y;
            var ptr = 0;

            while (len-- > 0) {
                x = what.charCodeAt(ptr++);
                result += base64_encodetable.charAt(( x >> 2 ) & 63);

                if (len-- <= 0) {
                    result += base64_encodetable.charAt(( x << 4 ) & 63);
                    result += "==";
                    break;
                }

                y = what.charCodeAt(ptr++);
                result += base64_encodetable.charAt(( ( x << 4 ) | ( ( y >> 4 ) & 15 ) ) & 63);

                if (len-- <= 0) {
                    result += base64_encodetable.charAt(( y << 2 ) & 63);
                    result += "=";
                    break;
                }

                x = what.charCodeAt(ptr++);
                result += base64_encodetable.charAt(( ( y << 2 ) | ( ( x >> 6 ) & 3 ) ) & 63);
                result += base64_encodetable.charAt(x & 63);

            }

            return result;
        },
        decode: function (data) {
            // Decodes string using MIME base64 algorithm
            //
            // version: 1109.2015
            // discuss at: http://phpjs.org/functions/base64_decode
            // +   original by: Tyler Akins (http://rumkin.com)
            // +   improved by: Thunder.m
            // +      input by: Aman Gupta
            // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
            // +   bugfixed by: Onno Marsman
            // +   bugfixed by: Pellentesque Malesuada
            // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
            // +      input by: Brett Zamir (http://brett-zamir.me)
            // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
            // -    depends on: utf8_decode
            // *     example 1: base64_decode('S2V2aW4gdmFuIFpvbm5ldmVsZA==');
            // *     returns 1: 'Kevin van Zonneveld'
            // mozilla has this native
            // - but breaks in 2.0.0.12!
            //if (typeof this.window['btoa'] == 'function') {
            //    return btoa(data);
            //}
            var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
            var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
                ac = 0,
                dec = "",
                tmp_arr = [];

            if (!data) {
                return data;
            }

            data += '';

            do { // unpack four hexets into three octets using index points in b64
                h1 = b64.indexOf(data.charAt(i++));
                h2 = b64.indexOf(data.charAt(i++));
                h3 = b64.indexOf(data.charAt(i++));
                h4 = b64.indexOf(data.charAt(i++));

                bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

                o1 = bits >> 16 & 0xff;
                o2 = bits >> 8 & 0xff;
                o3 = bits & 0xff;

                if (h3 == 64) {
                    tmp_arr[ac++] = String.fromCharCode(o1);
                } else if (h4 == 64) {
                    tmp_arr[ac++] = String.fromCharCode(o1, o2);
                } else {
                    tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
                }
            } while (i < data.length);

            dec = tmp_arr.join('');
            dec = utf8.decode(dec);

            return dec;
        }
    });
});


Mana.define('Mana/Core/UrlTemplate', ['singleton:Mana/Core/Base64', 'singleton:Mana/Core/Config'], function (base64, config) {
    return Mana.Object.extend('Mana/Core/UrlTemplate', {
        decodeAttribute: function (data) {
            if (config.getData('debug')) {
                return data;
            }
            else {
                return base64.decode(data.replace(/-/g, '+').replace(/_/g, '/').replace(/,/g, '='));
            }
        },
        encodeAttribute: function(data) {
            return base64.encode(data).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, ',');
        }
    });
});


Mana.define('Mana/Core/StringTemplate', ['jquery'], function ($, undefined) {
    return Mana.Object.extend('Mana/Core/StringTemplate', {
        concat: function(parsedTemplate, vars) {
            var result = '';
            $.each(parsedTemplate, function(i, token) {
                var type = token[0];
                var text = token[1];
                if (type == 'string') {
                    result += text;
                }
                else if (type == 'var') {
                    if (vars[text] !== undefined) {
                        result += vars[text];
                    }
                    else {
                        result += '{{' + text + '}}';
                    }
                }
            });
            return result;
        }
    });
});


Mana.define('Mana/Core/Layout', ['jquery', 'singleton:Mana/Core'], function ($, core, undefined) {
    return Mana.Object.extend('Mana/Core/Layout', {
        _init: function () {
            this._pageBlock = null;
        },
        getPageBlock: function () {
            return this._pageBlock;
        },
        getBlock: function (blockName) {
            return this._getBlockRecursively(this.getPageBlock(), blockName);
        },
        getBlockForElement: function(el) {
            var blockInfo = this._getElementBlockInfo(el);
            return blockInfo ? this.getBlock(blockInfo.id) : null;
        },
        _getBlockRecursively: function (block, blockName) {
            if (block.getId() == blockName) {
                return block;
            }

            var self = this, result = null;
            $.each(block.getChildren(), function (index, child) {
                result = self._getBlockRecursively(child, blockName);
                return result ? false : true;
            });
            return result;
        },
        beginGeneratingBlocks: function (parentBlock) {
            var vars = {
                parentBlock: parentBlock,
                namedBlocks: {}
            };
            if (parentBlock) {
                parentBlock.trigger('unload', {}, false, true);
                parentBlock.trigger('unbind', {}, false, true);
                vars.namedBlocks = this._removeAnonymousBlocks(parentBlock);
            }
            return vars;
        },
        endGeneratingBlocks: function (vars) {
            var parentBlock = vars.parentBlock, namedBlocks = vars.namedBlocks;
            var self = this;
            this._collectBlockTypes(parentBlock ? parentBlock.getElement() : document, function (blockTypes) {
                if (!self._pageBlock) {
                    var body = document.body, $body = $(body);
                    var typeName = $body.attr('data-m-block');
                    var PageBlock = typeName ? blockTypes[typeName] : blockTypes['Mana/Core/PageBlock'];
                    self._pageBlock = new PageBlock()
                        .setElement($('body')[0])
                        .setId('page');
                }
                var initialPageLoad = (parentBlock === undefined);
                if (initialPageLoad) {
                    parentBlock = self.getPageBlock();
                }

                self._generateBlocksInElement(parentBlock.getElement(), parentBlock, blockTypes, namedBlocks);
                $.each(namedBlocks, function (id, namedBlock) {
                    namedBlock.parent.removeChild(namedBlock.child);
                });
                parentBlock.trigger('bind', {}, false, true);
                parentBlock.trigger('load', {}, false, true);

                // BREAKPOINT: all generated client side blocks
                var a = 1;
            });
        },
        _collectBlockTypes: function (element, callback) {
            var blockTypeNames = ['Mana/Core/PageBlock'];
            this._collectBlockTypesInElement(element, blockTypeNames);
            Mana.requireOptional(blockTypeNames, function () {
                var blockTypeValues = arguments;
                ;
                var blockTypes = {};
                $.each(blockTypeNames, function (key, value) {
                    blockTypes[value] = blockTypeValues[key];
//                    if (blockTypeValues[key]) {
//                        blockTypes[value] = blockTypeValues[key];
//                    }
//                    else {
//                        throw "Block type '" + value + "' is not defined.";
//                    }
                });
                callback(blockTypes);
            });
        },
        _collectBlockTypesInElement: function (element, blockTypeNames) {
            var layout = this;
            $(element).children().each(function () {
                var blockInfo = layout._getElementBlockInfo(this);
                if (blockInfo) {
                    if (blockTypeNames.indexOf(blockInfo.typeName) == -1) {
                        blockTypeNames.push(blockInfo.typeName);
                    }
                }
                layout._collectBlockTypesInElement(this, blockTypeNames);
            });
        },
        _removeAnonymousBlocks: function (parentBlock) {
            var self = this, result = {};
            $.each(parentBlock.getChildren().slice(0), function (key, block) {
                if (block.getId()) {
                    result[block.getId()] = { parent: parentBlock, child: block};
                    self._removeAnonymousBlocks(block);
                }
                else {
                    parentBlock.removeChild(block);
                }
            });
            return result;
        },
        _getElementBlockInfo: function (element) {
            var $element = $(element);
            var id, typeName;

            if ((id = core.getPrefixedClass(element, 'mb-'))
                || (typeName = $element.attr('data-m-block'))
                || $element.hasClass('m-block')) {
                return {
                    id: id || element.id,
                    typeName: typeName || $element.attr('data-m-block') || 'Mana/Core/Block'
                };
            }

            return null;
        },
        _generateBlocksInElement: function (element, block, blockTypes, namedBlocks) {
            var layout = this;
            $(element).children().each(function () {
                var childBlock = layout._createBlockFromElement(this, block, blockTypes, namedBlocks);
                layout._generateBlocksInElement(this, childBlock || block, blockTypes, namedBlocks);
            });
        },
        _createBlockFromElement: function (element, parent, blockTypes, namedBlocks) {
            var blockInfo = this._getElementBlockInfo(element);

            if (blockInfo) {
                var type = blockTypes[blockInfo.typeName], block, exists = false;
                if (blockInfo.id) {
                    block = parent.getChild(core.getBlockAlias(parent.getId(), blockInfo.id));
                    if (block) {
                        exists = true;
                        delete namedBlocks[blockInfo.id];
                    }
                    else {
                        if (type) {
                            block = new type();
                        }
                        else {
                            console.error("Block '" + blockInfo.typeName + "' is not defined");
                        }
                    }
                    if (block) {
                        block.setId(blockInfo.id);
                    }
                }
                else  {
                    if (type) {
                        block = new type();
                    }
                    else {
                        console.error("Block '" + blockInfo.typeName + "' is not defined");
                    }
                }
                if (block) {
                    block.setElement(element);
                    if (!exists) {
                        parent.addChild(block);
                    }
                    return block;
                }
                else {
                    return null;
                }
            }
            else {
                return null;
            }
        },
        preparePopup: function(options) {
            options = this._preparePopupOptions(options);
            if (options.$popup === undefined) {
                var $popup = $('#m-popup');
                $popup.css({"width": "auto", "height": "auto"});
                if (core.isString(options.content)) {
                    $popup.html(options.content);
                }
                else {
                    $popup.html($(options.content).html());
                }

                if (options.popup['class']) {
                    $popup.addClass(options.popup['class']);
                }

                options.$popup = $popup;
            }
            return options.$popup;
        },
        _preparePopupOptions: function(options) {
            var result = {
                overlay: {
                    opacity: 0.2
                },
                popup: {
                    blockName: 'Mana/Core/PopupBlock'
                },
                popupBlock: {},
                fadein: {
                    overlayTime: 0,
                    popupTime: 300
                },
                fadeout: {
                    overlayTime: 0,
                    popupTime: 500
                }
            };
            $.extend(true, result, options);
            return result;
        },
        showPopup: function (options) {
            options = this._preparePopupOptions(options);

            var self = this;
            Mana.requireOptional([options.popup.blockName], function (PopupBlockClass) {
                var fadeoutCallback = options.fadeout.callback;
                options.fadeout.callback = function () {
                    $('#m-popup').fadeOut(options.fadeout.popupTime, function () {
                        if (fadeoutCallback) {
                            fadeoutCallback();
                        }
                    });
                };
                var overlay = self.getPageBlock().showOverlay('m-popup-overlay', options.fadeout);
                var $popup = self.preparePopup(options);
                overlay.animate({ opacity: options.overlay.opacity }, options.fadein.overlayTime, function () {
                    $popup.show();

                    var popupBlock = new PopupBlockClass();
                    popupBlock.setElement($popup[0]);
                    var vars = self.beginGeneratingBlocks(popupBlock);
                    self.endGeneratingBlocks(vars);
                    if (popupBlock.prepare) {
                        popupBlock.prepare(options.popupBlock);
                    }

                    $('.m-popup-overlay').on('click', function () {
                        self.hidePopup();
                        return false;
                    });
                    if (!self._popupEscListenerInitialized) {
                        self._popupEscListenerInitialized = true;
                        $(document).keydown(function (e) {
                            if ($('.m-popup-overlay').length) {
                                if (e.keyCode == 27) {
                                    self.hidePopup();
                                    return false;
                                }
                            }
                            return true;
                        });
                    }

                    $popup
                        .css("top", (($(window).height() - $popup.outerHeight()) / 2) + $(window).scrollTop() + "px")
                        .css("left", (($(window).width() - $popup.outerWidth()) / 2) + $(window).scrollLeft() + "px");

                    var popupHeight = $popup.height();
                    $popup.hide().css({"height": "auto"});

                    var css = {
                        left: $popup.css('left'),
                        top: $popup.css('top'),
                        width: $popup.width() + "px",
                        height: $popup.height() + "px"
                    };

                    $popup.children().each(function () {
                        $(this).css({
                            width: ($popup.width() + $(this).width() - $(this).outerWidth()) + "px",
                            height: ($popup.height() + $(this).height() - $(this).outerHeight()) + "px"
                        });
                    });
                    $popup
                        .css({
                            top: ($(window).height() / 2) + $(window).scrollTop() + "px",
                            left: ($(window).width() / 2) + $(window).scrollLeft() + "px",
                            width: 0 + "px",
                            height: 0 + "px"
                        })
                        .show();

                    $popup.animate(css, options.fadein.popupTime, options.fadein.callback);
                });
            });
        },
        hidePopup: function () {
            this.getPageBlock().hideOverlay();
        }
    });
});


Mana.define('Mana/Core/Ajax', ['jquery', 'singleton:Mana/Core/Layout', 'singleton:Mana/Core/Json',
    'singleton:Mana/Core', 'singleton:Mana/Core/Config'],
function ($, layout, json, core, config, undefined)
{
    return Mana.Object.extend('Mana/Core/Ajax', {
        _init: function() {
            this._interceptors = [];
            this._matchedInterceptorCache = {};
            this._lastAjaxActionSource = undefined;
            this._oldSetLocation = undefined;
            this._preventClicks = 0;
        },
        _encodeUrl: function(url, options) {
            if (options.encode) {
                if (options.encode.offset !== undefined) {
                    if (options.encode.length === undefined) {
                        if (options.encode.offset === 0) {
                            return window.encodeURI(url.substr(options.encode.offset));
                        }
                        else {
                            return url.substr(0, options.encode.offset) +
                                window.encodeURI(url.substr(options.encode.offset));
                        }
                    }
                    else if (options.encode.length === 0) {
                        return url;
                    }
                    else {
                        if (options.encode.offset === 0) {
                            return window.encodeURI(url.substr(options.encode.offset, options.encode.length))
                                + url.substr(options.encode.offset + options.encode.length);
                        }
                        else {
                            return url.substr(0, options.encode.offset) +
                                window.encodeURI(url.substr(options.encode.offset, options.encode.length)) +
                                url.substr(options.encode.offset + options.encode.length);
                        }
                    }
                }
                else {
                    return url;
                }
            }
            else {
                return window.encodeURI(url);
            }
        },
        get:function (url, callback, options) {
            var self = this, encodedUrl;
            options = this._before(options, url);
            $.get(this._encodeUrl(url, options))
                .done(function (response) { self._done(response, callback, options, url); })
                .fail(function (error) { self._fail(error, options, url)})
                .complete(function () { self._complete(options, url); });
        },
        post:function (url, data, callback, options) {
            var self = this;
            if (data === undefined) {
                data = [];
            }
            if (callback === undefined) {
                callback = function() {};
            }
            options = this._before(options, url, data);
            $.post(window.encodeURI(url), data)
                .done(function (response) { self._done(response, callback, options, url, data); })
                .fail(function (error) { self._fail(error, options, url, data)})
                .complete(function () { self._complete(options, url, data); });
        },
        update: function(response) {
            if (response.updates) {
                $.each(response.updates, function (selector, html) {
                    $(selector).html(html);
                });
            }
            if (response.blocks) {
                $.each(response.blocks, function (blockName, sectionIndex) {
                    var block = layout.getBlock(blockName);
                    if (block) {
                        block.setContent(response.sections[sectionIndex]);
                    }
                });
            }
            if (response.config) {
                config.set(response.config);
            }
            if (response.script) {
                $.globalEval(response.script);
            }
            if (response.title) {
                document.title = response.title.replace(/&amp;/g, '&');
            }
        },
        getSectionSeparator: function() {
            return "\n91b5970cd70e2353d866806f8003c1cd56646961\n";
        },
        _before: function(options, url, data) {
            var page = layout.getPageBlock();
            options = $.extend({
                showOverlay:page.getShowOverlay(),
                showWait:page.getShowWait(),
                showDebugMessages:page.getShowDebugMessages()
            }, options);

            if (options.showOverlay) {
                page.showOverlay();
            }
            if (options.showWait) {
                page.showWait();
            }
            if (options.preventClicks) {
                this._preventClicks++;
            }
            $(document).trigger('m-ajax-before', [[], url, '', options]);
            return options;
        },
        _done:function (response, callback, options, url, data) {
            var page = layout.getPageBlock();
            if (options.showOverlay) {
                page.hideOverlay();
            }
            if (options.showWait) {
                page.hideWait();
            }
            try {
                var content = response;
                try {
                    var sections = response.split(this.getSectionSeparator());
                    response = sections.shift();
                    response = json.parse(response);
                    response.sections = sections;
                }
                catch (e) {
                    callback(content, { url:url});
                    return;
                }
                if (!response) {
                    if (options.showDebugMessages) {
                        alert('No response.');
                    }
                }
                else if (response.error && !response.customErrorDisplay) {
                    if (options.showDebugMessages) {
                        alert(response.message || response.error);
                    }
                }
                else {
                    callback(response, { url:url, data: data});
                }
            }
            catch (error) {
                if (options.showDebugMessages) {
                    var s = '';
                    if (typeof(error) == 'string') {
                        s += error;
                    }
                    else {
                        s += error.message;
                        if (error.fileName) {
                            s += "\n    in " + error.fileName + " (" + error.lineNumber + ")";
                        }
                    }
                    if (response) {
                        s += "\n\n";
                        s += typeof(response) == 'string' ? response : json.stringify(response);
                    }
                    alert(s);
                }
            }
        },
        _fail:function (error, options, url, data) {
            var page = layout.getPageBlock();
            if (options.showOverlay) {
                page.hideOverlay();
            }
            if (options.showWait) {
                page.hideWait();
            }
            if (options.showDebugMessages) {
                alert(error.status + (error.responseText ? ': ' + error.responseText : ''));
            }
        },
        _complete:function (options, url, data) {
            if (options.preventClicks) {
                this._preventClicks--;
            }
            $(document).trigger('m-ajax-after', [[], url, '', options]);
        },
        addInterceptor: function (interceptor) {
            this._interceptors.push(interceptor);
        },
        removeInterceptor: function (interceptor) {
            var index = this._interceptors.indexOf(interceptor);
            if (index != -1) {
                this._interceptors.splice(index, 1);
            }
        },
        startIntercepting: function() {
            var self = this;

            // intercept browser history changes (Back button clicks, pushing new URL in _callInterceptionCallback() method)
            if (window.History && window.History.enabled) {
                $(window).on('statechange', self._onStateChange = function () {
                    var State = window.History.getState();
                    var url = State.url; // URL encoded
                    if (self._findMatchingInterceptor(url, self._lastAjaxActionSource)) {
                        self._internalCallInterceptionCallback(url, self._lastAjaxActionSource);
                    }
                    else {
                        self._oldSetLocation(url, self._lastAjaxActionSource);
                    }
                });
            }

            // intercept Magento setLocation() calls
            if (window.setLocation) {
                this._oldSetLocation = window.setLocation;
                window.setLocation = function (url, element) {
                    self._callInterceptionCallback(url, element);
                };
            }

            // intercept all link clicks
            $(document).on('click', 'a', self._onClick = function () {
                var url = this.href; // URL encoded
                if (self._preventClicks && url == location.href + '#') {
                    return false;
                }
                if (self._findMatchingInterceptor(url, this)) {
                    return self._callInterceptionCallback(url, this);
                }
                else {
                    return true;
                }
            });
        },
        stopIntercepting: function() {
            if (window.History && window.History.enabled) {
                $(window).off('statechange', self._onStateChange);
                self._onStateChange = null;
            }
            $(document).off('click', 'a', self._onClick);
            self._onClick = null;
        },
        _internalCallInterceptionCallback: function(url, element) {
            var interceptor = this._findMatchingInterceptor(url, element);
            if (interceptor) {
                this.lastUrl = url;
                interceptor.intercept(url, element);
                return false; // prevent default link click behavior
            }
            return true;
        },
        _callInterceptionCallback: function(url, element) {
            if (this._findMatchingInterceptor(url, element)) {
                this._lastAjaxActionSource = element;
                if (window.History && window.History.enabled) {
                    //noinspection JSUnresolvedVariable
                    window.History.pushState(null, window.title, url);
                }
                else {
                    this._internalCallInterceptionCallback(url, element);
                }
            }
            else {
                this._oldSetLocation(url, element);
            }
            return false;
        },
        _findMatchingInterceptor: function(url, element) {
            if (this._matchedInterceptorCache[url] === undefined) {
                var interceptor = false;
                if (config.getData('ajax.enabled')) {
                    $.each(this._interceptors, function(index, candidateInterceptor) {
                        if (candidateInterceptor.match(url, element)) {
                            interceptor = candidateInterceptor;
                            return false;
                        }
                        else {
                            return true;
                        }
                    });
                }
                this._matchedInterceptorCache[url] = interceptor;
            }
            return this._matchedInterceptorCache[url];
        },
        getDocumentUrl: function() {
            if (this.lastUrl) {
                return this.lastUrl;
            }
            else {
                return document.URL;
            }
        }
    });
});


Mana.define('Mana/Core/Block', ['jquery', 'singleton:Mana/Core', 'singleton:Mana/Core/Layout',
    'singleton:Mana/Core/Json'],
function($, core, layout, json, undefined) {
    return Mana.Object.extend('Mana/Core/Block', {
        _init: function() {
            this._id = '';
            this._element = null;
            this._parent = null;
            this._children = [];
            this._namedChildren = {};
            this._isSelfContained = false;
            this._eventHandlers = {};
            this._data = {};
            this._text = {};
            this._subscribeToHtmlEvents()._subscribeToBlockEvents();
        },
        _subscribeToHtmlEvents: function() {
            this._json = {};
            return this;
        },
        _subscribeToBlockEvents:function () {
            return this;
        },
        getElement:function() {
            return this._element;
        },
        $: function() {
            return $(this.getElement());
        },
        setElement:function (value) {
            this._element = value;
            return this;
        },
        addChild:function (child) {
            this._children.push(child);
            if (child.getId()) {
                this._namedChildren[core.getBlockAlias(this.getId(), child.getId())] = child;
            }
            child._parent = this;
            return this;
        },
        removeChild: function(child) {
            var index = $.inArray(child, this._children);
            if (index != -1) {
                core.arrayRemove(this._children, index);
                if (child.getId()) {
                    delete this._namedChildren[core.getBlockAlias(this.getId(), child.getId())];
                }
            }
            child._parent = null;
            return this;
        },
        getIsSelfContained: function() {
            return this._isSelfContained;
        },
        setIsSelfContained: function(value) {
            this._isSelfContained = value;
            return this;
        },
        getId:function () {
            return this._id || this.getElement().id;
        },
        setId:function (value) {
            this._id = value;
            return this;
        },
        getParent: function() {
            return this._parent;
        },
        getChild: function(name, index) {
            if (core.isFunction(name)) {
                var result = null;
                $.each(this._children, function (i, child) {
                    if (name(i, child)) {
                        result = child;
                        return false;
                    }
                    else {
                        return true;
                    }
                });
                return result;
            }
            else {
                return this._namedChildren[name];
            }
        },
        getChildren: function(condition) {
            if (condition === undefined) {
                return this._children.slice(0);
            }
            else {
                var result = [];
                $.each(this._children, function (index, child) {
                    if (condition(index, child)) {
                        result.push(child);
                    }
                });
                return result;
            }
        },
        getAlias: function() {
            var result = undefined;
            var self = this;
            $.each(this._parent._namedChildren, function(name, child) {
                if (child === self) {
                    result = name;
                    return false;
                }
                else {
                    return true;
                }
            });

            return result;
        },
        _trigger: function(name, e) {
            if (!e.stopped && this._eventHandlers[name] !== undefined) {
                $.each(this._eventHandlers[name], function(key, value) {
                    var result = value.callback.call(value.target, e);
                    if (result === false) {
                        e.stopped = true;
                    }
                    return result;
                });
            }
            return e.result;
        },
        trigger: function(name, e, bubble, propagate) {
            if (e === undefined) {
                e = {};
            }
            if (e.target === undefined) {
                e.target = this;
            }
            if (propagate === undefined) {
                propagate = false;
            }
            if (bubble === undefined) {
                bubble = true;
            }
            this._trigger(name, e);
            if (propagate) {
                $.each(this.getChildren(), function (index, child) {
                    child.trigger(name, e, false, propagate);
                });
            }
            if (bubble && this.getParent()) {
                this.getParent().trigger(name, e, bubble, false);
            }
            return e.result;
        },
        on: function(name, target, callback, sortOrder) {
            if (this._eventHandlers[name] === undefined) {
                this._eventHandlers[name] = [];
            }
            if (sortOrder === undefined) {
                sortOrder = 0;
            }
            this._eventHandlers[name].push({target: target, callback: callback, sortOrder: sortOrder});
            this._eventHandlers[name].sort(function(a, b) {
                if (a.sortOrder < b.sortOrder) return -1;
                if (a.sortOrder > b.sortOrder) return 1;
                return 0;
            });
            return this;
        },
        off:function (name, target, callback) {
            if (this._eventHandlers[name] === undefined) {
                this._eventHandlers[name] = [];
            }
            var found = -1;
            $.each(this._eventHandlers[name], function(index, handler) {
                if (handler.target == target && handler.callback == callback) {
                    found = index;
                    return false;
                }
                else {
                    return true;
                }
            });

            if (found != -1) {
                core.arrayRemove(this._eventHandlers[name], found);
            }
        },
        setContent: function(content) {
            if ($.type(content) != 'string') {
                if (content.content && this.getId() && content.content[this.getId()]) {
                    content = content.content[this.getId()];
                }
                else {
                    return this;
                }
            }

            var vars = layout.beginGeneratingBlocks(this);
            content = $(content);
            $(this.getElement()).replaceWith(content);
            this.setElement(content[0]);
            layout.endGeneratingBlocks(vars);

            return this;
        },
        getData: function(key) {
            return this._data[key];
        },
        setData: function(key, value) {
            this._data[key] = value;
            return this;
        },
        getText: function (key) {
            if (this._text[key] === undefined) {
                this._text[key] = this.$().data(key + '-text');
            }
            return this._text[key];
        },
        getJsonData: function(attributeName, fieldName) {
            if (this._json[attributeName] === undefined) {
                this._json[attributeName] = json.decodeAttribute(this.$().data(attributeName));
            }
            return fieldName === undefined ? this._json[attributeName] : this._json[attributeName][fieldName];
        }
    });
});


Mana.define('Mana/Core/PopupBlock', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Layout'], function ($, Block, layout) {
    return Block.extend('Mana/Core/PopupBlock', {
        prepare: function(options) {
            var self = this;
            this._host = options.host;

            this.$().find('.btn-close').on('click', function() { return self._close(); });
        },
        _close: function() {
            layout.hidePopup();
            return false;
        }
    });
});


Mana.define('Mana/Core/PageBlock', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Config'],
function ($, Block, config)
{
    return Block.extend('Mana/Core/PageBlock', {
        _init: function () {
            this._defaultOverlayFadeout = { overlayTime: 0, popupTime: 0, callback: null };
            this._overlayFadeout = this._defaultOverlayFadeout;
            this._super();
        },
        _subscribeToHtmlEvents: function() {
            var self = this;
            var inResize = false;
            function _raiseResize() {
                if (inResize) {
                    return;
                }
                inResize = true;
                self.resize();
                inResize = false;
            }


            return this
                ._super()
                .on('bind', this, function() {
                    $(window).on('load', _raiseResize);
                    $(window).on('resize', _raiseResize);
                })
                .on('unbind', this, function() {
                    $(window).off('load', _raiseResize);
                    $(window).off('resize', _raiseResize);
                });

        },
        _subscribeToBlockEvents: function() {
            return this
                ._super()
                .on('load', this, function () {
                    this.resize();
                });
        },
        resize: function () {
            this.trigger('resize', {}, false, true);
        },
        showOverlay: function(overlayClass, fadeout) {
            this._overlayFadeout = fadeout || this._defaultOverlayFadeout;
            var overlay = overlayClass ? $('<div class="m-overlay ' + overlayClass + '"></div>') : $('<div class="m-overlay"></div>');
            overlay.appendTo(this.getElement());
            overlay.css({left:0, top:0}).width($(document).width()).height($(document).height());
            return overlay;
        },
        hideOverlay: function() {
            var self = this;
            $('.m-overlay').fadeOut(this._overlayFadeout.overlayTime, function () {
                $('.m-overlay').remove();
                if (self._overlayFadeout.callback) {
                    self._overlayFadeout.callback();
                }
                self._overlayFadeout = self._defaultOverlayFadeout;
            })
            return this;
        },
        showWait: function() {
            $('#m-wait').show();
            return this;
        },
        hideWait: function() {
            $('#m-wait').hide();
            return this;
        },
        getShowDebugMessages: function() {
            return config.getData('debug');
        },
        getShowOverlay:function () {
            return config.getData('showOverlay');
        },
        getShowWait:function () {
            return config.getData('showWait');
        }
    });
});


Mana.require(['jquery', 'singleton:Mana/Core/Layout', 'singleton:Mana/Core/Ajax'], function($, layout, ajax) {
    function _generateBlocks() {
        var vars = layout.beginGeneratingBlocks();
        layout.endGeneratingBlocks(vars);
    }
    $(function() {
        _generateBlocks();
        ajax.startIntercepting();
    });
});


Mana.require(['jquery'], function($) {
    var bp = {
        xsmall: 479,
        small: 599,
        medium: 770,
        large: 979,
        xlarge: 1199
    };
    Mana.rwdIsMobile = false;
    $(function() {
        if (window.enquire) {
            enquire.register('screen and (max-width: ' + bp.medium + 'px)', {
                match: function () {
                    Mana.rwdIsMobile = true;
                    $(document).trigger('m-rwd-mobile');
                },
                unmatch: function () {
                    Mana.rwdIsMobile = false;
                    $(document).trigger('m-rwd-wide');
                }
            });
        }
    });
});


//region (Obsolete) additional jQuery functions used in MANAdev extensions
(function($) {
	// this variables are private to this code block
	var _translations = {};
	var _options = {};

	// Default usage of this function is to pass a string in original language and get translated string as a 
	// result. This same function is also used to register original and translated string pairs - in this case
	// plain object with mappings is passed as the only parameter. Anyway, we expect the only parameter to be 
	// passed
	$.__ = function(key) {
		if (typeof key === "string") { // do translation
			var args = arguments;
			args[0] = _translations[key] ? _translations[key] : key;
			return $.vsprintf(args);
		}
		else { // register translation pairs
			_translations = $.extend(_translations, key);
		}
	};
	// Default usage of this function is to pass a CSS selector and get plain object of associated options as 
	// a result. This same function is used to register selector-object pairs in this case plain object with 
	// with mappings is passed as the only parameter. Anyway, we expect the only parameter to be passed
	$.options = function (selector) {
		if (typeof selector === "string") { // return associated options
			return _options[selector];
		}
		else { // register selector-options pairs
			_options = $.extend(true, _options, selector);
		}
		$(document).trigger('m-options-changed');
	};
	
	$.dynamicUpdate = function (update) {
		if (update) {
			$.each(update, function(index, update) {
				$(update.selector).html(update.html);
			});
		}
	}
	$.dynamicReplace = function (update, loud, decode) {
		if (update) {
			$.each(update, function(selector, html) {
				var selected = $(selector);
				if (selected.length) {
					var first = $(selected[0]);
					if (selected.length > 1) {
						selected.slice(1).remove();
					}
					first.replaceWith(decode ? $.utf8_decode(html) : html);
				}
				else {
					if (loud) {
						throw 'There is no content to replace.';
					}
				}
				//console.log('Selector: ' + selector);
				//console.log('HTML: ' + html);
			});
		}
	}
	
	$.errorUpdate = function(selector, error) {
		if (!selector) {
			selector = '#messages';
		}
		var messages = $(selector);
		if (messages.length) {
			messages.html('<ul class="messages"><li class="error-msg"><ul><li>' + error + '</li></ul></li></ul>');
		}
		else {
			alert(error);
		}
	}
	
	// Array Remove - By John Resig (MIT Licensed)
	$.arrayRemove = function(array, from, to) {
	  var rest = array.slice((to || from) + 1 || array.length);
	  array.length = from < 0 ? array.length + from : from;
	  return array.push.apply(array, rest);
	};
	$.mViewport = function() {
		var m = document.compatMode == 'CSS1Compat';
		return {
			l : window.pageXOffset || (m ? document.documentElement.scrollLeft : document.body.scrollLeft),
			t : window.pageYOffset || (m ? document.documentElement.scrollTop : document.body.scrollTop),
			w : window.innerWidth || (m ? document.documentElement.clientWidth : document.body.clientWidth),
			h : window.innerHeight || (m ? document.documentElement.clientHeight : document.body.clientHeight)
		};
	}
	$.mStickTo = function(el, what) {
		var pos = $(el).offset();
		var viewport = $.mViewport();
		var top = pos.top + el.offsetHeight;
		var left = pos.left + (el.offsetWidth - what.outerWidth()) / 2;
		if (top + what.outerHeight() > viewport.t + viewport.h) {
			top = pos.top - what.outerHeight();
		}
		if (left + what.outerWidth() > viewport.l + viewport.w) {
			left = pos.left + el.offsetWidth - what.outerWidth();
		}
		what.css({left: left + 'px', top: top + 'px'});
	}

	$.fn.mMarkAttr = function (attr, condition) {
		if (condition) {
			this.attr(attr, attr);
		}
		else {
			this.removeAttr(attr);
		}
		return this;
	}; 
	// the following function is executed when DOM ir ready. If not use this wrapper, code inside could fail if
	// executed when referenced DOM elements are still being loaded.
	$(function() {
		// fix for IE 7 and IE 8 where dom:loaded may fire too early
		try {
		    if (window.mainNav) {
                window.mainNav("nav", {"show_delay":"100", "hide_delay":"100"});
            }
		}
		catch (e) {
			
		}
	});

    $.base64_decode = function (data) {
        // Decodes string using MIME base64 algorithm
        //
        // version: 1109.2015
        // discuss at: http://phpjs.org/functions/base64_decode
        // +   original by: Tyler Akins (http://rumkin.com)
        // +   improved by: Thunder.m
        // +      input by: Aman Gupta
        // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +   bugfixed by: Onno Marsman
        // +   bugfixed by: Pellentesque Malesuada
        // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +      input by: Brett Zamir (http://brett-zamir.me)
        // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // -    depends on: utf8_decode
        // *     example 1: base64_decode('S2V2aW4gdmFuIFpvbm5ldmVsZA==');
        // *     returns 1: 'Kevin van Zonneveld'
        // mozilla has this native
        // - but breaks in 2.0.0.12!
        //if (typeof this.window['btoa'] == 'function') {
        //    return btoa(data);
        //}
        var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
        var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
            ac = 0,
            dec = "",
            tmp_arr = [];

        if (!data) {
            return data;
        }

        data += '';

        do { // unpack four hexets into three octets using index points in b64
            h1 = b64.indexOf(data.charAt(i++));
            h2 = b64.indexOf(data.charAt(i++));
            h3 = b64.indexOf(data.charAt(i++));
            h4 = b64.indexOf(data.charAt(i++));

            bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

            o1 = bits >> 16 & 0xff;
            o2 = bits >> 8 & 0xff;
            o3 = bits & 0xff;

            if (h3 == 64) {
                tmp_arr[ac++] = String.fromCharCode(o1);
            } else if (h4 == 64) {
                tmp_arr[ac++] = String.fromCharCode(o1, o2);
            } else {
                tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
            }
        } while (i < data.length);

        dec = tmp_arr.join('');
        dec = $.utf8_decode(dec);

        return dec;
    };
    $.utf8_decode = function (str_data) {
        // Converts a UTF-8 encoded string to ISO-8859-1
        //
        // version: 1109.2015
        // discuss at: http://phpjs.org/functions/utf8_decode
        // +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
        // +      input by: Aman Gupta
        // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +   improved by: Norman "zEh" Fuchs
        // +   bugfixed by: hitwork
        // +   bugfixed by: Onno Marsman
        // +      input by: Brett Zamir (http://brett-zamir.me)
        // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // *     example 1: utf8_decode('Kevin van Zonneveld');
        // *     returns 1: 'Kevin van Zonneveld'
        var tmp_arr = [],
            i = 0,
            ac = 0,
            c1 = 0,
            c2 = 0,
            c3 = 0;

        str_data += '';

        while (i < str_data.length) {
            c1 = str_data.charCodeAt(i);
            if (c1 < 128) {
                tmp_arr[ac++] = String.fromCharCode(c1);
                i++;
            } else if (c1 > 191 && c1 < 224) {
                c2 = str_data.charCodeAt(i + 1);
                tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
                i += 2;
            } else {
                c2 = str_data.charCodeAt(i + 1);
                c3 = str_data.charCodeAt(i + 2);
                tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }
        }

        return tmp_arr.join('');
    };

    var _popupFadeoutOptions = { overlayTime: 500, popupTime: 1000, callback: null };
    $.mSetPopupFadeoutOptions = function(options) {
        _popupFadeoutOptions = options;
    }
    $.fn.extend({
        mPopup: function(name, options) {
            var o = $.extend({
                fadeOut: { overlayTime: 0, popupTime:500, callback:null },
                fadeIn: { overlayTime: 0, popupTime:500, callback: null },
                overlay: { opacity: 0.2},
                popup: { contentSelector:'.' + name + '-text', containerClass:'m-' + name + '-popup-container', top:100 }

            }, options);
            $(this).live('click', function() {
                if ($.mPopupClosing()) {
                    return false;
                }
                // preparations
                var html = $(o.popup.contentSelector).html();
                $.mSetPopupFadeoutOptions(o.fadeOut);

                // put overlay to prevent interaction with the page and to catch 'cancel' mouse clicks
                var overlay = $('<div class="m-popup-overlay"> </div>');
                overlay.appendTo(document.body);
                overlay.css({left:0, top:0}).width($(document).width()).height($(document).height());
                overlay.animate({ opacity:o.overlay.opacity }, o.fadeIn.overlayTime, function () {
                    // all this code is called when overlay animation is over

                    // fill popup with content
                    $('#m-popup')
                        .css({"width":"auto", "height":"auto"})
                        .html(html)
                        .addClass(o.popup.containerClass)
                        .css("top", (($(window).height() - $('#m-popup').outerHeight()) / 2) - o.popup.top + $(window).scrollTop() + "px")
                        .css("left", (($(window).width() - $('#m-popup').outerWidth()) / 2) + $(window).scrollLeft() + "px")

                    // get intended height and set initial height to 0
                    var popupHeight = $('#m-popup').height();
                    $('#m-popup').show().height(0);
                    $('#m-popup').hide().css({"height":"auto"});

                    // calculate intended popup position
                    var css = {
                        left:$('#m-popup').css('left'),
                        top:$('#m-popup').css('top'),
                        width:$('#m-popup').width() + "px",
                        height:$('#m-popup').height() + "px"
                    };

                    // adjust (the only) child of popup container element
                    $('#m-popup').children().each(function () {
                        $(this).css({
                            width:($('#m-popup').width() + $(this).width() - $(this).outerWidth()) + "px",
                            height:($('#m-popup').height() + $(this).height() - $(this).outerHeight()) + "px"
                        });
                    });

                    // make popup a point
                    $('#m-popup')
                        .css({
                            top:($(window).height() / 2) - o.popup.top + $(window).scrollTop() + "px",
                            left:($(window).width() / 2) + $(window).scrollLeft() + "px",
                            width:0 + "px",
                            height:0 + "px"
                        })
                        .show();

                    // explode popup to intended size
                    $('#m-popup').animate(css, o.fadeIn.popupTime, function () {
                        if (o.fadeIn.callback) {
                            o.fadeIn.callback();
                        }
                    });
                });

                // prevent following to target link of <a> tag
                return false;
            });
        }
    });
    var _popupClosing = false;
    $.mPopupClosing = function (value) {
        if (value !== undefined) {
            _popupClosing = value;
        }
        return _popupClosing;
    };
    $.mClosePopup = function () {
        $.mPopupClosing(true);
        $('.m-popup-overlay').fadeOut(_popupFadeoutOptions.overlayTime, function() {
            $('.m-popup-overlay').remove();
            $('#m-popup').fadeOut(_popupFadeoutOptions.popupTime, function() {
                if (_popupFadeoutOptions.callback) {
                    _popupFadeoutOptions.callback();
                }
                $.mPopupClosing(false);
            });
        })
        return false;
    };

})(jQuery);
//endregion

//# sourceMappingURL=core.js.map