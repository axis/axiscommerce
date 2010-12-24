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

var Order;
Ext.onReady(function(){

    Order = {
        _config: {},
        activeId: 0,
        record: Ext.data.Record.create([
            {name: 'id', type: 'int'},
            {name: 'number'},
            {name: 'site_id', type: 'int'},
            {name: 'date_purchased_on', type: 'date', dateFormat: 'Y-m-d H:i:s'},
            {name: 'customer_name'},
            {name: 'customer_email'},
            {name: 'customer_id', type: 'int'},
            {name: 'order_status_id', type: 'int'},
            {name: 'order_total_base'},
            {name: 'order_total_customer'}
        ]),
        window: {},
        form: {},
        add: function() {
            var form = Order.form.getForm();
            Ext.getCmp('grid-products').getStore().removeAll();
            form.clear();

            var store = Ext.StoreMgr.lookup('storeOrderStatusses');
            store.removeAll();
            store.baseParams = {'statusId': 0};
            store.load();

            Order.window.show();
        },
        load: function(id) {
            if (!id) {
                alert('Select order to edit'.l());
                return false;
            }
            Order.activeId = id;
//            Order.form.getForm().clear();
            var form = Order.form.getForm();
            form.clear();
//            console.log(Order.form.getForm().clear);

            form.load({
                url: Axis.getUrl('sales_order/get-order-info/orderId/') + Order.activeId,
                method: 'post',
                success: function(form, action) {
                    var data = Ext.decode(action.response.responseText).data;
                    Order.window.setTitle('Order'.l() + ' ' + data.order.number);
                    Order.window.show();

                    Ext.getCmp('grid-products').getStore().loadData(data.products);
//
                    store = Ext.StoreMgr.lookup('storeOrderStatusses');
                    store.removeAll();
                    store.baseParams = {'statusId': data.order.order_status_id};
                    store.load();

                    store = Ext.StoreMgr.lookup('storeAddresses');
                    store.removeAll();
                    store.baseParams = {'customerId': data.order.customer_id};
                    store.load();

                    form.findField('order[customer_id]').loadData(data);
//                    Ext.getCmp('box-subtotal-info').el.dom.innerHTML = data.totals.form;

                    Ext.getCmp('grid-history').getStore().loadData(data.history);

                    form.findField('order[billing_address_type]').setValue(1); //old address
                    form.findField('order[delivery_address_type]').setValue(1);

                    Ext.getCmp('order[payment_form]').update(data.payment.form);
                }
            });
        },
        save: function(hide) {
            hide = hide || false;
            var form = Order.form.getForm();

            form.submit({
                url: Axis.getUrl('sales_order/save'),
                params: {
                    products: Ext.getCmp('grid-products').getStore().getData()
                },
                submitEmptyText: false,
                method: 'post',
                success : function(form, action) {
                    var data = Ext.decode(action.response.responseText).data;
                    if (hide) {
                        Order.window.hide();
                    } else {
                        Order.load(data.order_id);
                    }
                    Ext.getCmp('grid-order').getStore().reload();
                }
            });
        },
        //@todo change status many order
        batchSave: function() {
            var data = {};
            var modified = Ext.getCmp('grid-order').getStore().getModifiedRecords();
            if (!modified.length)
                return;

            for (var i = 0; i < modified.length; i++) {
                data[modified[i]['id']] = modified[i]['data'];
            }

            Ext.Ajax.request({
                url: Axis.getUrl('sales_order-status/batch-save'),
                params: {
                    data: Ext.encode(data)
                },
                callback: function() {
                    Ext.getCmp('grid-order').getStore().commitChanges();
                    Ext.getCmp('grid-order').getStore().reload();
                }
            });

        },
        remove: function(orderIds) {
            if (!confirm('Are you sure?'.l())) {
                return false;
            }

            Ext.Ajax.request({
                url: Axis.getUrl('sales_order/delete'),
                params: {data: Ext.encode(orderIds)},
                callback: function(){
                    Ext.getCmp('grid-order').getStore().reload();
                }
            });
        },
        beforePrint: function()
        {
            $('#print-label-address-type').val('billing');
            $('#print-invoice').val(false);
            $('#print-packingslip').val(false);
            $('#print-label').val(false);
            var selectedItems = Ext.getCmp('grid-order')
                .getSelectionModel().selections.items;
            if (!selectedItems.length) {
                return false;
            }
            var data = {};
            for (var i = 0; i < selectedItems.length; i++) {
                data[i] = selectedItems[i]['data']['id'];
            }
            $('#print-form-data').val(Ext.encode(data));
            return true;
        },
        setConfig: function(config) {
            Order._config =  config;
        },
        config : function () {
            return Order._config;
        }
    };
}, this);

