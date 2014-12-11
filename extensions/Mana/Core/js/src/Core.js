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
