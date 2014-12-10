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
