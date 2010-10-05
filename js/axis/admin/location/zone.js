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

Ext.onReady(function() {
    var Item = {
        
        activeId: 0,
        
        record: Ext.data.Record.create([
            {name: 'id',         type: 'int'},
            {name: 'code',       type: 'string'},
            {name: 'name',       type: 'string'},
            {name: 'country_id', type: 'int'}
        ]),
        
        create: function () {
            grid.stopEditing();
            var record = new Item.record({
                name: '',
                code: '',
                country_id: ''
            });
            ds.insert(0, record);
            grid.startEditing(0, 0);
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
                url: Axis.getUrl('location_zone/save'),
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
            
            if (!confirm('Are you sure?'))
                return;
                
            var data = {};
            
            for (var i = 0; i < selectedItems.length; i++) {
                if (!selectedItems[i]['data']['id']) continue;
                data[i] = selectedItems[i]['data']['id'];
            }
            
            Ext.Ajax.request({
                url: Axis.getUrl('location_zone/delete'),
                params: {data: Ext.encode(data)},
                callback: function() {
                    ds.reload();
                }
            })
        }
    }
    
    var dsCountry = new Ext.data.JsonStore({
        id: 'id',
        fields: ['id', 'name'],
        data: countries
    });
    
    var ds = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('location_zone/list')
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
            width: 350,
            sortable: true,
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }, {
            header: "Code".l(),
            dataIndex: 'code',
            width: 80,
            sortable: true,
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }, {
            header: "Country".l(),
            dataIndex: 'country_id',
            width: 300,
            sortable: true,
            mode: 'local',
            editor: new Ext.form.ComboBox({
               typeAhead: true,
               triggerAction: 'all',
               lazyRender: true,
               store: dsCountry,
               displayField: 'name',
               valueField: 'id',
               mode: 'local'
            }),
            renderer: function(value) {
                if (value == '') {
                    return "None".l();
                } else {
                    if ((record = dsCountry.getById(value))) {
                        return record.get('name');
                    }
                    return value;
                }
            }
        }
    ]);
    
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
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-text-icon',
            handler : function() {
                grid.getStore().reload();
            }
        }]
    });
    
    ds.load({params:{start:0, limit:25}});
    
    new Axis.Panel({
        items: [
            grid
        ]
    });
}, this);