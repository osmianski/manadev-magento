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
