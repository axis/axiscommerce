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
    var Item = {
        
        activeId: 0,
        
        record: Ext.data.Record.create([
            {name: 'id'},
            {name: 'name'},
            {name: 'description'},
            {name: 'created_on'},
            {name: 'modified_on'}
        ]),
        
        create: function (){
            grid.stopEditing();
            var record = new Item.record({
                name: '',
                description: '',
                type: 'new'
            });
            ds.insert(0, record);
            grid.startEditing(0, 1);
        },
        
        getSelectedId: function() {
            var selModel = grid.getSelectionModel();
            var selectedItems = grid.getSelectionModel().selections.items;
            if (!selectedItems.length) {
                return false;
            }
            if (selectedItems[0]['data']['id'])
                return selectedItems[0].id;
            return false;
        },
        
        save: function() {
            var data = {};
            var modified = ds.getModifiedRecords();
            if (!modified.length)
                return;
            
            for (var i = 0; i < modified.length; i++) {
                data[modified[i]['id']] = modified[i]['data'];
            }
            
            Ext.Ajax.request({
                url: Axis.getUrl('tax_class/save'),
                params: {
                    data: Ext.encode(data)
                },
                callback: function() {
                    ds.commitChanges();
                    ds.reload();
                }
            });
        },
        
        remove: function() {
            var selectedItems = grid.getSelectionModel().selections.items;
            
            if (!selectedItems.length)
                return;
            
            if (!confirm('Delete items?'))
                return;
                
            var data = {};
            
            for (var i = 0; i < selectedItems.length; i++) {
                if (!selectedItems[i]['data']['id']) continue;
                data[i] = selectedItems[i]['data']['id'];
            }
                
            Ext.Ajax.request({
                url: Axis.getUrl('tax_class/delete'),
                params: {data: Ext.encode(data)},
                callback: function() {
                    ds.reload();
                }
            });
        }
    };
    
    Ext.QuickTips.init();
    
    var ds = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('tax_class/list')
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Item.record),
        remoteSort: true,
        pruneModifiedRecords: true
    });
    
    var cm = new Ext.grid.ColumnModel([{
        header: "Name".l(),
        dataIndex: 'name',
        width: 150,
        sortable: true,
        editor: new Ext.form.TextField({
            allowBlank: false
        })
    }, {
        header: "Description".l(),
        dataIndex: 'description',
        width: 500,
        editor: new Ext.form.TextField({
            allowBlank: false
        })
    }, {
        header: "Created".l(),
        dataIndex: 'created_on',
        width: 130
    }, {
        header: "Modified".l(),
        dataIndex: 'modified_on',
        width: 130
    }]);
    
    var grid = new Axis.grid.EditorGridPanel({
        ds: ds,
        cm: cm,
        viewConfig: {
            forceFit: true,
            emptyText: 'No records found'.l()
        },
        bbar: new Axis.PagingToolbar({
            store: ds
        }),
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
            handler : Item.create
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            cls: 'x-btn-text-icon',
            handler : Item.save
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler : Item.remove
        }, '->', {
            text: 'Reload'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: function() {
                grid.getStore().reload();
            }
        }]
    });
    
    new Axis.Panel({
        items: [
            grid
        ]
    });
    
    ds.load({params:{start:0, limit:25}});
}, this);