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

Ext.onReady(function(){

    var storePaymentMethod = new Ext.data.Store({
        storeId: 'storePaymentMethod',
        url:  Axis.getUrl('sales/payment/list'),
//        totalProperty: 'count',
//        autoLoad: true,
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, [
            {name: 'code', type: 'string'},
            {name: 'name', type: 'string'},
            {name: 'form', type: 'string'}
        ]),
        pruneModifiedRecords: true,
        reloadList: function() {
            var form = Order.form.getForm();
            var store = Ext.StoreMgr.lookup('storeGridProducts');

            var params = {
                shipping_method_code : form.findField('order[shipping_method_code]').getValue(),
                country_id           : form.findField('order[billing_country]').getValue(),
                zone_id              : form.findField('order[billing_state]').getValue(),
                quantity             : store.sum('quantity'),
                weight               : store.sum('product_subweight'),
                subtotal             : store.sum('product_subtotal')
            };
            for (index in params) {
                if ('zone_id' == index || 'shipping_method_code' == index) {
                    break;
                }
                var param = params[index];
                if ('' === param || null === param) {
                    alert(index);
                    return;
                }
                
            }
            
            Ext.StoreMgr.lookup('storePaymentMethod').load({params: params});
        }
    });
    var cmpPaymentCode = new Ext.form.ComboBox({
        hideLabel: true,
        xtype: 'combo',
        name: 'order[payment_method_code]',
        hiddenName: 'order[payment_method_code]',
        allowBlank: false,
        triggerAction: 'all',
        displayField: 'name',
        valueField: 'code',
        typeAhead: true,
        mode: 'local',
        store: storePaymentMethod,
        plugins: inlineField,
        lazyRender: true,
        anchor: '-10',
        listeners: {
            beforeselect: function (combo, record, index) {
                
                var value = combo.getStore().getAt(index);//=== record
                if (combo.lastSelectionText == value.get('name')) {
                    return false;
                }
                var form = Order.form.getForm();
                
                Ext.StoreMgr.lookup('storeShippingMethod').reloadList({
                    payment_method_code: value.get('code')
                });

                form.findField('order[payment_method]').setValue(
                    value.get('name')
                );
                Ext.getCmp('order[payment_form]').update(value.get('form'));
            },
            focus: function(combo) {
                 if (!Ext.isDefined(this.store.totalLength)) {
                     this.store.reloadList();
                 }
            }
        }
    });
    cmpPaymentCode.setValue = cmpPaymentCode.setValue
        .createInterceptor(function(value) {
            if (!Ext.isDefined(this.store.totalLength)) {
                this.store.on('load', this.setValue.createDelegate(this, arguments), null, {single: true});
                return;
            }
    });

       Order.form.boxPaymentMethod = {
        id: 'box-payment-method',
        title : 'Payment method'.l(),
        items: [cmpPaymentCode, {
                xtype: 'hidden',
                name: 'order[payment_method]'
            }, new Ext.Panel({
                id: 'order[payment_form]',
                width: 300,
//                header: false,
                border: false,
                html: ''
            })
        ]
    };
    
}, this);