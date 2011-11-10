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

var manufacturerTab = {
    el: null,
    checked:[],
    getSelected: function(){
        var data = [];
        this.el.store.each(function(r){
            if (1 == r.get('check')) {
                data.push(r.get('id'));
            }
        });
        return data;
    },
    clear: function(){
        this.checked = [];
        this.el.store.load();
    },
    setData: function(data) {
        if ('undefined' == typeof data) {
            this.clear();
            return;
        }
        
        this.checked = data;
        
        var params = {
            'filter[id][field]' : 'id'
        };
        Ext.each(data, function(value, index) {
            params['filter[id][value][' + index + ']']  = value;
        });
        
        this.el.store.load({
            params : params,
            callback: function() {
                delete this.lastOptions.params; //it is amazing fucking shit 
            }
        });
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
        header: 'Checked'.l(),
        width: 100,
        filterable : false
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
        title: 'Manufacrurer'.l(),
        autoExpandColumn: 'name',
        cm: cm,
        store: ds,
        plugins: [new Axis.grid.Filter(), checkColumn],
        bbar: new Axis.PagingToolbar({
            pageSize: 25,
            store: ds
        }),
        massAction: false
    });
});