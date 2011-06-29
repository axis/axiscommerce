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

var TaxClass = {

    record: null,

    grid: null,

    create: function() {
        TaxClass.grid.stopEditing();
        var record = new TaxClass.record({
            name: '',
            description: '',
            type: 'new'
        });
        TaxClass.grid.getStore().insert(0, record);
        TaxClass.grid.startEditing(0, 2);
    },

    getSelectedId: function() {
        var selectedItems = TaxClass.grid.getSelectionModel().getSelections();
        if (!selectedItems.length) {
            return false;
        }
        if (selectedItems[0]['data']['id']) {
            return selectedItems[0].id;
        }
        return false;
    },

    save: function() {
        var modified = TaxClass.grid.getStore().getModifiedRecords();
        if (!modified.length) {
            return;
        }

        var data = {};
        for (var i = 0; i < modified.length; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }

        Ext.Ajax.request({
            url: Axis.getUrl('tax_class/save'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                var ds = TaxClass.grid.getStore();
                ds.commitChanges();
                ds.reload();
            }
        });
    },

    remove: function() {
        var selectedItems = TaxClass.grid.getSelectionModel().getSelections();
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
            url: Axis.getUrl('tax_class/delete'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                TaxClass.grid.getStore().reload();
            }
        });
    }
};

Ext.onReady(function() {

    Ext.QuickTips.init();

    TaxClass.record = Ext.data.Record.create([
        {name: 'id', type: 'int'},
        {name: 'name'},
        {name: 'description'},
        {name: 'created_on', type: 'date', dateFormat: 'Y-m-d H:i:s'},
        {name: 'modified_on', type: 'date', dateFormat: 'Y-m-d H:i:s'}
    ]);

    var ds = new Ext.data.Store({
        autoLoad: true,
        baseParams: {
            limit: 25
        },
        pruneModifiedRecords: true,
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, TaxClass.record),
        remoteSort: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        },
        url: Axis.getUrl('tax_class/list')
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: 'id'.l(),
            dataIndex: 'id',
            width: 90
        }, {
            header: "Name".l(),
            dataIndex: 'name',
            width: 150,
            editor: new Ext.form.TextField({
                allowBlank: false
            })
        }, {
            header: "Description".l(),
            dataIndex: 'description',
            id: 'description',
            editor: new Ext.form.TextField({
                allowBlank: false
            })
        }, {
            header: "Created".l(),
            dataIndex: 'created_on',
            width: 130,
            renderer: function(v) {
                return Ext.util.Format.date(v) + ' ' + Ext.util.Format.date(v, 'H:i:s');
            }
        }, {
            header: "Modified".l(),
            dataIndex: 'modified_on',
            width: 130,
            renderer: function(v) {
                return Ext.util.Format.date(v) + ' ' + Ext.util.Format.date(v, 'H:i:s');
            }
        }]
    });

    TaxClass.grid = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'description',
        ds: ds,
        cm: cm,
        plugins: [new Axis.grid.Filter()],
        bbar: new Axis.PagingToolbar({
            store: ds
        }),
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler: TaxClass.create
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler: TaxClass.save
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: TaxClass.remove
        }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: function() {
                TaxClass.grid.getStore().reload();
            }
        }]
    });

    new Axis.Panel({
        items: [
            TaxClass.grid
        ]
    });
});
