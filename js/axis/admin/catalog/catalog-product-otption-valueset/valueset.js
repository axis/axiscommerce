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

var Set = {

    window: null,

    form: null,

    grid: null,

    record: Ext.data.Record.create([
        {name: 'id', type: 'int'},
        {name: 'name', type: 'string'}
    ]),

    create: function (){
        Set.form.getForm().setValues({
            id: 0,
            name: ''
        });
        Set.window.setTitle('New Valueset'.l());
        Set.window.show();
    },

    load: function(id) {
        var record = Set.grid.getStore().getById(id);
        Set.form.getForm().setValues({
            id: record.get('id'),
            name: record.get('name')
        });
        Set.window.setTitle(record.get('name'));
        Set.window.show();
    },

    save: function() {
        Set.form.getForm().submit({
            url: Axis.getUrl('catalog_product-option-valueset/save-set'),
            success: function(form, action) {
                var response = Ext.decode(action.response.responseText);
                Set.window.hide();
                Set.grid.getStore().reload();
                Value.load(response.data.id);
            }
        });
    },

    remove: function() {
        var selectedItems = Set.grid.getSelectionModel().selections.items;

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
            url: Axis.getUrl('catalog_product-option-valueset/delete-sets'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                delete Value.grid.getStore().baseParams.setId;
                Set.grid.getStore().reload();
                Value.grid.getStore().reload();
            }
        });
    }
};

var Value = {

    grid: null,

    record: null,

    create: function() {
        if (!Value.grid.getStore().baseParams.setId) {
            return alert('Select the valueset on the left panel'.l());
        }
        Value.grid.stopEditing();
        var defaults = {
            sort_order: 10,
            valueset_id: Value.grid.getStore().baseParams.setId
        };
        for (var id in Axis.locales) {
           defaults['name_' + id] = '';
        }
        var newValue = new Value.record(defaults);
        Value.grid.getStore().insert(0, newValue);
        Value.grid.startEditing(0, 2);
    },

    save: function() {
        var modified = Value.grid.getStore().getModifiedRecords();
        if (!modified.length) {
            return;
        }

        var data = {};
        for (var i = 0; i < modified.length; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }

        var jsonData = Ext.encode(data);
        Ext.Ajax.request({
            url: Axis.getUrl('catalog_product-option-valueset/save-values'),
            params: {
                data: jsonData,
                setId: Value.grid.getStore().baseParams.setId
            },
            callback: function() {
                Value.grid.getStore().commitChanges();
                Value.grid.getStore().reload();
            }
        });
    },

    load: function(id) {
        Value.grid.getStore().baseParams.setId = id;
        Value.grid.getStore().load();
    }
};

Ext.onReady(function(){

    var dsSet = new Ext.data.Store({
        autoLoad: true,
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('catalog_product-option-valueset/list-sets')
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, Set.record)
    });

    var cmSet = new Ext.grid.ColumnModel({
        columns: [{
            header: "Name".l(),
            id: 'name',
            dataIndex: 'name',
            editor: new Ext.form.TextField({
                allowBlank: false
            }),
            filter: {
                operator: 'LIKE'
            }
        }]
    });

    Set.grid = new Axis.grid.GridPanel({
        autoExpandColumn: 'name',
        ds: dsSet,
        cm: cmSet,
        region: 'west',
        width: 250,
        plugins: [new Axis.grid.Filter()],
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler: Set.create
        }, {
            text: 'Edit'.l(),
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            handler: function(){
                var selected = Set.grid.getSelectionModel().getSelected();

                if (!selected) {
                    return;
                }

                Set.load(selected.get('id'));
            }
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: Set.remove
        }, '->', {
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-icon',
            handler: function(){
                Set.grid.getStore().reload();
            }
        }]
    });

    Set.grid.on('rowclick', function(grid, index, e) {
        Value.load(grid.getStore().getAt(index).get('id'));
    });
    Set.grid.on('rowdblclick', function(grid, index, e) {
        Set.load(grid.getStore().getAt(index).get('id'));
    });

    Set.form = new Axis.form.FormPanel({
        bodyStyle: 'padding: 10px;',
        items: [{
            allowBlank: false,
            anchor: '-5',
            fieldLabel: 'Name'.l(),
            initialValue: '',
            maxLength: 128,
            name: 'name',
            xtype: 'textfield'
        }, {
            name: 'id',
            xtype: 'hidden'
        }]
    });

    Set.window = new Axis.Window({
        width: 350,
        height: 150,
        items: [
            Set.form
        ],
        buttons: [{
            icon: Axis.skinUrl + '/images/icons/database_save.png',
            text: 'Save'.l(),
            handler: function() {
                Set.save();
            }
        }, {
            icon: Axis.skinUrl + '/images/icons/cancel.png',
            text: 'Cancel'.l(),
            handler: function(){
                Set.window.hide();
            }
        }]
    });

    valueRecord = [
        {name: 'id', type: 'int'},
        {name: 'sort_order', type: 'int'},
        {name: 'valueset_id', type: 'int'}
    ];

    var valueCols = [{
        dataIndex: 'id',
        header: 'Id'.l(),
        width: 90
    }];
    for (var id in Axis.locales) {
        valueRecord.push(
            {name: 'name_' + id}
        );
        valueCols.push({
            dataIndex: 'name_' + id,
            editor: new Ext.form.TextField({
                allowBlank: false,
                maxLength: 128
            }),
            header: 'Title ({language})'.l('core', Axis.locales[id]['language']),
            table: 'cpovt',
            filter: {
                operator: 'LIKE',
                name: 'name'
            }
        });
    }

    Value.record = new Ext.data.Record.create(valueRecord);

    var valueStore = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('catalog_product-option-valueset/list-values')
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, Value.record)
    });

    valueCols.push({
        align: 'right',
        header: 'Sort Order'.l(),
        dataIndex: 'sort_order',
        editor: new Ext.form.NumberField({
            allowBlank: false,
            allowNegative: false,
            maxValue: 250
        })
    });

    Value.grid = new Axis.grid.EditorGridPanel({
        ds: valueStore,
        cm: new Ext.grid.ColumnModel({
            defaults: {
                sortable: true
            },
            columns: valueCols
        }),
        viewConfig: {
            forceFit: true,
            emptyText: 'No records found'.l()
        },
        plugins: [new Axis.grid.Filter()],
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
            handler : Value.create
        },{
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/accept.png',
            cls: 'x-btn-text-icon',
            handler : Value.save
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler : function() {
                var selectedItems = Value.grid.getSelectionModel().getSelections();

                if (!selectedItems.length || !confirm('Are you sure?'.l())) {
                    return;
                }

                var data = {};
                for (var i = 0; i < selectedItems.length; i++) {
                    data[i] = selectedItems[i].id;
                }
                Ext.Ajax.request({
                    url: Axis.getUrl('catalog_product-option-valueset/delete-values'),
                    params: {
                        data: Ext.encode(data)
                    },
                    callback: function() {
                        Value.grid.getStore().commitChanges();
                        Value.grid.getStore().reload();
                    }
                });
            }
        }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-text-icon',
            handler: function(){
                if (!Value.grid.getStore().baseParams.setId) {
                    return alert('Select the valueset on the left panel'.l());
                }
                Value.grid.getStore().reload();
            }
        }]
    });

    new Axis.Panel({
        items: [
            Set.grid,
            Value.grid
        ]
    });
});