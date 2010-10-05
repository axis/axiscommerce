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
 */

Axis.AttributeDetails = Ext.extend(Ext.util.Observable, {
    
    constructor: function(config) {
        Ext.apply(this, config);
        
        this.events = {
            /**
             * @event cancel
             * Fires when cancel button was pressed
             */
            'cancelpress': true,
            /**
             * @event ok
             * Fires when ok button was pressed
             * @param {Ext.data.Record} Record with form data
             */
            'okpress': true
        };
        Axis.AttributeDetails.superclass.constructor.call(this);
        
        this.formPanel = new Axis.FormPanel({
            border: true,
            bodyStyle: 'padding: 10px;',
            defaults: {
                anchor: '100%'
            },
            items: [{
                allowBlank: true,
                fieldLabel: 'SKU prefix'.l(),
                name: 'sku_prefix',
                value: this.sku_prefix,
                xtype: 'textfield'
            }, {
                allowNegative: false,
                allowBlank: false,
                fieldLabel: 'Q-ty'.l(),
                name: 'quantity',
                value: 1,
                xtype: 'numberfield'
            }, {
                allowNegative: false,
                allowBlank: false,
                fieldLabel: 'Cost'.l(),
                name: 'cost',
                value: 0,
                xtype: 'numberfield'
            }, {
                allowBlank: false,
                fieldLabel: 'Price'.l(),
                name: 'price',
                value: 0,
                xtype: 'numberfield'
            },
            ProductWindow.modifierType.cloneConfig({
                fieldLabel: 'Price modifier'.l(),
                name: 'price_type',
                value: 'by'
            }), {
                allowBlank: false,
                fieldLabel: 'Weight'.l(),
                name: 'weight',
                value: 0,
                xtype: 'numberfield'
            },
            ProductWindow.modifierType.cloneConfig({
                fieldLabel: 'Weight modifier'.l(),
                name: 'weight_type',
                value: 'by'
            })]
        });
        
        this.window = new Axis.Window({
            border: false,
            closable: false,
            title: 'Default data'.l(),
            width: 350,
            height: 320,
            items: [
                this.formPanel
            ],
            buttons: [{
                icon: Axis.skinUrl + '/images/icons/accept.png',
                text: 'Ok'.l(),
                scope: this,
                handler: this.okPress
            }, {
                icon: Axis.skinUrl + '/images/icons/cancel.png',
                text: 'Cancel'.l(),
                scope: this,
                handler: this.cancelPress
            }],
            listeners: {
                hide: {
                    scope: this,
                    fn: this.hide
                }
            }
        });
    },
    
    setSkuPrefix: function(value) {
        this.formPanel.getForm().findField('sku_prefix').setValue(value);
        return this;
    },
    
    hideField: function(field) {
        var field = this.formPanel.getForm().findField(field);
        field.disable();
        field.hide();
        return this;
    },
    
    showField: function(field) {
        var field = this.formPanel.getForm().findField(field);
        field.enable();
        field.show();
        return this;
    },
    
    destroy: function() {
        if (this.formPanel) {
            this.formPanel.destroy();
        }
        if (this.window) {
            this.window.destroy();
        }
        this.purgeListeners();
    },
    
    hide: function() {
        this.window.hide();
    },
    
    show: function() {
        this.window.show();
    },
    
    okPress: function() {
        if (false === this.fireEvent('okpress', this.formPanel.getForm().getFieldValues())) {
            return;
        }
        this.hide();
    },
    
    cancelPress: function() {
        this.fireEvent('cancelpress');
        this.hide();
    }
    
});
