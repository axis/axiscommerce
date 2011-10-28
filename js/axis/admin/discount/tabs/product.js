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

      
var discountWindowFormProductTab = {
    el: null,
    onLoad: discountWindowFormSiteTab.onLoad 
}
    
Ext.onReady(function() {

    var fields = [
        {name: 'id', type: 'int'},
        {name: 'sku'},
        {name: 'quantity', type: 'float'},
        {name: 'price', type: 'float'},
        {name: 'is_active', type: 'int'}
    ];

    var record = Ext.data.Record.create(fields);

    var ds = new Ext.data.Store({
        baseParams: {
            limit: 25
        },
        url    :  Axis.getUrl('catalog/product/list'),
        reader : new Ext.data.JsonReader({
                root : 'data',
                totalProperty: 'count',
                id   : 'id'
            }, 
            record
        ),
        remoteSort: true,
        pruneModifiedRecords: true,
        sortInfo: {
            field: 'id',
            direction: 'ASC'
        }
    });
    
    var checkColumn = new Axis.grid.CheckColumn({
        dataIndex: 'check',
        header: 'Belongs to'.l(),
        width: 100,
        filterable : false
    });
    
    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: 'Id'.l(),
            dataIndex: 'id',
            width: 90
        }, {
            header: 'Sku'.l(),
            dataIndex: 'sku',
            id: 'sku',
            filter: {
                operator: 'LIKE'
            }
        }, checkColumn]
    });
    
    discountWindowFormProductTab.el = new Axis.grid.GridPanel({
        title: 'Products'.l(),
        autoExpandColumn: 'sku',
        cm: cm,
        id: 'grid',
        store: ds,
        plugins: [new Axis.grid.Filter(), checkColumn],
        bbar: new Axis.PagingToolbar({
            pageSize: 25,
            store: ds
        }),
        massAction: false
    });
});