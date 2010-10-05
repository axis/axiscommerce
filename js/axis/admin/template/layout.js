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

Ext.namespace('Axis', 'Axis.Template', 'Axis.Template.Layout');
Ext.onReady(function() {
    var Layout = {
        templateId: 1,
        
        record: Ext.data.Record.create([
            {name: 'id'},
            {name: 'page_id'},
            {name: 'layout'},
            {name: 'priority'}
        ]),
        
        loadGrid: function(templateId) {
            Layout.templateId = templateId;
            ds.commitChanges();
            ds.load({params: {tId: Layout.templateId}});
        },
        
        create: function() {
            grid.stopEditing();
            var record = new Layout.record({
                page_id: '',
                layout: '',
                priority: '100'
            });
            ds.insert(0, record);
            grid.startEditing(0, 2);
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
                url: Axis.getUrl('template_layout/save'),
                params: {
                    data: Ext.encode(data),
                    tId: Layout.templateId
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
            
            if (!confirm('Delete Items?'))
                return;
                
            var data = {};
            
            for (var i = 0; i < selectedItems.length; i++) {
                if (!selectedItems[i]['data']['id'])
                    continue;
                data[i] = selectedItems[i]['data']['id'];
            }
                
            Ext.Ajax.request({
                url: Axis.getUrl('template_layout/delete'),
                params: {
                    data: Ext.encode(data),
                    tId: Layout.templateId
                },
                callback: function() {
                    ds.commitChanges();
                    ds.reload();
                }
            });
        }
    }
    
    Axis.Template.Layout = Layout;
    
    Ext.QuickTips.init();
    
    var dsPages = new Ext.data.JsonStore({
        id: 'id',
        fields: ['id', 'name'],
        data: Axis.pages
    });
   
    var ds = new Ext.data.Store({
        url: Axis.getUrl('template_layout/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, Layout.record),
        pruneModifiedRecords: true
    });
    
    var selectModel = new Ext.grid.CheckboxSelectionModel();
    
    var existLayoutStore = new Ext.data.Store({
        url:  Axis.getUrl('template_layout/list-collect'),
        reader: new Ext.data.JsonReader({
            root: 'data'
        }, ['name']),
        autoLoad: true
    })
    
    var layoutCombo = new Ext.form.ComboBox({
        id: 'layoutCombo',
        triggerAction: 'all',
        displayField: 'name',
        typeAhead: true,
        mode: 'local',
        valueField: 'name',
        editable: false,
        store: existLayoutStore
    })
    
    var cm = new Ext.grid.ColumnModel([
        selectModel, {
            header: "Priority".l(),
            dataIndex: 'priority',
            width: 80,
            sortable: true,
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        },{
            header: "Layout".l(),
            dataIndex: 'layout',
            width: 200,
            editor: layoutCombo
        },{
            header: "Page".l(),
            dataIndex: 'page_id',
            width: 250,
            sortable: true,
            mode: 'local',
            editor: new Ext.form.ComboBox({
                typeAhead: true,
                triggerAction: 'all',
                lazyRender: true,
                store: dsPages,
                editable: false,
                displayField: 'name',
                valueField: 'id',
                mode: 'local'
            }),
            renderer: function(value) {
                if (value == '0' || value == '') {
                    return 'None';
                } else {
                    for (var i in Axis.pages)
                       if (Axis.pages[i]['id'] == value)
                           return Axis.pages[i]['name'];
                    return value;
                }
            }
        }
    ]);
    
    var grid = new Ext.grid.EditorGridPanel({
        title: 'Layouts'.l(),
        ds: ds,
        cm: cm,
        sm: selectModel,
        viewConfig: {
            forceFit:true,
            emptyText: 'No records found'.l()
        },
        clicksToEdit: 1,
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
            handler : Layout.create
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            cls: 'x-btn-text-icon',
            handler : Layout.save
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler : Layout.remove
        }, '->', {
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-icon',
            handler: function() {
                grid.getStore().reload();
            }
        }]
    });
    
    Layout.grid = grid;
}, this);