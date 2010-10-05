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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

var MessageStack = {
    _target: null,
    _stack: [],
    _sortOrder: ['error', 'warning', 'notice', 'success'],

    init: function(messages, target)
    {
        if (target) {
            this._target = target;
        }
        this.clear();
        this._toString(messages);
        return this;
    },

    render: function()
    {
        $(this._target).prepend('<div id="messages"></div>');
        for (var i in this._stack) {
            if (typeof this._stack[i] != 'string' || this._stack[i] == '') {
                continue;
            }
            $('#messages', this._target).append(this._stack[i]);
        }
    },

    clear: function()
    {
        this._stack = [];
        if (null !== this._target) {
            $('#messages', this._target).remove();
        }
        return this;
    },

    hide: function(e)
    {
        $(e).fadeOut();
    },

    _toString: function(messages)
    {
        /* first read important messages */
        for (var i in this._sortOrder) {
            if (!messages[this._sortOrder[i]] || !messages[this._sortOrder[i]].length)
                continue;

            this._fillStack(this._sortOrder[i], messages[this._sortOrder[i]]);

            /* delete processed messages */
            delete messages[this._sortOrder[i]];
        }

        /* read everything else */
        for (var i in messages) {
            if (!messages[i] || !messages[i].length || typeof messages[i] == 'function')
                continue;

            this._fillStack(i, messages[i]);

            /* delete processed messages */
            delete messages[i];
        }
    },

    _fillStack: function(type, messages)
    {
        this._stack[type] =
            '<ul class="' + type + '-msg" title="' + type + '" onclick="MessageStack.hide(this)">' +
                this._parse(messages) +
            '</ul>';
    },

    _parse: function(object)
    {
        if (typeof object == 'string') {
            return object;
        }

        var parsed = '';

        for (var i in object) {
            if (typeof object[i] == 'function') {
                continue;
            }
            parsed += '<li>' + object[i] + '</li>';
        }
        return parsed;
    },

    setTarget: function(target)
    {
        this._target = target;
        return this;
    }

}