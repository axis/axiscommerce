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

var Locale = 
{
    iterator: 0,
    
    addLocale: function()
    {
        this.add('locale');
    },
    
    addCurrency: function()
    {
        this.add('currency');//@todo add currency customization
    },
    
    add: function(type)
    {
        var source = $('#default-' + type).clone()
            .attr('id', '')
            .attr('name', type + '[' + this.iterator++ + ']')
            .hide();
            
        var dest = $('#additional-' + type + ' ul');
        
        if (!dest.length) {
            $('#additional-' + type).append('<ul></ul>');
            dest = $('#additional-' + type + ' ul');
        }
        
        dest.prepend(source);
        source.wrap('<li>');
        source.before('<a href="javascript:void(0)" class="btn-remove f-right" onclick="Locale.remove(this)">remove</a>');
        source.show();
    },
    
    remove: function(e)
    {
        $(e).parent('li').remove();
    }
    
};

Spinner = 
{
    show: function()
    {
        var size = getViewportSize();
        var spinner = document.createElement('div');
        $(spinner).hide()
            .css({
                'position': 'absolute',
                'left': size.width / 2 - 170 / 2,
                'top': size.height / 2 - 80 / 2 - 100,
                'width': 250,
                'height': 100
            })
            .html('<p class="message">' + this.message + '</p>')
            .addClass('spinner');
        document.body.appendChild(spinner);
        
        var mask = document.createElement('div');
        $(mask).hide()
            .css({
                'position': 'absolute',
                'left': 0,
                'top': 0,
                'width': size.width,
                'height': size.height
            })
            .addClass('mask');
        document.body.appendChild(mask);
        
        $(spinner).show();
        $(mask).show();
    },
    
    hide: function()
    {
        $('.spinner').remove();
    }
};

/* modules */
(function($) {
    $.fn.initModules = function(tabs, spinnerMessage) 
    {
        Spinner.message = spinnerMessage;
        buildTabs(this, tabs.split(','));
        setActiveTab('all');
    }
    
    buildTabs = function(el, tabs)
    {
        var list = $(document.createElement('ul'))
            .addClass('module-tabs')
            .insertBefore(el);
        
        for (var i = tabs.length - 1; i >= 0; i--) {
            var text = tabs[i].charAt(0).toUpperCase() + tabs[i].substr(1);
            var source = $(document.createElement('span'))
                .text(text);
                
            var wrapper = $(document.createElement('li'))
                .attr('id', tabs[i])
                .addClass('package');
                
            list.prepend(source);
            source.wrap(wrapper);
        }
        addObservers();
        initFilters();
        applyFilters();
    }
    
    initFilters = function()
    {
        $('.module-filter li').click(function(){
            var filter = $(this).attr("id").substr(5);
            if ($(this).hasClass('active')) {
                if (getActiveTab() == 'all') {
                    $('.module.'+ filter).show();
                }
                $(this).removeClass('active');
            } else {
                if (getActiveTab() == 'all') {
                    $('.module.'+ filter).hide();
                }
                $(this).addClass('active');
            }
        });
    }
    
    addObservers = function()
    {
        /* module tabs */
        $('.module-tabs .package').click(function() {
            $('.module-tabs > li').removeClass('active');
            var id = $(this).attr('id');
            setActiveTab(id);
            if (id == 'all') {
                $('.module-container li.all').show()
                $('.module-filter').show();
                applyFilters();
            } else {
                $('.module-container li:not(.' + id + ')').hide()
                $('.module-container li.' + id + ', .select-all').show()
                $('.module-filter').hide();
            }
        })
        
        /* installation modes */
        $('.installation-mode input:radio').click(function(a, b){
            if (this.id == 'standard') {
                $('#form-modules').hide();
                $('#form-modules .module').addClass('active');
                $('#form-modules input:checkbox').attr('checked', 'checked');
            } else {
                $('#form-modules').show();
            }
        })
        
        /* module rows */
        $('.module').click(function(){
            if ($(this).children('input:checkbox').attr('checked') && !$(this).hasClass('required')) {
                $(this).removeClass('active');
                $(this).children('input:checkbox').removeAttr('checked');
            } else {
                $(this).addClass('active');
                $(this).children('input:checkbox').attr('checked', 'checked');
            }
        })
        
        $('.module label').click(function(){
            $(this).parent('.module').click();
            return false;
        })
        
        /* select all */
        $('.select-all').click(function(){
            if ($('.module').not('.required').not('.select-all').hasClass('active')) {
                $('.module').not('.required').removeClass('active');
                $('.module').not('.required').children('input').removeAttr('checked');
            } else {
                $('.module').addClass('active');
                $('.module').children('input').attr('checked', 'checked');
            }
        })
        
        /* form submit */
        $('button#submit').click(function() {
            $(this).attr('disabled', 'disabled');
            $('#form-modules input:disabled').removeAttr('disabled');
            Spinner.show();
            $('#form-modules').submit();
        })
    }
    
    getActiveTab = function()
    {
        return $('.module-tabs .active').attr('id');
    }
    
    setActiveTab = function(id)
    {
        $('.module-tabs li#' + id).addClass('active');
    }
    
    applyFilters = function()
    {
        $('.module-filter li.active').each(function() {
            var filter = $(this).attr("id").substr(5);
            $('.module.' + filter).hide();
        })
    }
    
})(jQuery);

function getViewportSize(){
    var viewportwidth = viewportheight = 0;
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