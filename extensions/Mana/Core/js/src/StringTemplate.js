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
