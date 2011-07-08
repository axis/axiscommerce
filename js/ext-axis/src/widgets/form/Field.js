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

Ext.intercept(Ext.form.Field.prototype, 'initComponent', function() {
    if (this.allowBlank === false && this.fieldLabel) {
        this.fieldLabel = '<span class="x-form-field-required">*</span> ' + this.fieldLabel;
    }
});

Ext.intercept(Ext.form.Field.prototype, 'afterRender', function() {
    if(this.qtipText){
        Ext.QuickTips.register({
            target:  this.getEl(),
            title: '',
            text: this.qtipText,
            enabled: true
        });

        var label = null;
        var wrapDiv = this.getEl().up('div.x-form-element');
        if (wrapDiv) {
            label = wrapDiv.child('label');
        }
        if (!label) {
            wrapDiv = this.getEl().up('div.x-form-item');
            if (wrapDiv) {
                label = wrapDiv.child('label');
            }
        }

        if (label) {
            Ext.QuickTips.register({
                target:  label,
                title: '',
                text: this.qtipText,
                enabled: true
            });
        }
    }
    
    if (this.description){
        this.getEl().insertHtml('afterEnd', 
            '<div class="x-form-element-description">'
                + this.description
            + '</div>'
        );
    }
});