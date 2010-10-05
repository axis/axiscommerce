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

/* Ajax spinner implementation */
Ext.Ajax.on('beforerequest', function(connection, options){

    Spinner.show();

});

/* Ajax messagestack implementation */
Ext.Ajax.on('requestcomplete', function(connection, response, options){

    Spinner.hide();

    if (!isJSON(response.responseText)) {
        return;
    }

    var obj = Ext.decode(response.responseText);

    if ((obj.silent && obj.silent == true) || !obj.messages) {
        return;
    }

    if (typeof obj.messages == 'object' && obj.messages.length !== 0) {
        MessageStack.init(obj.messages).render();
    }
});

Ext.Ajax.on('requestexception', function(connection, response, options) {
    Spinner.hide();
});

var Spinner = {

    count: 0,

    show: function()
    {
        ++this.count;
        if (jQuery('#mask').length) {
            return;
        }
        var viewport = getViewportSize();
        var scrollOffset = getScrollOffset();
        jQuery(document.body).append(
            '<div id="mask" style="">' +
            '<div id="mask-loading" class="ext-el-mask-msg x-mask-loading"><div>' + 'Loading'.l() + '</div></div>' +
            '</div>'
        );
        jQuery('#mask').css({
            zIndex: 20000,
            position: 'absolute',
            width: '300px',
            height: '150px',
            top: scrollOffset.top + viewport.height / 2 - 75,
            left: scrollOffset.left + viewport.width / 2 - 150
        });
        var loadingLabel = jQuery('#mask-loading');
        loadingLabel.css({
            top: 75 - loadingLabel.height() / 2,
            left: 150 - loadingLabel.width() / 2
        });
    },

    hide: function()
    {
        if (!--this.count) {
            jQuery('#mask').remove();
        }
    }
}

var MessageStack = {
    _target: '#inside-box',
    _stack: [],
    _sortOrder: ['error', 'warning', 'notice', 'success'],

    init: function(messages)
    {
        this.clear();
        this._toString(messages);
        return this;
    },

    render: function()
    {
        jQuery(this._target).prepend('<div id="messages"></div>');
        for (var i in this._stack) {
            if (typeof this._stack[i] != 'string' || this._stack[i] == '')
                continue;
            jQuery('#messages', this._target).append(this._stack[i]);
        }
    },

    clear: function()
    {
        this._stack = [];
        jQuery('#messages', this._target).remove();
    },

    hide: function(e)
    {
        jQuery(e).fadeOut();
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
        if (typeof object == 'string')
            return object;

        var parsed = '';

        for (var i in object) {
            if (typeof object[i] == 'function')
                continue;
            parsed += '<li>' + object[i] + '</li>';
        }
        return parsed;
    }

}

function getViewportSize(){
    var viewportwidth = 0;
    var viewportheight = 0;
    if (typeof window.innerWidth != 'undefined') {
        viewportwidth = window.innerWidth;
        viewportheight = window.innerHeight;
    } else if (typeof document.documentElement != 'undefined' &&
        typeof document.documentElement.clientWidth != 'undefined' &&
        document.documentElement.clientWidth != 0) { // IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)

        viewportwidth = document.documentElement.clientWidth;
        viewportheight = document.documentElement.clientHeight;
    } else {// older versions of IE
        viewportwidth = document.getElementsByTagName('body')[0].clientWidth;
        viewportheight = document.getElementsByTagName('body')[0].clientHeight;
    }

    return {
        'width': viewportwidth,
        'height': viewportheight
    };
}

function getScrollOffset() {
    return {
        'left': document.all ? document.documentElement.scrollLeft : window.pageXOffset,
        'top': document.all ? document.documentElement.scrollTop : window.pageYOffset
    };
}

function isJSON(str) {
    if (str == '') return false;
    str = str.replace(/\\./g, '@').replace(/"[^"\\\n\r]*"/g, '');
    return (/^[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]*$/).test(str);
}