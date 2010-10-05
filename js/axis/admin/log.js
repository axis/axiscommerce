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

Ext.onReady(function (){
    var storePageviews = new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('log/list')
        }),
        reader: new Ext.data.JsonReader({
                root : 'data',
                totalProperty: 'count',
                id: 'id'
            },
            ['id', 'visitor_id', 'hit', 'url', 'pdate', 'refer', 'customer_email', 'customer_id']
        ),
        sortInfo: {field: 'hit', direction: "DESC"},
        remoteSort: true
    });
    
    var columnsPageviews = new Ext.grid.ColumnModel([{
        header: "Date".l(), 
        width: 100, 
        sortable: true,
        dataIndex: 'pdate'
    },{
        header: "url".l(), 
        width: 470, 
        sortable: true,
        dataIndex: 'url'
    }, {
        header: "Hit".l(), 
        width: 80, 
        dataIndex: 'hit',
        sortable: true 
    }]); 
       
    var gridPageviews = new Axis.grid.GridPanel({
        store: storePageviews,
        cm: columnsPageviews,
        viewConfig: {
            forceFit: true,
            emptyText: 'No records found'.l()
        },
        stripeRows: true
    });
    
    new Axis.Panel({
        items: [
            gridPageviews
        ]
    });

    storePageviews.load();
});