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

var manufacturerTab = {
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
        Ext.getCmp('manufacture-filter').setValue(1);
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
        {name: 'id',       type: 'int'},
        {name: 'name',     type: 'string'},
        {name: 'key_word', type: 'string', mapping: 'url'},
        {name: 'image',    type: 'string'}
    ];

    for (var id in Axis.locales) {
        fields.push({
            name: 'description[' + id + '][title]',
            mapping: 'description.lang_' + id + '.title'
        }, {
            name: 'description[' + id + '][description]',
            mapping: 'description.lang_' + id + '.description'
        });
    }

    var record = Ext.data.Record.create(fields);

    var ds = new Ext.data.Store({
        baseParams: {
            limit: 25
        },
        url: Axis.getUrl('catalog/manufacturer/list'),
        reader: new Ext.data.JsonReader({
            root : 'data',
            totalProperty: 'count',
            id: 'id'
        }, record),
        remoteSort: true,
        pruneModifiedRecords: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        }
    });
    
    ds.on('load', function(s, records) {
        Ext.each(records, function(record){
            Ext.each(manufacturerTab.checked, function(id) {
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
            id: 'manufacture-filter',
            name: 'id',
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
                if (0 == manufacturerTab.checked.length) {
                    return [-1];
                }
                return manufacturerTab.checked;
            }
        }
    });
    
    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: 'Name'.l(),
            dataIndex: 'name',
            id: 'name',
            filter: {
                operator: 'LIKE'
            }
        }, checkColumn]
    });
    
    manufacturerTab.el = new Axis.grid.GridPanel({
        title: 'Manufacturer'.l(),
        autoExpandColumn: 'name',
        cm: cm,
        store: ds,
        plugins: [new Axis.grid.Filter(), checkColumn],
        bbar: new Axis.PagingToolbar({
            pageSize: 25,
            store: ds
        }),
        massAction: false,
        deferRowRender: false
    });
    
    manufacturerTab.delayedLoader = new Axis.DelayedLoader({
        el: manufacturerTab.el,
        ds: ds
    });
});