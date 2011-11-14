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

      
var productTab = {
    el: null,
    checked:[],
    getSelected: function(){
        var store    = this.el.store,
            modified = store.getModifiedRecords(),
            data     = this.checked;
        
        Ext.each(modified, function (r) {
            var value = r.get('id') + '';
            var indexOf = data.indexOf(value);
            
            if (-1 == indexOf && 1 == r.get('check')) {
                data.remove(value);
                data.push(value);
            }
            if (-1 != indexOf && 0 == r.get('check')) {
                data.remove(value);
            }
        });
        return data;
    },
    clear: function(){
        this.setData([]);
    },
    setData: function(data) {
        Ext.getCmp('product-filter').setValue(1);
        if ('undefined' == typeof data) {
            data = [];
        }
        this.checked = data;
        
        this.delayedLoader.state = '';
        if (this.el.findParentByType('tabpanel').getActiveTab() == this.el) {
            this.delayedLoader.load();
        }
    }
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
    
    ds.on('load', function(s, records) {
        Ext.each(records, function(record){
            Ext.each(productTab.checked, function(id) {
                if (record.get('id') == id) {
                    record.set('check', 1);
                }
            });
        });
    });
    
    var checkColumn = new Axis.grid.CheckColumn({
        dataIndex: 'check',
        width: 100,
        filter: {
            editable: false,
            resetValue: 'reset',
            id: 'product-filter',
            name: 'id',
            operator: 'IN',
            store: new Ext.data.ArrayStore({
                data: [[0, 'No'.l()], [1, 'Yes'.l()]],
                fields: ['id', 'name']
            }),
            getValue: function() {
                var value = this.__proto__.getValue.call(this);
                
                if (0 === value.length || this.resetValue === value) {
                    return this.resetValue;
                }
                
                this.operator = 'IN';
                if (0 == value) {
                    this.operator = 'NOT IN';
                }
                if (0 == productTab.checked.length) {
                    return [-1];
                }
                return productTab.checked;
            }
        }
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
            header: 'SKU'.l(),
            dataIndex: 'sku',
            id: 'sku',
            filter: {
                operator: 'LIKE'
            }
        }, checkColumn]
    });
    
    productTab.el = new Axis.grid.GridPanel({
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
        massAction: false,
        deferRowRender: false
    });
    
    productTab.delayedLoader = new Axis.DelayedLoader({
        el: productTab.el,
        ds: ds
    });
});