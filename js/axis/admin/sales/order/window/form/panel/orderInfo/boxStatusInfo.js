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
    var status = [
        {name: 'id',     type: 'int'},
        {name: 'name',   type: 'string'},
        {name: 'system', type: 'int'}
    ];
    for (var languageId in Axis.languages) {
         status.push({'name': 'status_name_' + languageId, type: 'string'});
    }

    var storeOrderStatusses = new Ext.data.Store({
        storeId: 'storeOrderStatusses',
//        autoLoad: true,
        url:  Axis.getUrl('sales_order-status/get-childs'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, status)
    });
    
    Order.form.boxStatusInfo = {
        title : 'Status Update'.l(),
        id: 'box-status-info',
//        xtype: 'panel',
//        layout: 'form',
//        labelAlign: 'top',
//        border: false,
        items: [new Ext.form.ComboBox({
            hideLabel: true,
            id : 'next-order-status-id',
            hiddenName: 'history[order_status_id]',
            name: 'history[order_status_id]',
            fieldLabel: 'Order Status'.l(),
            triggerAction: 'all',
            store: storeOrderStatusses,
            displayField: 'status_name_' + Axis.language,
            valueField: 'id',
            mode: 'local',
            lazyRender: true,
            anchor:'98%'
        }), {
            xtype: 'textarea',
            fieldLabel: 'Status Comment'.l(),
            name: 'history[comments]',
            anchor: '98%'
        }, {
            xtype:'checkbox',
            fieldLabel: 'Notify Customer'.l(),
            name: 'history[notified]',
            anchor:'98%'
        }]
    };
    
}, this);