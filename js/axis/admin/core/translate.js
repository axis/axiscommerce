/**
 * Axis
 *
 * This file is part of Axis.
 *
 * Axis is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Axis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Axis.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

var Locale = {
    module: function(code, obj){
        if (this[code] == undefined) {
            this[code] = {};
        }
        $.extend(this[code], obj);
    }
};

/**
 * First element of arguments is always points at module to use
 *
 * @param {String} module_code
 */
String.prototype.l = function(module_code) {
    var key = this.toString();
    var module = 'core';

    if (module_code != undefined) {
        module = module_code;
    }

    if (!Locale[module] || !Locale[module][key]) {
        localized = key; // localization not found
    } else {
        localized = Locale[module][key];
    }

    if (arguments.length > 1) {
        for (var i = 1, limit = arguments.length - 1; i <= limit; i++) {
            /*if (typeof arguments[i] != 'string') {
                continue;
            }*/
            localized = localized.replace(new RegExp("{.+?}"), arguments[i]);
        }
    }

    return localized;
}