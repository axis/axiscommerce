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

(function($) {
    $.fn.axisWindow = function(trigger, params) {
        return new AxisWindow(this, trigger, params);
    }
})(jQuery);

var AxisWindow = function(el, trigger, params) {
    this.window = el;
    this.window.hide();
    this.window.appendTo(document.body);

    params.trigger = trigger;
    this.params = $.extend({
        modal   : false,
        width   : 350,
        opacity : 0.3
    }, params);

    this.window.css({
        width: this.params.width
    });

    var self = this;
    $(params.trigger).click(function(e) {
        e.preventDefault();
        self.show();
    });
    $(document).keydown(function(e) {
        var keyCode = e.keyCode;
        if (keyCode == 27) {
            self.hide();
        }
    });
};

AxisWindow.prototype.show = function() {
    var viewportSize = BrowserWindow.getViewportSize(),
        scrollOffset = BrowserWindow.getScrollOffset();
    this.window.css({
        left: scrollOffset.left + viewportSize.width / 2 - this.window.width() / 2,
        top : scrollOffset.top + viewportSize.height / 2 - this.window.height() / 1.2
    });

    if (this.params.modal) {
        this.mask(true);
    }

    this.window.show();

    if (this.params.onShow) {
        this.params.onShow.apply(this);
    }
};

AxisWindow.prototype.hide = function() {
    if (this.blocked) {
        return false;
    }
    if (this.params.modal) {
        this.mask(false);
    }
    this.window.hide();
};

AxisWindow.prototype.mask = function(flag) {
    if (!flag) {
        this.overlay.remove();
        this.overlay = null;
        return;
    } else if (!this.overlay) {
        var pageSize = BrowserWindow.getPageSize();
        this.window.before('<div class="axis-window-mask" style="display:none;"></div>')
        this.overlay = $('.axis-window-mask');
        this.overlay.css({
            width   : pageSize.width,
            height  : pageSize.height
        });
        this.overlay.fadeTo(0, this.params.opacity);
        var self = this;
        this.overlay.click(function() {
            self.hide();
        });
    }
    this.overlay.show();
};

AxisWindow.prototype.setBlocked = function(flag) {
    this.blocked = flag;
};
