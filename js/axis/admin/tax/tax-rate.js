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
            {name: 'id'},
            {name: 'tax_class_id'},
            {name: 'geozone_id'},
            {name: 'customer_group_id'},
            {name: 'rate'},
            {name: 'description'},
            {name: 'created_on'},
            {name: 'modified_on'}
        ]),
        create: function () {
            grid.stopEditing();
            var record = new Item.record({
                tax_class_id: '',
                geozone_id: '',
                customer_group_id: '',
                rate: '0',
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
            var modified = ds.getModifiedRecords();
            
            if (!modified.length)
                return;
            
            var data = {};
            for (var i = 0; i < modified.length; i++) {
                data[modified[i]['id']] = modified[i]['data'];
            }
            
            Ext.Ajax.request({
                url: Axis.getUrl('tax_rate/save'),
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
            
            if (!confirm('Are you sure?'.l()))
                return;
                
            var data = {};
            
            for (var i = 0; i < selectedItems.length; i++) {
                if (!selectedItems[i]['data']['id']) continue;
                data[i] = selectedItems[i]['data']['id'];
            }
                
            Ext.Ajax.request({
                url: Axis.getUrl('tax_rate/delete'),
                params: {data: Ext.encode(data)},
                callback: function() {
                    ds.reload();
                }
            });
        }
    };
    
    Ext.QuickTips.init();
    
    dsGeozone = new Ext.data.JsonStore({
        id: 'id',
        fields: ['id', 'name'],
        data: zones
    });
   
    dsCustomerGroups = new Ext.data.JsonStore({
        id: 'id',
        fields: ['id', 'name'],
        data: customer_groups
    });
    
    dsTaxClass = new Ext.data.JsonStore({
        id: 'id',
        fields: ['id', 'name'],
        data: tax_classes
    });
    
    var ds = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('tax_rate/list')
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
        header: "Customer Group".l(),
        dataIndex: 'customer_group_id',
        width: 80,
        sortable: true,
        mode: 'local',
        editor: new Ext.form.ComboBox({
           typeAhead: true,
           triggerAction: 'all',
           lazyRender: true,
           store: new Ext.data.JsonStore({
               id: 'id',
               fields: ['id', 'name'],
               data: customer_groups
           }),
           displayField: 'name',
           valueField: 'id',
           mode: 'local'
        }),
        renderer: function(value) {
            if (value == '') {
                return "None".l();
            } else {
                return dsCustomerGroups.getById(value).get('name');
            }
        }
    },{
        header: "Rate(%)".l(),
        dataIndex: 'rate',
        width: 100,
        editor: new Ext.form.TextField({
           allowBlank: false
        })
    },{
        header: "Tax Class".l(),
        dataIndex: 'tax_class_id',
        width: 200,
        sortable: true,
        mode: 'local',
        editor: new Ext.form.ComboBox({
           typeAhead: true,
           triggerAction: 'all',
           lazyRender: true,
           store: new Ext.data.JsonStore({
               id: 'id',
               fields: ['id', 'name'],
               data: tax_classes
           }),
           displayField: 'name',
           valueField: 'id',
           mode: 'local'
        }),
        renderer: function(value) {
            if (value == '0' || value == '') {
                return "None".l();
            } else {
                return dsTaxClass.getById(value).get('name');
            }
        }
    },{
        header: "Zone".l(),
        dataIndex: 'geozone_id',
        width: 200,
        sortable: true,
        mode: 'local',
        editor: new Ext.form.ComboBox({
           typeAhead: true,
           triggerAction: 'all',
           lazyRender: true,
           store: new Ext.data.JsonStore({
               id: 'id',
               fields: ['id', 'name'],
               data: zones
           }),
           displayField: 'name',
           valueField: 'id',
           mode: 'local'
        }),
        renderer: function(value) {
            if (value == '0' || value == '') {
                return "None".l();
            } else {
                return dsGeozone.getById(value).get('name');
            }
        }
    },{
        header: "Created".l(),
        dataIndex: 'created_on',
        width: 130
    },{
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
        },{
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            cls: 'x-btn-text-icon',
            handler : Item.save
        },{
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
    })
    
    ds.load({params:{start:0, limit:25}});
}, this);