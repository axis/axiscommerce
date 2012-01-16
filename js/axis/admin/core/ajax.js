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
 * @copyright   Copyright 2008-2012 Axis
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
        MessageStack.init(obj.messages);
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
            jQuery('#mask').show();
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
            top : scrollOffset.top + viewport.height / 2 - 75,
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
            jQuery('#mask').hide();
        }
    }
}

var MessageStack = {

    _container: null,

    _sortOrder: ['error', 'warning', 'notice', 'success'],

    init: function(messages)
    {
        if (!this._container) {
            this._container = Ext.DomHelper.insertFirst(document.body, {
                id: 'messages'
            }, true);
            this._container.alignTo(document, 't-t');
        }

        this._toString(messages);
    },

    render: function(type, messages)
    {
        var el = Ext.DomHelper.append(this._container, {
            html: this._getMessageMarkup(type, this._parse(messages)),
            style: 'visibility: hidden'
        }, true);

        el.pause(0.5).slideIn();

        if ('success' == type) {
            el.pause(2).ghost('t', {
                remove: true
            });
        }
    },

    remove: function(el)
    {
        Ext.get(el).parent('.msg-container').ghost('t', {
            remove: true
        });
    },

    _toString: function(messages)
    {
        /* first read important messages */
        for (var i in this._sortOrder) {
            if (!messages[this._sortOrder[i]] || !messages[this._sortOrder[i]].length) {
                continue;
            }

            this.render(this._sortOrder[i], messages[this._sortOrder[i]]);

            /* delete processed messages */
            delete messages[this._sortOrder[i]];
        }

        /* read everything else */
        for (var i in messages) {
            if (!messages[i] || !messages[i].length || typeof messages[i] == 'function') {
                continue;
            }

            this.render(i, messages[i]);

            /* delete processed messages */
            delete messages[i];
        }
    },

    _parse: function(object)
    {
        if (typeof object == 'string') {
            return '<li>' + object + '</li>';
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

    _getMessageMarkup: function(type, message)
    {
        return '<div class="msg-container">'+
            '<div class="x-box-tl"><div class="x-box-tr"><div class="x-box-tc"></div></div></div>'+
            '<div class="x-box-ml"><div class="x-box-mr"><div class="x-box-mc">'+
            '<ul class="' + type + '-msg">' + message + '</ul>'+
            '</div></div></div>'+
            '<div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div>'+
            '<a href="#" onclick="MessageStack.remove(this); return false;" class="close-container">Close</a>'+
            '</div>'
        ;
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