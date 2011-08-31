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

    ////////////////////////////////////////////////////////////////////////////
    ///// customer box start
    ////////////////////////////////////////////////////////////////////////////

    var storeCustomerGroup = new Ext.data.Store({
//        storeId: 'storeCustomerGroup',
        autoLoad: true,
        url: Axis.getUrl('account/group/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, [
            {name: 'id',          type: 'int',    mapping: 'id'},
            {name: 'name',        type: 'string', mapping: 'name'},
            {name: 'description', type: 'string', mapping: 'description'}
        ]),
        pruneModifiedRecords: true
    });

    var cmpCustomerGroup = new Ext.form.ComboBox({
        fieldLabel: 'Group'.l(),
        xtype: 'combo',
        name: 'customer[group_id]',
        hiddenName: 'customer[group_id]',
        triggerAction: 'all',
        displayField: 'name',
        valueField: 'id',
        typeAhead: true,
//        mode: 'local',
        store: storeCustomerGroup,
        plugins: inlineField,
        anchor: '-10',
        allowBlank: false,
        lazyRender: true,
        listeners: {
            beforeselect: function(combo, record, index) {
                Ext.StoreMgr.lookup('storeShippingMethod').reloadList({
                    customer_group_id: combo.getStore().getAt(index).get('id')
                });
            }
        }
    });

    cmpCustomerGroup.setValue = cmpCustomerGroup.setValue.createSequence(
        function(value) {
            if (!value) { //fix
                return;
            }
            Ext.StoreMgr.lookup('storeShippingMethod').reloadList({
                customer_group_id: value
            });
    });

    var storeCustomers = new Ext.data.Store({
        storeId: 'storeCustomers',
//        autoLoad: true,
        reader: new Ext.data.JsonReader({
            idProperty: 'id',
            root: 'data',
            totalProperty: 'count'
        }, Ext.data.Record.create([
            {name: 'id',         type: 'int'},
            {name: 'email'},
            {name: 'firstname'},
            {name: 'lastname'},
            {name: 'site_id',    type: 'int'},
            {name: 'group_id',   type: 'int'},
            {name: 'created_at', type: 'date', dateFormat: 'Y-m-d'},
            {name: 'is_active',  type: 'int'}
        ])),
//        remoteSort: true,
        proxy: new Ext.data.HttpProxy({
            method: 'POST',
            url: Axis.getUrl('account/customer/list')
        })
        ,listeners: {
            load: function(store, records, options) {
                var form = Order.form.getForm();
                pageSize = form.findField('order[customer_id]').pageSize;
//                store.insert(pageSize,
                store.add(new store.recordType({
                    id: -1,
                    email: 'Guest'.l(),
                    firstname: '',
                    lastname: '',
                    group_id: CUSTOMER_GROUP_GUEST_ID,
                    site_id: 1

                }));
//                store.insert(pageSize++,
                store.add(new store.recordType({
                    id: -2,
                    email: 'Add new Customer',
                    firstname: '',
                    lastname: '',
                    group_id: CUSTOMER_GROUP_GENERAL_ID,
                    site_id: 1

                }));
            }
        }
    });

//     Custom rendering Template
    var resultTpl = new Ext.XTemplate(
        '<tpl for="."><div class="x-combo-list-item">',
            '<h3><span>{email}',
                '<tpl if="\'\' !== firstname || \'\' !== lastname">',
                    ' by {firstname} {lastname}',
                '</tpl>',
            '</span></h3>',
        '</div></tpl>'
    );

    var cmpCustomer = new Ext.form.ComboBox({
        fieldLabel: 'Customer'.l(),
        name: 'order[customer_id]',
        hiddenName: 'order[customer_id]',
        triggerAction: 'all',
        displayField: 'email',
        valueField: 'id',
        typeAhead: true,
//        mode: 'local',

        tpl: resultTpl,
        itemSelector: 'div.x-combo-list-item',
        loadingText: 'Loading...'.l(),
        pageSize: 3,
        listWidth: 300,

        store: storeCustomers,
        plugins: inlineField,
        anchor: '-10',
//        lazyRender: true,
        listeners: {
            beforeselect: function(combo, record, index) {
                var form = Order.form.getForm();
                var readOnly = record.get('id') == -2 ? false : true;
                var field = form.findField('customer[firstname]');
                
                field.setValue(record.get('firstname'));
                field.readOnly = readOnly;

                field = form.findField('customer[lastname]');
                field.setValue(record.get('lastname'));
                field.readOnly = readOnly;

                form.findField('order[customer_email]').setValue(
                    -1 !== record.get('email').indexOf('@') ? record.get('email') : ''
                );
                cmpCustomerGroup.setValue(record.get('group_id'));
                if (!readOnly) {
                    return;
                }
                store = Ext.StoreMgr.lookup('storeAddresses');
                store.removeAll();
                store.baseParams = {'customerId': record.get('id')};
                store.load();

//                form.findField('order[ip_address]').setValue(
//                    '127.0.0.1'
//                );
            }
        }
    });

    // call in Order.load
    cmpCustomer.loadData = function(data) {
        var store = this.getStore();
//        store.removeAll();
        if (count = store.getCount()) {
            count--;
            if (0 < store.getAt(count).get('id')) {
                store.removeAt(count);
            }
        }
        if (0 != data.order.customer_id) {
            store.add(new store.recordType({
                id:        data.order.customer_id,
                email:     data.order.customer_email,
                firstname: data.customer.firstname,
                lastname:  data.customer.lastname,
                group_id:  data.customer.group_id,
                site_id:   data.order.site_id

            }));
            this.setValue(data.order.customer_id);
        } else {
            this.setValue('Guest'.l());
        }
    };

    Order.form.boxCustomer = {
        title : 'Customer'.l(),
        id: 'box-customer-info',
        defaults: {
            plugins: inlineField,
            anchor: '-10'
        },
        items: [cmpCustomer, {
                fieldLabel: 'Firstname'.l(),
                xtype: 'textfield',
                name: 'customer[firstname]'
//                ,readOnly: true
            }, {
                fieldLabel: 'Lastname'.l(),
                xtype: 'textfield',
                name: 'customer[lastname]'
//                ,readOnly: true
            }, {
                fieldLabel: 'Email'.l(),
                xtype: 'textfield',
                name: 'order[customer_email]',
                allowBlank: false
            }, cmpCustomerGroup, {
                fieldLabel: 'Ip'.l(),
//                xtype: 'textfield',
                xtype: 'hidden',
                name: 'order[ip_address]'
            }
        ]
    };
    
}, this);