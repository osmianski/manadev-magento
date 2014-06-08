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
