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
            {name: 'name'},
            {name: 'template'},
            {name: 'event'},
            {name: 'from'},
            {name: 'type'},
            {name: 'status'},
            {name: 'site'}
        ]),
        create: function() {
            grid.stopEditing();
            var record = new Item.record({
                event: 'contact_us',
                template: 'contact-us',
                from: 'email1',
                name: '',
                status: 1,
                type: 'html',
                site: ''
            });
            ds.insert(0, record);
            grid.getStore().getAt(0).set('event', 'default');
            grid.startEditing(0, 2);
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
                url:  Axis.getUrl('core/mail/batch-save'),
                params: {
                    data: Ext.encode(data)
                },
                callback: function() {
                    ds.commitChanges();
                    ds.reload();
                }
            });
        },
        edit: function(row) {
             editorForm.getForm().load({
                url:   Axis.getUrl('core/mail/load'),
                params: {templateId: row.id},
                method: 'post'
            });
             editorWin.show();
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
                url: Axis.getUrl('core/mail/remove'),
                params: {data: Ext.encode(data)},
                callback: function() {
                    ds.reload();
                }
            });
        }
    };
    
    Ext.QuickTips.init();
    
    var ds = new Ext.data.Store({
        url: Axis.getUrl('core/mail/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Item.record),
        pruneModifiedRecords: true
    });
    
    var status = new Axis.grid.CheckColumn({
        header: "Status".l(),
        dataIndex: 'status'
    });
    
    var siteStore = new Ext.data.JsonStore({
        id: 'id',
        fields: ['id', 'name'],
        data: sites
    });
    
    var eventStore = new Ext.data.Store({
        url:  Axis.getUrl('core/mail/list-event'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, ['id', 'name']),
        autoLoad: true,
        listeners: {
            load: tryLoadGrid
        }
    });
    
    var templateStore = new Ext.data.Store({
        url:  Axis.getUrl('core/mail/list-template'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, ['id', 'name']),
        autoLoad: true,
        listeners: {
            load: tryLoadGrid
        }
    });
    
    var mailStore = new Ext.data.Store({
        url:  Axis.getUrl('core/mail/list-mail'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, ['id', 'name']),
        autoLoad: true,
        listeners: {
            load: tryLoadGrid
        }
    });
    
    var typeStore = new Ext.data.SimpleStore({
        data: [[1, 'txt'],[2, 'html']],
        fields: ['id', 'name']
    });
    
    var cm = new Ext.grid.ColumnModel([
        {
            header: "Id".l(),
            dataIndex: 'id',
            width: 60,
            sortable: true
        }, {
            header: "Event".l(),
            dataIndex: 'event',
            width: 200,
            editor:  new Ext.form.ComboBox({
                triggerAction: 'all',
                displayField: 'name',
                typeAhead: true,
                mode: 'local',
                valueField: 'id',
                store: eventStore
            }),
            renderer: function(value, meta) {
                 if (eventStore.getById(value)) {
                    return eventStore.getById(value).data.name;
                 } else {
                    return 'Undefined'.l();
                }
            }
        }, {
            header: "Template".l(),
            dataIndex: 'template',
            id: 'template',
            width: 170,
            sortable: true,
            editor: new Ext.form.ComboBox({
                triggerAction: 'all',
                displayField: 'name',
                typeAhead: true,
                mode: 'local',
                valueField: 'id',
                store: templateStore
            }),
            renderer: function(value, meta) {
                 if (templateStore.getById(value)) {
                    return templateStore.getById(value).data.name;
                } else {
                     return 'None';
                }
            }
        }, {
            header: "Sender".l(),
            dataIndex: 'from',
            width: 200,
            editor: new Ext.form.ComboBox({
                triggerAction: 'all',
                displayField: 'name',
                typeAhead: true,
                mode: 'local',
                valueField: 'id',
                store: mailStore
            }),
            renderer: function(value, meta) {
                 if (mailStore.getById(value))
                    return mailStore.getById(value).data.name;
                else 
                    return 'None';
            }
        }, {
            header: "Name".l(),
            dataIndex: 'name',
            width: 130,
            sortable: true,
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }, status, {
            header: "Type".l(),
            dataIndex: 'type',
            width: 80,
            editor:  new Ext.form.ComboBox({
                triggerAction: 'all',
                displayField: 'name',
                typeAhead: true,
                mode: 'local',
                valueField: 'name',
                store: typeStore
            })
        },{
            header: "Sites".l(),
            dataIndex: 'site',
            width: 150,
            editor:  new Ext.ux.Andrie.Select(Ext.applyIf({
                fieldLabel:  'Field',
                multiSelect: true,
                minLength: 1
            }, {
                store:siteStore,
                valueField:'id',
                displayField:'name',
                triggerAction:'all',
                mode:'local'
            })),
            renderer: function(value, meta) {
                var ret = new Array();
                value = value.split(',');
                for (var i = 0, n = value.length; i < n; i++) {
                    if (value[i] != '' && siteStore.getById(value[i])) {
                        ret.push(siteStore.getById(value[i]).data.name);
                    }
                }
                ret = ret.join(', ');
                return ret ;
            }
        }
    ]);
    
    var grid = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'template',
        ds: ds,
        cm: cm,
        plugins: [status],
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
            handler: Item.create
        },{
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            cls: 'x-btn-text-icon',
            handler: Item.save
        },{
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
    
    var loadedStore = 0;
    function tryLoadGrid(){
        if (++loadedStore == 3)
            ds.load();
    }
    
    var textArea = new Ext.form.TextArea({
         fieldLabel: 'Content'.l(),
        listeners: {
            render: {
                fn: function(f){
                    f.resizer = new Ext.Resizable(f.getEl(),{handles:'s,se,e',wrap:true});
                    f.resizer.on('resize',function(){delete f.anchor;});
                }
            }
        },
        onResize: function(){
            Ext.form.TextArea.superclass.onResize.apply(this, arguments);
            var r = this.resizer;
            var csize = r.getResizeChild().getSize();
            r.el.setSize(csize.width, csize.height);
        },
        anchor: '98% 70%',
        name: 'content',
        id: 'resizable_area'
    });
    
    var editorForm = new Ext.FormPanel({
        url: Axis.getUrl('core/mail/batch-save'),
        border: false,
        bodyStyle: 'padding:5px 5px 0',
        labelWidth: 70,
        items: [{
            layout:'column',
            border: false,
            items:[{
                columnWidth:.5,
                border: false,
                defaults: {
                    width: 130
                },
                layout: 'form',
                items: [new Ext.form.ComboBox({
                    triggerAction: 'all',
                    displayField: 'name',
                    typeAhead: true,
                    mode: 'local',
                    valueField: 'id',
                    fieldLabel: 'Template'.l(),
                    name: 'template',
                    hiddenName: 'template',
                    anchor:'95%',
                    store: templateStore
                }), new Ext.form.ComboBox({
                    triggerAction: 'all',
                    displayField: 'name',
                    typeAhead: true,
                    mode: 'local',
                    valueField: 'id',
                    fieldLabel: 'Event'.l(),
                    name: 'event',
                    hiddenName: 'event',
                    anchor:'95%',
                    store: eventStore
                }), new Ext.form.ComboBox({
                    triggerAction: 'all',
                    displayField: 'name',
                    typeAhead: true,
                    mode: 'local',
                    valueField: 'id',
                    fieldLabel: 'Sender'.l(),
                    name: 'from',
                    hiddenName: 'from',
                    anchor:'95%',
                    store: mailStore
                })]
            },{
                columnWidth:.5,
                border: false,
                defaults: {
                    width: 130
                },
                layout: 'form',
                items: [{
                    xtype: 'textfield',
                    name: 'name',
                    anchor:'95%',
                    fieldLabel: 'Name'.l()
                }, new Ext.form.ComboBox({
                    triggerAction: 'all',
                    displayField: 'name',
                    typeAhead: true,
                    mode: 'local',
                    valueField: 'name',
                    store: typeStore,
                    name: 'type',
                    hiddenName: 'type',
                    anchor:'95%',
                    fieldLabel: 'Type'.l()
                }), new Ext.ux.Andrie.Select(Ext.applyIf({
                    fieldLabel:  'Sites'.l(),
                    multiSelect: true,
                    minLength: 1
                    }, {
                        store: siteStore,
                        valueField:'id',
                        displayField:'name',
                        triggerAction:'all',
                        mode:'local',
                        hiddenName: 'site',
                        anchor:'95%',
                        name: 'site'
                }))]
            }]},textArea,{
                xtype: 'hidden',
                name: 'id'
            }]
    });
    
    var editorWin =  new Ext.Window({
        layout: 'fit',
        width: 600,
        maximizable:true,
        height: 400,
        plain: false, 
        title: 'Template'.l(),
        closeAction: 'hide',
        buttons: [{
            text: 'Save'.l(),
            handler: function (){
            editorForm.getForm().submit({
                method: 'post',
                success: function(form, response ) {
                    editorWin.hide();
                    ds.reload();
                },
                failure: function(form, response){
                    alert('Error ' + response.result.data.error);
                } 
            });
            }
        },{
            text: 'Cancel'.l(),
            handler: function(){
                editorWin.hide();
            }
        }],
        items: editorForm
    });
    
   grid.on('rowdblclick', function(grid, index){
       Item.edit(grid.getStore().getAt(index));
   });
   
}, this);