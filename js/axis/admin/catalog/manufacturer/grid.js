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
 */

Ext.onReady(function(){

    var record = [
        {name: 'id', type: 'int'},
        {name: 'name', type: 'string'},
        {name: 'key_word', type: 'string', mapping: 'url'},
        {name: 'image', type: 'string'}
    ];

    for (var id in Axis.locales) {
        record.push({
            name: 'description[' + id + '][title]',
            mapping: 'description.lang_' + id + '.title'
        }, {
            name: 'description[' + id + '][description]',
            mapping: 'description.lang_' + id + '.description'
        });
    }

    var manufacturer_object = Ext.data.Record.create(record);

    var ds = new Ext.data.Store({
        autoLoad: true,
        baseParams: {
            limit: 25
        },
        url: Axis.getUrl('catalog/manufacturer/list'),
        reader: new Ext.data.JsonReader({
            root : 'data',
            totalProperty: 'count',
            id: 'id'
        }, manufacturer_object),
        remoteSort: true,
        pruneModifiedRecords: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        }
    })

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: 'Id'.l(),
            dataIndex: 'id',
            width: 90
        }, {
            header: 'Name'.l(),
            dataIndex: 'name',
            id: 'name',
            editor: new Ext.form.TextField({
                allowBlank: false,
                maxLength: 128
            }),
            filter: {
                operator: 'LIKE'
            }
        }, {
            header: 'Url'.l(),
            dataIndex: 'key_word',
            editor: new Ext.form.TextField({
                allowBlank: false,
                maxLength: 128
            }),
            width: 190,
            table: 'ch',
            filter: {
                operator: 'LIKE'
            }
        }, {
            header: 'Image'.l(),
            dataIndex: 'image',
            editor: new Ext.form.TextField({
                allowBlank: true,
                maxLength: 255
            }),
            width: 190,
            filter: {
                operator: 'LIKE'
            }
        }]
    });

    var grid = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'name',
        cm: cm,
        id: 'grid',
        store: ds,
        plugins: [new Axis.grid.Filter()],
        tbar: [{
            text: 'Add'.l(),
            handler: add,
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/add.png'
        }, {
            text: 'Edit'.l(),
            handler: function(){
                edit();
            },
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/page_edit.png'
        }, {
            text: 'Save'.l(),
            handler: save,
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/save_multiple.png'
        }, {
            text: 'Delete'.l(),
            handler: deleteSelected,
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/delete.png'
        },'->', {
            text: 'Reload'.l(),
            handler: reload,
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/refresh.png'
        }],
        bbar: new Axis.PagingToolbar({
            pageSize: 25,
            store: ds
        })
    });

    new Axis.Panel({
        items: [grid]
    });

    Ext.getCmp('grid').on('rowdblclick', function(grid, index){
        edit(grid.getStore().getAt(index));
    });

    function reload() {
        Ext.getCmp('grid').store.reload();
    }

    function add() {
        Ext.getCmp('window').show();
        Ext.getCmp('window').setTitle('New Manufacturer'.l());
        Ext.getCmp('form').getForm().clear();
    }

    function edit(record) {
        record = record || Ext.getCmp('grid').getSelectionModel().getSelected();

        if (!record) {
            return;
        }

        Ext.getCmp('window').show();
        Ext.getCmp('window').setTitle(record.get('name'));
        var form = Ext.getCmp('form').getForm();
        form.clear();
        form.setValues(record.data);
    }

    function save() {
        var modified = Ext.getCmp('grid').store.getModifiedRecords();

        if (!modified.length) {
            return false;
        }

        var data = {};
        for (var i = 0, len = modified.length; i < len; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }
        Ext.Ajax.request({
            url: Axis.getUrl('catalog/manufacturer/batch-save'),
            method: 'post',
            params: {
                data: Ext.encode(data)
            },
            success: function(response, request) {
                response = Ext.decode(response.responseText);
                if (response.success) {
                    reload();
                }
            }
        })
    }

    function deleteSelected() {
        var selections = Ext.getCmp('grid').getSelectionModel().getSelections();

        if (!selections.length || !confirm('Are you sure?'.l())) {
            return;
        }

        var data = {};
        for (var i = 0, len = selections.length; i < len; i++) {
            data[i] = selections[i]['id'];
        }
        Ext.Ajax.request({
            url: Axis.getUrl('catalog/manufacturer/remove'),
            method: 'post',
            params: {
                data: Ext.encode(data)
            },
            success: reload
        })
    }
});