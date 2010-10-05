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

Ext.form.HtmlEditor.override({
    
    sourceEditMode: true,
    
    beforeBlur: function() {
        this[this.sourceEditMode ? 'pushValue' : 'syncValue']();
    },
    
    cleanHtml: function(html) {
        html = String(html);
        if(Ext.isWebKit){ // strip safari nonsense
            html = html.replace(/\sclass="(?:Apple-style-span|khtml-block-placeholder)"/gi, '');
        }

        /*
         * Neat little hack. Strips out all the non-digit characters from the default
         * value and compares it to the character code of the first character in the string
         * because it can cause encoding issues when posted to the server.
         */
        if(html.charCodeAt(0) == this.defaultValue.replace(/\D/g, '')){
            html = html.substring(1);
        }
        return decodeURIComponent(html);
    },
    
    listeners: {
        
        initialize: function() {
            Ext.EventManager.on(this.getDoc(), {
                'blur': this.onBlur,
                'focus': this.onFocus,
                scope: this
            });
            Ext.EventManager.on(this.el, {
                'blur': this.onBlur,
                'focus': this.onFocus,
                scope: this
            });
        }
        
    }
    
});

Ext.sequence(Ext.form.HtmlEditor.prototype, 'onRender', function() {
    this.tb.items.get('sourceedit').toggle(this.sourceEditMode);
    this.toggleSourceEdit(this.sourceEditMode);
});
