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

Ext.onReady(function(){

    ////////////////////////////////////////////////////////////////////////////
    ///// delivery box start
    ////////////////////////////////////////////////////////////////////////////

    var storeCountry = new Ext.data.Store({
        storeId: 'storeCountry',
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

    var cmpDeliveryCountry = new Ext.form.ComboBox({
        emptyText: 'Country ',
        name: 'order[delivery_country]',
        hiddenName: 'order[delivery_country]',
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
                var zonesCombo = Ext.getCmp('combo-zone-delivery');
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
                cmpDeliveryAddress.boxChanged();
                Ext.StoreMgr.lookup('storeShippingMethod').reloadList({
                    delivery_country_id: combo.getStore().getAt(index).get('id')
                });
            }
        }
    });
//    cmpDeliveryCountry.setValue = cmpDeliveryCountry.setValue.createInterceptor(
//        function(value) {
//            if ('undefined' === typeof value) {
//                return;
//            }
//            Ext.StoreMgr.lookup('storeShippingMethod').reloadList({
//                delivery_country_id: value
//            });
//    });

    var cmpDeliveryZone = new Ext.form.ComboBox({
        xtype: 'combo',
        id: 'combo-zone-delivery',
        emptyText: 'Zone'.l() + ' ',
        name: 'order[delivery_state]',
        hiddenName: 'order[delivery_state]',
        triggerAction: 'all',
        displayField: 'name',
        listWidth: 200,
        valueField: 'id',
        typeAhead: true,
        mode: 'local',
        store: storeZones,
        lastQuery: '',
        flex: 1,
        plugins: inlineField,
        lazyRender: true,
        listeners: {
            beforeselect: function(combo, record, index) {
                cmpDeliveryAddress.boxChanged();
                Ext.StoreMgr.lookup('storeShippingMethod').reloadList({
                    delivery_zone_id: combo.getStore().getAt(index).get('id')
                });
            }
        }
    });

    var cmpDeliveryPostcode = new Ext.form.TextField({
//        allowBlank: false,
        emptyText:  'Zip'.l() + ' ',
        name: 'order[delivery_postcode]',
        flex: 1,
        plugins: inlineField,
        listeners: {
            change: function(element, newValue, OldValue) {
                cmpDeliveryAddress.boxChanged();
                Ext.StoreMgr.lookup('storeShippingMethod').reloadList({
                    postcode: newValue
                });
            }
        }
    });

    var tplShortAddress = '{firstname} {lastname} {street_address} {city} {postcode}' +
        ' <tpl for="country">{name}</tpl>' +
        ' <tpl for="zone">{name}</tpl>'
    ;

    var cmpDeliveryAddress = new Ext.form.ComboBox({
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
        store: Ext.StoreMgr.lookup('storeAddresses'),
        plugins: inlineField,
        lazyRender: true,
        listeners: {
            beforeselect: function(combo, record, index) {
                var form = Order.form.getForm();

                form.findField('order[delivery_address_type]').setValue(
                    record.get('id') ? 1 : 0
                );

                form.findField('order[delivery_firstname]').setValue(
                    record.get('firstname')
                );
                form.findField('order[delivery_lastname]').setValue(
                    record.get('lastname')
                );
                form.findField('order[delivery_company]').setValue(
                    record.get('company')
                );
                form.findField('order[delivery_street_address]').setValue(
                    record.get('street_address')
                );
                form.findField('order[delivery_suburb]').setValue(
                    record.get('suburb')
                );

                form.findField('order[delivery_city]').setValue(
                    record.get('city')
                );

                cmpDeliveryCountry.setValue(record.get('country').id);
                cmpDeliveryCountry.fireEvent(
                    'select', cmpDeliveryCountry, record.get('country').id
                );

                cmpDeliveryPostcode.setValue(record.get('postcode'));
                cmpDeliveryZone.setValue(record.get('zone').id);

                form.findField('order[delivery_phone]').setValue(
                    record.get('phone')
                );

                form.findField('order[delivery_fax]').setValue(
                    record.get('fax')
                );
                    
                Ext.StoreMgr.lookup('storeShippingMethod').reloadList();
            }
        },
        boxChanged: function() {
            cmpDeliveryAddress.setValue('');
            var form = Order.form.getForm();
            form.findField('order[delivery_address_type]').setValue(0);
        }
    });

    Order.form.boxDelivery = {
        title : 'Delivery Address'.l(),
        id: 'box-delivery-info',
        defaults: {
            hideLabel:  true,
            anchor: '-10'
        },
        items: [cmpDeliveryAddress, {
            name: 'order[delivery_address_type]', // 0 == new address, 1 == old address
            initialValue: 0,
            xtype: 'hidden'}, {
            xtype: 'compositefield',
            defaults: {
//                allowBlank: false,
                plugins: inlineField,
                listeners: {
                    change: cmpDeliveryAddress.boxChanged
                }
            },
            items: [{
                    xtype: 'textfield',
                    emptyText: 'Firstname ',
                    name: 'order[delivery_firstname]',
                    flex: 1
                }, {
                    xtype: 'textfield',
                    emptyText: 'Lastname ',
                    name: 'order[delivery_lastname]',
                    flex: 1
                }
            ]
        },
        {
            xtype: 'textfield',
            plugins: inlineField,
            name: 'order[delivery_company]',
            emptyText: 'Company ',
            listeners: {
                change: cmpDeliveryAddress.boxChanged
            }
        },
        {
            xtype: 'compositefield',
            defaults: {
                plugins: inlineField,
                listeners: {
                    change: cmpDeliveryAddress.boxChanged
                }
            },
            items: [
                {
                    xtype: 'textfield',
                    emptyText: 'Street ',
                    name: 'order[delivery_street_address]',
//                    allowBlank: false,
                    flex: 1
                }, {
                    xtype: 'textfield',
                    emptyText: 'Suburb ',
                    name: 'order[delivery_suburb]',
                    flex: 1
                }
            ]
        }, {
            xtype: 'compositefield',
            items: [{
                    xtype: 'textfield',
                    emptyText:  'City ',
                    name: 'order[delivery_city]',
                    flex: 1,
//                    allowBlank: false,
                    plugins: inlineField,
                    listeners: {
                        change: cmpDeliveryAddress.boxChanged
                    }
                }, cmpDeliveryCountry
            ]
        }, {
            xtype: 'compositefield',
            items: [cmpDeliveryPostcode, cmpDeliveryZone]
        }, {
            xtype: 'compositefield',
            defaults: {
//                allowBlank: false,
                plugins: inlineField,
                listeners: {
                    change: cmpDeliveryAddress.boxChanged
                }
            },
            items: [{
                    xtype: 'textfield',
                    emptyText: 'Phone ',
                    name: 'order[delivery_phone]',
                    flex: 1
                }, {
                    xtype: 'textfield',
                    emptyText: 'Fax ',
                    name: 'order[delivery_fax]',
                    flex: 1
                }
            ]
        }]
    };
    
}, this);