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
Ext.onReady(function () {
    
    var column = Ext.data.Record.create([
        {name: 'id',         type: 'int'},
        {name: 'site_id',    type: 'int'},
        {name: 'country_id', type: 'int'},
        {name: 'zone_id',    type: 'int'},
        {name: 'zip'},
        {name: 'value',      type: 'int'},
        {name: 'price',      type: 'int'}
    ]);
    new Ext.data.Store({
        storeId : 'shippingTable/rate',
//        autoLoad: true, 
        baseParams: {
            limit: 25
        },
        reader: new Ext.data.JsonReader({
                idProperty: 'id',
                root : 'data',
                totalProperty: 'count'
            }, column
        ),
        remoteSort: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        },
        proxy: new Ext.data.HttpProxy({
            method: 'post',
            url: Axis.getUrl('shipping-table/rate/list')
        })
    });
    
    // @todo move to core/model/site.js    
    var column = Ext.data.Record.create([
       {name: 'id', type: 'int'},
       {name: 'base'},
       {name: 'name'},
       {name: 'secure'}
//       ,{name: 'root_category'}
    ]);
    
    new Ext.data.Store({
        storeId: 'core/site',
//        autoLoad: true,
        proxy: new Ext.data.HttpProxy({
            method: 'post',
            url: Axis.getUrl('core/site/list')
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, column),
        pruneModifiedRecords: true
    })
    
    // @todo move to location/model/country.js
    var column = Ext.data.Record.create([
        {name: 'id',                type: 'int'},
        {name: 'name',              type: 'string'},
        {name: 'iso_code_2',        type: 'string'},
        {name: 'iso_code_3',        type: 'string'},
        {name: 'address_format_id', type: 'int'}
    ]);
    new Ext.data.Store({
        storeId: 'location/country',
//        autoLoad: true,
        baseParams: {
            start: 0, 
            limit: 250
        },
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            idProperty: 'id'
        }, column),
        remoteSort: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        },
        pruneModifiedRecords: true,
        proxy: new Ext.data.HttpProxy({
            method: 'post',
            url: Axis.getUrl('location/country/list')
        })
    });
    
    // @todo move to location/model/zone.js
    var column = Ext.data.Record.create([
        {name: 'id',         type: 'int'},
        {name: 'code',       type: 'string'},
        {name: 'name',       type: 'string'},
        {name: 'country_id', type: 'int'}
    ]);
    new Ext.data.Store({
        storeId: 'location/zone',
//        autoLoad: true,
        baseParams: {
            start: 0, 
            limit: 250
        },
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            idProperty: 'id'
        }, column),
        remoteSort: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        },
        pruneModifiedRecords: true,
        proxy: new Ext.data.HttpProxy({
            method: 'post',
            url: Axis.getUrl('location/zone/list')
        })
    });
});