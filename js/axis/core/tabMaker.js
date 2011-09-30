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
    $.fn.makeTabs = function(settings) {
        var config = {
            'effect': 'none', // @todo fade, slide
            'duration': 300
        };

        $.extend(config, settings);
        $('.tab-container').each(function(){
            buildTabs(this);
            setActiveTab(this, getIndexByUrlHash());
            addObservers(this);
        })

        function buildTabs(container) {
            var tabs = $(document.createElement('ul'))
                .addClass('tabs')
                .prependTo(container);

            var tabsContent = $(document.createElement('div'))
                .addClass('content')
                .insertAfter(tabs);

            $('.tab', container).each(function(){
                var tab = $('.head', this).eq(0);
                tabs.append(tab);
                tab.wrap('<li>');

                var tabContent = $('.content', this).eq(0);
                tabContent.removeClass().addClass(this.className);
                tabsContent.append(tabContent);
                $(this).remove();
            })
        }

        function addObservers(container) {
            var links = $('.tabs a', container);
            links.click(function(e) {
                setActiveTab(container, links.index(this));
            })
            links.hover(
                function() {
                    $(this).parents('li').addClass('over');
                },
                function() {
                    $(this).parents('li').removeClass('over');
                }
            );
        }

        function setActiveTab(container, index) {
            $('.tabs li, .content .tab', container).removeClass('active');
            $('.tabs li:eq(' + index + '), .content .tab:eq(' + index + ')', container).addClass('active');

            switch (config.effect) {
                case 'fade':
                case 'slide':
                default:
                    switchTabDisplay(container, index);
                break;
            }
        }

        function switchTabDisplay(container, index) {
            $('.content .tab', container).not(':eq(' + index + ')').css('display', 'none');
            $('.content .tab:eq(' + index + ')', container).css('display', 'block');
        }

        function getIndexByUrlHash(container) {
            var hash = window.location.hash;
            if ('' === hash) {
                return 0;
            }

            // #tab- = 5 letters (see app/design/front/default/templates/core/box/tab.phtml)
            var el = $('.' + hash.substr(5));
            if (!el.length) {
                return 0;
            }

            var container = el.parents('.tab-container');
            if (!container.length) {
                return 0;
            }

            $('html, body').scrollTop(container.offset().top);
            return $('.content .tab', container).index(el);
        }
    }
})(jQuery);
