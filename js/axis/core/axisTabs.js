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

(function($) {
    $.fn.axisTabs = function(settings) {
        var containers = $.unique(
            $('.tab').map(function() {
                return $(this).attr('class').match(/tabs-[^\s]+/);
            }).get()
        );

        $.map(containers, function(val, i) {
            new AxisTabs(val);
        });
    }
})(jQuery);

var AxisTabs = function(tabsSelector) {
    this.selector = tabsSelector;
    var elements  = $('.' + this.selector);

    // build
    this.container = $(document.createElement('div'))
        .addClass('tab-container box ' + this.selector)
        .insertBefore(elements.get(0));

    this.head = $(document.createElement('ul'))
        .addClass('tabs')
        .prependTo(this.container);

    this.content = $(document.createElement('div'))
        .addClass('content')
        .insertAfter(this.head);

    var self = this;
    elements.each(function() {
        self.add(this);
    });

    // show active
    var index = this.getTabIndexByUrlHash();
    this.activate(index);

    // if url has hash for tab - scroll to it
    if (this.hasHash) {
        this.scrollTo();
    }
};

AxisTabs.prototype.activate = function(index) {
    $('.tabs li, .content .tab', this.container).removeClass('active');
    $('.tabs li:eq(' + index + '), .content .tab:eq(' + index + ')', this.container).addClass('active');

    $('.tab', this.content).hide();
    $('.tab:eq(' + index + ')', this.content).show();
};

AxisTabs.prototype.scrollTo = function() {
    $('html, body').scrollTop(this.container.offset().top);
};

AxisTabs.prototype.add = function(tab) {
    // DOM
    var head = $('.head', tab).eq(0);
    this.head.append(head);
    head.wrap('<li>');

    var content = $('.content', tab).eq(0);
    content.removeClass()
        .addClass(tab.className)
        .removeClass(this.selector)
        .hide();
    this.content.append(content);
    $(tab).remove();

    // observers
    var index = this.head.children().length - 1,
        self  = this,
        link  = head.find('a');

    link.click(function(e) {
        self.activate(index);
    });
    link.hover(
        function() {
            $(this).parents('li').addClass('over');
        },
        function() {
            $(this).parents('li').removeClass('over');
        }
    );
};

AxisTabs.prototype.getTabIndexByUrlHash = function() {
    var hash = window.location.hash;
    if ('' === hash) {
        return 0;
    }

    // #tab- = 5 letters (see app/design/front/default/templates/core/box/tab.phtml)
    var el = this.container.find('.' + hash.substr(5));
    if (!el.length) {
        return 0;
    }

    this.hasHash = true;
    return $('.content .tab', this.container).index(el);
};
