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


Ext.onReady(function(){
    var Item = {
        
        activeId: 0,
        
        record: Ext.data.Record.create([
            {name: 'id'},
            {name: 'module_name'},
            {name: 'controller_name'},
            {name: 'action_name'}
        ]),
        
        create: function (){
            grid.stopEditing();
            var record = new Item.record({
                module_name: '',
                controller_name: '*',
                action_name: '*'
            });
            ds.insert(0, record);
            grid.getStore().getAt(0).set('module_name', '*');
            grid.startEditing(0, 1);
        },
        
        save: function() {
            var modified = ds.getModifiedRecords();
            
            if (!modified.length)
                return;
                
            var data = {};
            
            for (var i = 0; i < modified.length; i++) {
                data[modified[i]['id']] = modified[i]['data'];
            }
            
            Ext.Ajax.request({
                url: Axis.getUrl('core/page/batch-save'),
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
                url: Axis.getUrl('core/page/remove'),
                params: {data: Ext.encode(data)},
                callback: function() {
                    ds.reload();
                }
            });
        }
    };
    
    Ext.QuickTips.init();
    
    var ds = new Ext.data.GroupingStore({
        url: Axis.getUrl('core/page/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Item.record),
        pruneModifiedRecords: true,
        sortInfo: {field: 'controller_name', direction: "ASC"},
        groupField: 'module_name'
    });
    
    var cm = new Ext.grid.ColumnModel([
        {
            header: "Module".l(),
            dataIndex: 'module_name',
            sortable: true,
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        },{
            header: "Controller".l(),
            dataIndex: 'controller_name',
            sortable: true,
            groupable:false,
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        },{
            header: "Action".l(),
            dataIndex: 'action_name',
            sortable: true,
            groupable:false,
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }
    ]);
    
    var grid = new Axis.grid.EditorGridPanel({
        ds: ds,
        cm: cm,
        view: new Ext.grid.GroupingView({
            forceFit:true,
            emptyText: 'No records found'.l(),
            groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
        }),
        plugins: [
            new Ext.ux.grid.Search({
                mode: 'local',
                align: 'left',
                iconCls: false,
                dateFormat: 'Y-m-d',
                width: 200,
                minLength: 2
            })
        ],
        bbar: [],
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
            handler: Item.create
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            cls: 'x-btn-text-icon',
            handler: Item.save
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler: Item.remove
        }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-text-icon',
            handler: function() {
                grid.getStore().reload();
            }
        }]
    });
    
    new Axis.Panel({
        items: [
            grid
        ]
    })
    
    ds.load();
    
}, this);