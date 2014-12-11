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
