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

Ext.onReady(function(){
    var Item = {
        activeId: 0,

        record: {},

        create: function () {
            orderStatusChild.removeAll();
            Ext.getCmp('windowOrder').show();
//            Ext.getCmp('formOrder').getForm().clear();
        },

        getSelectedId: function() {
            var selModel = grid.getSelectionModel();
            var selectedItems = grid.getSelectionModel().selections.items;
            if (!selectedItems.length) {
                return false;
            }
            if (selectedItems[0]['data']['id']) {
                return selectedItems[0].id;
            }
            return false;
        },
        edit: function(id) {
            id = id || Item.getSelectedId();
            if (!id) {
                return;
            }
            orderStatusChild.removeAll();
            orderStatusChild.baseParams = {
                'statusId': id
            };
            orderStatusChild.load();
            Ext.getCmp('formOrder').load({
                url: Axis.getUrl('sales_order-status/get-info'),
                params: {'statusId': id},
                method: 'post'
            });
            Ext.getCmp('windowOrder').show();
        },

        batchSave: function() {
            var data = {};
            var modified = ds.getModifiedRecords();
            if (!modified.length) {
                return;
            }

            for (var i = 0; i < modified.length; i++) {
                data[modified[i]['id']] = modified[i]['data'];
            }

            Ext.Ajax.request({
                url: Axis.getUrl('sales_order-status/batch-save'),
                params: {
                    data: Ext.encode(data)
                },
                callback: function() {
                    ds.commitChanges();
                    ds.reload();
                    Ext.getCmp('add-order-status-from').store.reload();
                }
            });
        },

        remove: function() {
            if (!confirm('Are you sure?'.l())) {
                return;
            }
            var data = {};
            var selectedItems = grid.getSelectionModel().selections.items;
            for (var i = 0; i < selectedItems.length; i++) {
                if (!selectedItems[i]['data']['id']) continue;
                data[i] = selectedItems[i]['data']['id'];
            }

            Ext.Ajax.request({
                url: Axis.getUrl('sales_order-status/delete'),
                params: {data: Ext.encode(data)},
                callback: function() {
                    ds.reload();
                    Ext.getCmp('add-order-status-from').store.reload();
                }
            });
        }
    };

    Ext.QuickTips.init();

    /* Build record */
    var record = [{name: 'id'}, {name: 'name'}, {name: 'system'}];
    for (var languageId in Axis.languages) {
         record.push({'name': 'status_name_' + languageId});
    }
    Item.record = Ext.data.Record.create(record);

    var ds = new Ext.data.Store({
        url: Axis.getUrl('sales_order-status/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Item.record),
        pruneModifiedRecords: true
    });

    var columns = [{
         header: "Id".l(),
         dataIndex: 'id',
         width: 40,
         sortable: true
    }, {
        header: 'Status'.l(),
        dataIndex: 'system',
        width: 180,
        renderer: function(value) {
            if (value == 1) return 'System (locked)'
            else return 'Normal'
        }
    }, {
         header: "Method Name".l(),
         id: 'name',
         dataIndex: 'name',
         sortable: true,
         editor: new Ext.form.TextField({
             allowBlank: false
         })
    }];
    for (var languageId in Axis.languages) {
        columns.push({
            header: 'Name ({language})'.l('core', Axis.languages[languageId]),
            dataIndex: 'status_name_' + languageId,
            width: 180,
            sortable: false,
            editor: new Ext.form.TextField({
                allowBlank: false
            })
         });
    }
    var cm = new Ext.grid.ColumnModel(columns);

    var grid = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'name',
        ds: ds,
        cm: cm,
        id:'grid-status',
        listeners: {
            'rowdblclick': function(grid, index, e) {
                Item.edit(grid.store.getAt(index).id);
            }
        },
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
            handler: Item.create
        }, {
            text: 'Edit'.l(),
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            cls: 'x-btn-text-icon',
            handler: Item.edit
        },{
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            cls: 'x-btn-text-icon',
            handler: Item.batchSave
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler: Item.remove
        }, '->', {
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-icon',
            handler: function(){
                grid.getStore().reload();
                Ext.getCmp('add-order-status-from').store.reload();
            }
        }]
    });

    new Axis.Panel({
        items: [
            grid
        ]
    });

    ds.load();

}, this);