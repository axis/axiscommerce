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
    ///// general box start
    ////////////////////////////////////////////////////////////////////////////
    var storeStatusSites = new Ext.data.ArrayStore({
        fields: ['id', 'name'],
        data : statusSites
    });

    var storeCurrency = new Ext.data.Store({
        storeId: 'storeCurrency',
        url:  Axis.getUrl('locale_currency/list'),
        totalProperty: 'count',
//        autoLoad: true,
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, [
            {name: 'id',                 type: 'int'},
            {name: 'code',               type: 'string'},
            {name: 'currency_precision', type: 'int'},
            {name: 'display',            type: 'int'},
            {name: 'format',             type: 'string'},
            {name: 'position',           type: 'int'},
            {name: 'rate',               type: 'float'},
            {name: 'title',              type: 'string'}
        ]),
        pruneModifiedRecords: true
    });

    Order.form.boxGeneral = {
        title : 'Order Info'.l(),
        id: 'box-general-info',
        defaults: {
            plugins: inlineField,
            anchor: '-10'
        },
        items: [{
                fieldLabel: 'Number'.l(),
                xtype: 'textfield',
//                readOnly: true,
                submitValue: false,
                name: 'order[number]'

            }, {
                fieldLabel: 'Store'.l(),
                xtype: 'combo',
                name: 'order[site_id]',
                hiddenName: 'order[site_id]',
                allowBlank: false,
                triggerAction: 'all',
                displayField: 'name',
                valueField: 'id',
                typeAhead: true,
                mode: 'local',
                store: storeStatusSites
            }, {
                fieldLabel: 'Placed on'.l(),
                xtype: 'datefield',
                name: 'order[date_purchased_on]',
                allowBlank: false,
                valueFormat:  'Y-m-d H:i:s'
            }, {
                fieldLabel: 'Order Status'.l(),
                xtype: 'textfield',
                readOnly: true,
                submitValue: false,
                name: 'order[status_name]'
            }, {
                name: 'order[order_status_id]', 
                initialValue: 0,
                xtype: 'hidden'
            }, {
                fieldLabel: 'Currency'.l(),
                xtype: 'combo',
                name: 'order[currency]',
                hiddenName: 'order[currency]',
                allowBlank: false,
                triggerAction: 'all',
                displayField: 'code',
                valueField: 'code',
                typeAhead: true,
//                mode: 'local',
                store: storeCurrency
            }, {
                name: 'order[ip_address]',
                initialValue: '127.0.0.1',
                xtype: 'hidden'
            }
        ]
    };
    
}, this);