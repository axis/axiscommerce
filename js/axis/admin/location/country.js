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
    
    Ext.QuickTips.init();
    
    var Country = {
        
        activeId: 0,
        
        record: Ext.data.Record.create([
            {name: 'id',                type: 'int'},
            {name: 'name',              type: 'string'},
            {name: 'iso_code_2',        type: 'string'},
            {name: 'iso_code_3',        type: 'string'},
            {name: 'address_format_id', type: 'int'}
        ]),
        
        create: function (){
            grid.stopEditing();
            var record = new Country.record({
                name: '',
                iso_code_2: '',
                iso_code_3: '',
                address_format_id: ''
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
                url: Axis.getUrl('location_country/save'),
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
            
            if (!selectedItems.length || !confirm('Are you sure?'.l())) {
                return;
            }
                
            var data = {};
            
            for (var i = 0; i < selectedItems.length; i++) {
                if (!selectedItems[i]['data']['id']) continue;
                data[i] = selectedItems[i]['data']['id'];
            }
                
            Ext.Ajax.request({
                url: Axis.getUrl('location_country/delete'),
                params: {data: Ext.encode(data)},
                callback: function() {
                    ds.reload();
                }
            });
        }
    };
    
    var dsAFormat = new Ext.data.Store({
        autoLoad: true,
        proxy: new Ext.data.HttpProxy({
            method: 'get',
            url: Axis.getUrl('location_country/get-address-format')
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, [
            {name: 'id', type: 'int'}, 
            {name: 'name'}
        ])
    });
    
    var ds = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('location_country/list')
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Country.record),
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
        },{
            header: "ISO 2".l(),
            dataIndex: 'iso_code_2',
            width: 70,
            sortable: true,
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        },{
            header: "ISO 3".l(),
            dataIndex: 'iso_code_3',
            width: 70,
            sortable: true,
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }, {
            header: "Address Format".l(),
            dataIndex: 'address_format_id',
            width: 100,
            sortable: true,
            editor: new Ext.form.ComboBox({
               typeAhead: true,
               triggerAction: 'all',
               lazyRender: true,
               store: dsAFormat,
               displayField: 'name',
               valueField: 'id',
               mode: 'local'
            }),
            renderer: function(value) {
                if (value == '0' || value == '') {
                    return "None";
                } else {
                    if ((record = dsAFormat.getById(value))) {
                        return record.get('name')
                    }
                    return value;
                }
            }
        }
    ]);
    
    var grid = new Axis.grid.EditorGridPanel({
        id: 'grid-country',
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
            handler : Country.create
        },{
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            cls: 'x-btn-text-icon',
            handler : Country.save
        },{
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler : Country.remove
        }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-text-icon',
            handler : function() {
                grid.getStore().reload();
            }
        }]
    });
    
    dsAFormat.on('load', function(){
        ds.load({params:{start:0, limit:25}});
    });
    
    new Axis.Panel({
        items: [
            grid
        ]
    });
    
}, this);
