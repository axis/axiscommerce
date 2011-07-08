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

Axis.getUrl = function(url, ssl) {
    var prefix = Axis.baseUrl;
    if (ssl) {
        prefix = Axis.secureUrl;
    }
    prefix += Axis.langUrl;
    if (url) {
        url = url.replace(new RegExp("^[/]+", "g"), "");
    } else {
        url = '';
    }
    return prefix + '/' + url;
};

BrowserWindow = {
    getViewportSize: function() {
        var viewportwidth = 0,
            viewportheight = 0;
        if (typeof window.innerWidth != 'undefined') {
            viewportwidth = window.innerWidth;
            viewportheight = window.innerHeight;
        } else if (typeof document.documentElement != 'undefined'
            && typeof document.documentElement.clientWidth != 'undefined'
            && document.documentElement.clientWidth != 0) { // IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)

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
    },
    getScrollOffset: function() {
        return {
            'left': document.all ? document.documentElement.scrollLeft : window.pageXOffset,
            'top': document.all ? document.documentElement.scrollTop : window.pageYOffset
        };
    },
    getPageSize: function() {
        return {
            'width': jQuery(document).width(),
            'height': jQuery(document).height()
        };
    }
};

jQuery('document').ready(function($){
    /* List decoration */
    $('ul li:first-child, ol li:first-child').addClass('first');
    $('ul li:last-child, ol li:last-child').addClass('last');

    $('.navigation li').hover(
        function(){ $(this).addClass('over').children('ul').addClass('shown'); },
        function(){ $(this).removeClass('over').children('ul').removeClass('shown'); }
    );
});

/* Table decoration */
function decorateTable(table){
    if ($('#' + table).length > 0) {
        var bodyRows = $('#' + table + ' > tbody > tr'),
            headRows = $('#' + table + ' > thead > tr'),
            footRows = $('#' + table + ' > tfoot > tr'),
            rows    = [bodyRows, headRows, footRows],
            i       = 0,
            rowset;

        while ((rowset = rows[i])) {
            i++;
            if (!rowset.length) {
                continue;
            }

            $(rowset[0]).addClass('first');
            $(rowset[rowset.length-1]).addClass('last');

            rowset.each(function(j) {
                $(this).addClass(j % 2 == 0 ? 'even' : 'odd');

                var cols = $(rowset[j]).children('td, th');

                $(cols[0]).addClass('first')
                $(cols[cols.length-1]).addClass('last');
            });
        }
    }
}
