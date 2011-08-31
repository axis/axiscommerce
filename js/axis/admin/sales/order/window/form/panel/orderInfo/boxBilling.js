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
    ///// billing box start
    ////////////////////////////////////////////////////////////////////////////

    var storeCountry = new Ext.data.Store({
//        storeId: 'storeCountry',
        autoLoad: true,
        baseParams: {start: 0, limit: 300, show_allcountry: 0},
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('location_country/list')
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, [
            {name: 'id',                type: 'int'},
            {name: 'name',              type: 'string'},
            {name: 'iso_code_2',        type: 'string'},
            {name: 'iso_code_3',        type: 'string'},
            {name: 'address_format_id', type: 'int'}
        ]),
        remoteSort: true,
        pruneModifiedRecords: true
    });
    
    var storeZones = new Ext.data.Store({
//        storeId: 'storeZones',
        autoLoad: true,
        baseParams: {start: 0, limit: 1000, show_allzones: 0},
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('location_zone/list')
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, [
            {name: 'id',         type: 'int'},
            {name: 'code',       type: 'string'},
            {name: 'name',       type: 'string'},
            {name: 'country_id', type: 'int'}
        ]),
        remoteSort: true,
        pruneModifiedRecords: true
    });


    var cmpBillingCountry = new Ext.form.ComboBox({
        id: 'combo-country-billing',
        emptyText: 'Country ',
        name: 'order[billing_country]',
        hiddenName: 'order[billing_country]',
        triggerAction: 'all',
        displayField: 'name',
        valueField: 'id',
        typeAhead: true,
        listWidth: 200,
        mode: 'local',
        store: storeCountry,
        flex: 1,
//        allowBlank: false,
        plugins: inlineField,
        lazyRender: true,
        listeners: {
            select: function(combo, value) {
                var zonesCombo = Ext.getCmp('combo-zone-billing');
                zonesCombo.clearValue();
                zonesCombo.store.filterBy(function(record) {
                    return record.get('country_id') === parseInt(combo.getValue())
                });
            },
            beforerender: function(element) {
                storeZones.filterBy(function(record) {
                    return record.get('country_id') === parseInt(element.getValue())
                });
            },
            beforeselect: function(combo, record, index) {
                cmpBillingAddress.boxChanged();
                Ext.StoreMgr.lookup('storeShippingMethod').reloadList({
                    billing_country_id: combo.getStore().getAt(index).get('id')
                });
            }
        }
    });
//    cmpBillingCountry.setValue = cmpBillingCountry.setValue.createInterceptor(function(value) {
//        console.log(value);
////        this.fireEvent('select', {'combo' : cmpBillingCountry, 'value' : value});
//    });

    var cmpBillingZone = new Ext.form.ComboBox({
        id: 'combo-zone-billing',
        emptyText: 'Zone ',
        name: 'order[billing_state]',
        hiddenName: 'order[billing_state]',
        triggerAction: 'all',
        displayField: 'name',
        listWidth: 200,
        valueField: 'id',
        typeAhead: true,
        mode: 'local',
        store: storeZones,
        lastQuery: '',
        lazyRender: true,
        flex: 1,
        plugins: inlineField,
        listeners: {
            beforeselect: function(combo, record, index) {
                cmpBillingAddress.boxChanged();
                Ext.StoreMgr.lookup('storeShippingMethod').reloadList({
                    billing_zone_id: combo.getStore().getAt(index).get('id')
                });
            }
        }
    });
    
    var tplShortAddress = '{firstname} {lastname} {street_address} {city} {postcode}' +
        ' <tpl for="country">{name}</tpl>' +
        ' <tpl for="zone">{name}</tpl>'
    ;

    var recordAddress = Ext.ux.data.CalcRecord.create([
        {name: 'id',               type: 'int'},
        {name: 'city'},
        {name: 'company'},
        {name: 'country'},
        {name: 'customer_id',      type: 'int'},
        {name: 'default_billing',  type: 'int'},
        {name: 'default_shipping', type: 'int'},
        {name: 'fax'},
        {name: 'firstname'},
        {name: 'gender'},
        {name: 'lastname'},
        {name: 'phone'},
        {name: 'postcode'},
        {name: 'street_address'},
        {name: 'suburb'},
        {name: 'zone'},
        {name: 'short_address',
//            dependencies: ['firstname', 'lastname'],
            notDirty: true,
            calc: function(record) {
                var tpl = new Ext.XTemplate(
                    '<tpl for=".">' + tplShortAddress + '</tpl>'
                );
                return tpl.apply(record.data);
            }
        }
    ]);

    var storeAddresses = new Ext.data.Store({
        storeId: 'storeAddresses',
//        autoLoad: true,
        reader: new Ext.data.JsonReader({
            idProperty: 'id',
            root: 'data',
            totalProperty: 'count'
        }, recordAddress),
        remoteSort: true,
        proxy: new Ext.data.HttpProxy({
            method: 'POST',
            url: Axis.getUrl('account/address/list')
        }),
        listeners: {
            load: function(store, records, options) {

                var emptyRecord = new store.recordType({
                    id:               -1,
                    country:          {},
                    firstname:        'New Address',
                    zone:             {}
                });
                emptyRecord.get = function(name) {
                    if (!this.data[name] || -1 == this.data.id) {
                        return '';
                    }
                    return this.data[name];
                };
                store.insert(0, emptyRecord);
            }
        }
    });

    var cmpBillingAddress = new Ext.form.ComboBox({
        fieldLabel: 'Address'.l(),
        submitValue: false,
        triggerAction: 'all',
        displayField: 'short_address',
        tpl: '<tpl for="."><div class="x-combo-list-item" >\n' +
                tplShortAddress +
            '</div></tpl>',
        valueField: 'id',
        typeAhead: true,
        hideLabel: false,
        listWidth: 450,
        store: storeAddresses,
        plugins: inlineField,
        lazyRender: true,
        listeners: {
            beforeselect: function(combo, record, index) {
                var form = Order.form.getForm();

                form.findField('order[billing_address_type]').setValue(
                    record.get('id') ? 1 : 0
                );

                form.findField('order[billing_firstname]').setValue(
                    record.get('firstname')
                );
                form.findField('order[billing_lastname]').setValue(
                    record.get('lastname')
                );
                form.findField('order[billing_company]').setValue(
                    record.get('company')
                );
                form.findField('order[billing_street_address]').setValue(
                    record.get('street_address')
                );
                form.findField('order[billing_suburb]').setValue(
                    record.get('suburb')
                );
                
                form.findField('order[billing_city]').setValue(
                    record.get('city')
                );
               
                cmpBillingCountry.setValue(record.get('country').id);
                cmpBillingCountry.fireEvent(
                    'select', cmpBillingCountry, record.get('country').id
                );

                form.findField('order[billing_postcode]').setValue(
                    record.get('postcode')
                );
                cmpBillingZone.setValue(record.get('zone').id);

                form.findField('order[billing_phone]').setValue(
                    record.get('phone')
                );
                    
                form.findField('order[billing_fax]').setValue(
                    record.get('fax')
                );

                Ext.StoreMgr.lookup('storeShippingMethod').reloadList();
            }
        },
        boxChanged: function() {
            cmpBillingAddress.setValue('');
            var form = Order.form.getForm();
            form.findField('order[billing_address_type]').setValue(0);
        }
    });

    Order.form.boxBilling = {
        title : 'Billing Address'.l(),
        id: 'box-billing-info',
        defaults: {
            hideLabel:  true,
            anchor: '-10'
        },
        items: [cmpBillingAddress, {
            name: 'order[billing_address_type]', // 0 == new address, 1 == old address
            initialValue: 0,
            xtype: 'hidden'}, {
            
            xtype: 'compositefield',
            defaults: {
//                allowBlank: false,
                plugins: inlineField,
                listeners: {
                    change: cmpBillingAddress.boxChanged
                }
            },
            items: [{
                    xtype: 'textfield',
                    emptyText: 'Firstname ',
                    name: 'order[billing_firstname]',
                    flex: 1
                }, {
                    xtype: 'textfield',
                    emptyText: 'Lastname ',
                    name: 'order[billing_lastname]',
                    flex: 1
                }
            ]
        }, {
            xtype: 'textfield',
            plugins: inlineField,
            name: 'order[billing_company]',
            emptyText: 'Company ',
            listeners: {
                change: cmpBillingAddress.boxChanged
            }
        }, {
            xtype: 'compositefield',
            defaults: {
                plugins: inlineField,
                listeners: {
                    change: cmpBillingAddress.boxChanged
                }
            },
            items: [
                {
                    xtype: 'textfield',
                    emptyText: 'Street ',
                    name: 'order[billing_street_address]',
//                    allowBlank: false,
                    flex: 1
                }, {
                    xtype: 'textfield',
                    emptyText: 'Suburb ',
                    name: 'order[billing_suburb]',
                    flex: 1
                }
            ]
        }, {
            xtype: 'compositefield',
            items: [{
                    xtype: 'textfield',
                    emptyText: 'City ',
                    name: 'order[billing_city]',
                    flex: 1,
//                    allowBlank: false,
                    plugins: inlineField,
                    listeners: {
                        change: cmpBillingAddress.boxChanged
                    }
                }, cmpBillingCountry

            ]
        }, {
            xtype: 'compositefield',
            items: [{
//                    allowBlank: false,
                    xtype: 'textfield',
                    emptyText:  'Zip ',
                    name: 'order[billing_postcode]',
                    flex: 1,
                    plugins: inlineField,
                    listeners: {
                        change: cmpBillingAddress.boxChanged
                    }
                }, cmpBillingZone
            ]
        }, {
            xtype: 'compositefield',
            defaults: {
//                allowBlank: false,
                plugins: inlineField,
                listeners: {
                    change: cmpBillingAddress.boxChanged
                }
            },
            items: [{
                    xtype: 'textfield',
                    emptyText: 'Phone ',
                    name: 'order[billing_phone]',
                    flex: 1
                }, {
                    xtype: 'textfield',
                    emptyText: 'Fax ',
                    name: 'order[billing_fax]',
                    flex: 1
                }
            ]
        }]
    };

}, this);