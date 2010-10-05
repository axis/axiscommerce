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

Ext.namespace('Axis', 'Axis.Template', 'Axis.Template.Box');
Ext.onReady(function(){
    Ext.apply(Axis.Template.Box, {
        templateId: 0,
        record: Ext.data.Record.create([
            {name: 'id', type: 'int'},
            {name: 'block'},
            {name: 'box_status', type: 'int'},
            {name: 'class'},
            {name: 'sort_order', type: 'int'},
            {name: 'show'}
        ]),
        getSelectedId: function() {
            var selectedItems = grid.getSelectionModel().selections.items;
            if (!selectedItems.length || !selectedItems[0]['data']['id']) {
                return false;
            }
            return selectedItems[0].id;
        },
        loadGrid: function(templateId) {
            Box.templateId = templateId;
            ds.commitChanges();
            ds.load({params: {tId: templateId}});
        },
        create: function() {
            if (!Box.templateId)
                return alert('Select template on the left');
                
            grid.stopEditing();
            var record = new Box.record({
                'block': 'content',
                'box_status': 0,
                'class': '',
                'sort_order': 100,
                'show': ''
            });
            ds.insert(0, record);
            grid.startEditing(0, 1);
        },
        edit: function(id) {
            if (!id) {
                return;
            }
            Box.window.show();
            Box.window.load({
                url: Axis.getUrl('template_box/edit/'),
                params: {
                    boxId: id,
                    tId: Box.templateId
                },
                callback: function() {
                    Ext.ux.Table.colorize();
                    $(':checkbox', '#form-box').bind('click', function(){
                        var type = $(this).attr('id').substring(0, 4);
                        var pageId = $(this).attr('id').substring(5);
                        
                        if (type == 'hide'){
                           $('#show-' + pageId).removeAttr('checked');
                        } else{
                           $('#hide-' + pageId).removeAttr('checked');
                        }
                    });
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
                if (!selectedItems[i]['data']['id']) {
                    continue;
                }
                data[i] = selectedItems[i]['data']['id'];
            }
                
            Ext.Ajax.request({
                url: Axis.getUrl('template_box/delete'),
                params: {data: Ext.encode(data)},
                callback: function() {
                    ds.reload();
                }
            });
        },
        save: function() {
            Ext.Ajax.request({
                url: $('#form-box').get(0).action,
                form: 'form-box',
                success: function() {
                    Box.window.hide();
                    ds.reload();
                }
            });
        },
        saveMulti: function() {
            var data = {};
            var modified = ds.getModifiedRecords();
            if (!modified.length)
                return;
            
            for (var i = 0; i < modified.length; i++) {
                data[modified[i]['id']] = modified[i]['data'];
            }
            
            Ext.Ajax.request({
                url: Axis.getUrl('template_box/batch-save'),
                params: {
                    data: Ext.encode(data),
                    tId: Box.templateId
                },
                callback: function() {
                    ds.commitChanges();
                    ds.reload();
                }
            });
        },
        window: new Ext.Window({
            layout: 'fit',
            width: 650,
            height: 500,
            constrain: true,
            maximizable: true,
            closeAction: 'hide',
            bodyStyle:'background: white',
            title: 'Box'.l(),
            autoScroll:true,
            buttons: [{
                text: 'Save'.l(),
                handler: function() {
                    Box.save()
                }
            },{
                text: 'Cancel'.l(),
                handler: function(){
                    Box.window.hide();
                }
            }]
        })
    });
    var Box = Axis.Template.Box;
    
    var ds = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('template_box/list') 
        }),
        
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, Box.record)
    });
    
    var dsPages = new Ext.data.Store({
        data: Axis.pages,
        reader: new Ext.data.JsonReader({
            idProperty: 'id',
            fields: [
                {name: 'id', type: 'int'}, 
                {name: 'name'}
            ]
        })
    });
    
    var selectModel = new Ext.grid.CheckboxSelectionModel();
    
    var status = new Axis.grid.CheckColumn({
        header: 'Status'.l(),
        width: 50,
        dataIndex: 'box_status'
    });
    
    var actions = new Ext.ux.grid.RowActions({
        header:'Actions'.l(),
        actions:[{
            iconCls:'icon-edit',
            tooltip:'Edit'.l()
        }],
        callbacks: {
            'icon-edit': function(grid, record, action, row, col) {
                Box.edit(record.id);
            }
        }
    });
    
    var cm = new Ext.grid.ColumnModel([
        selectModel, {
            header: "Box".l(),
            dataIndex: 'class',
            sortable: true,
            mode: 'local',
            width: 220,
            editor: new Ext.form.ComboBox({
               typeAhead: true,
               triggerAction: 'all',
               lazyRender: true,
               store: Box.classes,
               mode: 'local'
            })
        }, {
            header: 'Layout block'.l(),
            dataIndex: 'block',
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }, {
            header: "Show on".l(), 
            sortable: true,
            dataIndex: 'show',
            width: 200,
            editor: new Ext.ux.Andrie.Select({
                multiSelect: true,
                store: dsPages,
                valueField: 'id',
                displayField: 'name',
                triggerAction: 'all',
                mode: 'local'
            })
            ,
            renderer: function(value, meta) {
                if (typeof(value) == 'undefined' || value == '')  {
                    return 'None'.l();
                }
                var ret = new Array();
                value = value.split(',');
                for (var i = 0, n = value.length; i < n; i++) {
                    if (value[i] != '') {
                        ret.push(dsPages.getById(value[i]).data.name);
                    }
                }
                ret = ret.join(', ');
                meta.attr = 'ext:qtip="Used on pages : ' + ret + '"';
                return ret;
            }
        }, {
            header: 'Sort Order'.l(),
            width: 80,
            dataIndex: 'sort_order',
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }, 
        status,
        actions
    ]);
    
    var grid = new Ext.grid.EditorGridPanel({
        title: 'Boxes'.l(),
        ds: ds,
        cm: cm,
        viewConfig: {
            forceFit:true,
            emptyText: 'No records found'.l()
        },
        selModel: selectModel,
        clicksToEdit: 1,
        plugins: [
            status, 
            actions,
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
            handler: Box.create
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            cls: 'x-btn-text-icon',
            handler: Box.saveMulti
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler: Box.remove
        }, '->', {
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-icon',
            handler: function() {
                grid.getStore().reload();
            }
        }]
    });
    grid.on('rowdblclick', function(grid, rowIndex, e) {
        //Ext.getCmp('window').show();
        Box.edit(grid.getStore().getAt(rowIndex).get('id'));
    })
    Box.grid = grid;
})