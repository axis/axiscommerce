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

Ext.form.DateField.override({
    
    valueFormat: 'Y-m-d',
    
//    initValue: function() {
//        this.hiddenField.value = Ext.util.Format.date(
//            Date.parseDate(this.value, this.format), this.valueFormat
//        );
//        Ext.form.DateField.superclass.initValue.call(this);
//    },
    
    onRender: function(ct, position) {
        Ext.form.DateField.superclass.onRender.call(this, ct, position);
        
        this.hiddenField = this.el.insertSibling({
            tag: 'input',
            type: 'hidden',
            name: this.name
        }, 'before', true);
        
        this.el.dom.removeAttribute('name');
        
        this.el.on({
            keyup: {
                scope: this,
                fn: this.updateHidden
            },
            blur: {
                scope: this,
                fn: this.updateHidden
            }
        }, Ext.isIE ? 'after' : 'before');
        
        this.setValue = this.setValue.createSequence(this.updateHidden);
    },
    
//    setValue: function(date) {
//        if (this.rendered) {
//            this.hiddenField.value = this.formatHiddenDate(date);
//        }
//        return Ext.form.DateField.superclass.setValue.call(this, this.formatDate(this.parseDate(date)));
//    },
    
    formatHiddenDate: function(date) {
        if (Ext.isDate(date)) {
            return date.dateFormat(this.valueFormat);
        } else {
            return date;
        }
    },
    
    updateHidden: function() {
        this.hiddenField.value = this.formatHiddenDate(this.getValue());
    }
});