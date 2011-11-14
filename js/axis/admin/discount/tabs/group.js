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

var groupTab = {
    el: null,
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
        this.el.store.each(function(record){
            record.set('check', 0);
            record.commit();
        });
    },
    setData: function(data) {
        if ('undefined' == typeof data) {
            this.clear();
            return;
        }
        var store = this.el.store;
        
        Ext.each(data, function(id) {
            var record = store.getById(id);
            if ('undefined' !== typeof record) {
                record.set('check', 1);
                record.commit();
            }
        });
    }
}

Ext.onReady(function() {

    var fields = [
        {name: 'id',          type: 'int',    mapping: 'id'},
        {name: 'name',        type: 'string', mapping: 'name'},
        {name: 'description', type: 'string', mapping: 'description'}
    ];

    var record = Ext.data.Record.create(fields);

    var ds = new Ext.data.Store({
        url: Axis.getUrl('account/group/list'),
        baseParams: {
            limit: 25
        },
        reader : new Ext.data.JsonReader({
                root : 'data',
                id   : 'id'
            }, 
            record
        ),
        remoteSort: true,
        mode: 'local',
        autoLoad: true,
        pruneModifiedRecords: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        }
    });
    
    var checkColumn = new Axis.grid.CheckColumn({
        dataIndex: 'check',
        width: 100,
        filterable : false
    });
    
    var cm = new Ext.grid.ColumnModel({
        columns: [{
            header: 'Name'.l(),
            dataIndex: 'name',
            id: 'name',
            filterable : false
        }, checkColumn]
    });
    
    groupTab.el = new Axis.grid.GridPanel({
        title: 'Group'.l(),
        autoExpandColumn: 'name',
        cm: cm,
        store: ds,
        plugins: [checkColumn],
        massAction: false,
        deferRowRender: false
    });
}); 