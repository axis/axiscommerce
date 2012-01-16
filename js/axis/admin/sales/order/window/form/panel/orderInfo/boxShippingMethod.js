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

Ext.onReady(function(){

    var storeShippingMethod = new Ext.data.Store({
        storeId: 'storeShippingMethod',
        url:  Axis.getUrl('sales/shipping/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, [
            {name: 'code',  type: 'string'},
            {name: 'title', type: 'string'},
            {name: 'name',  type: 'string'},
            {name: 'price', type: 'float'},
            {name: 'tax',   type: 'float'}
        ]),
        pruneModifiedRecords: true,

        reloadList :function(params) {
            var form = Order.form.getForm();
            var store = Ext.StoreMgr.lookup('storeGridProducts');

            if (!store.data.items.length) {
                return;
            }

            params = Ext.applyIf(params || {}, {
                billing_country_id  : form.findField('order[billing_country]').getValue(),
                billing_zone_id     : form.findField('order[billing_state]').getValue(),
                delivery_country_id : form.findField('order[delivery_country]').getValue(),
                delivery_zone_id    : form.findField('order[delivery_state]').getValue(),
                postcode            : form.findField('order[delivery_postcode]').getValue(),
                customer_group_id   : form.findField('customer[group_id]').getValue(),
                quantity            : store.sum('quantity'),
                weight              : store.sum('product_subweight'),
                subtotal            : store.sum('product_subtotal'),
                payment_method_code : form.findField('order[payment_method_code]').getValue()

            });

            var oldValue = form.findField('order[shipping_method_code]').getValue();
            Ext.StoreMgr.lookup('storeShippingMethod').load({
                params: params,
                callback: function(r, options, success) {
                    if (!success) {
                        return;
                    }
                    var indexOldValue = Ext.StoreMgr.lookup('storeShippingMethod')
                        .findBy(function(record) {
                            return record.get('code') === oldValue;
                    });

                    if (-1 !== indexOldValue) {
//                        form.findField('order[shipping_method_code]').setValue();
                        if (!oldValue) {
                            return;
                        }
                        form.findField('order[shipping_method_code]').setValue(oldValue);
//                        var record = Ext.StoreMgr.lookup('storeShippingMethod').getAt(indexOldValue);
//                        form.findField('totals[shipping]').setValue(record.get('price'));
//                        debugger;
//                        form.findField('totals[shipping_tax]').setValue(record.get('tax'));
                    }
                }
            });

        }
    });
    var cmpShippingCode = new Ext.form.ComboBox({
        hideLabel: true,
        plugins: inlineField,
        anchor: '-10',
        name: 'order[shipping_method_code]',
        hiddenName: 'order[shipping_method_code]',
        allowBlank: false,
        triggerAction: 'all',
        displayField: 'name',
        valueField: 'code',
        typeAhead: true,
        mode: 'local',
        store: storeShippingMethod,
        lazyRender: true,
        listeners: {
            beforeselect: function(element, record, index) {
                Ext.StoreMgr.lookup('storePaymentMethod').reloadList();
                var store = element.getStore();
                var form = Order.form.getForm();
                var config = totalsConfig;
                if (true === config.shipping) {
                    form.findField('totals[shipping]').setValue(record.get('price'));
                }
                if (true === config.shippingTax) {
                    form.findField('totals[shipping_tax]').setValue(record.get('tax'));
                }
                form.findField('order[shipping_method]').setValue(record.get('title'));
            },
            focus: function(combo) {
                if (!Ext.isDefined(this.store.totalLength)) {
                    this.store.reloadList();
                }
            }
        }
    });

    cmpShippingCode.setValue = cmpShippingCode.setValue
        .createInterceptor(function(value) {
            if (value) {
                return;
            }
            if (!Ext.isDefined(this.store.totalLength)) {
                this.store.on('load', this.setValue.createDelegate(this, arguments), null, {single: true});
                return;
            }
    });

    Order.form.boxShippingMethod = {
        id: 'box-shipping-method',
        title: 'Shipping method'.l(),
        items: [cmpShippingCode, {
                xtype: 'hidden',
                name: 'order[shipping_method]'
            }]
    };

}, this);