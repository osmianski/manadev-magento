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
