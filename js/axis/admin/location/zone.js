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

var Zone = {

    activeId: 0,

    grid: null,

    record: null,

    create: function () {
        Zone.grid.stopEditing();
        var record = new Zone.record({
            name: '',
            code: '',
            country_id: ''
        });
        Zone.grid.getStore().insert(0, record);
        Zone.grid.startEditing(0, 2);
    },

    save: function() {
        var modified = Zone.grid.getStore().getModifiedRecords();

        if (!modified.length) {
            return;
        }

        var data = {};

        for (var i = 0; i < modified.length; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }

        Ext.Ajax.request({
            url: Axis.getUrl('location/zone/batch-save'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                var ds = Zone.grid.getStore();
                ds.commitChanges();
                ds.reload();
            }
        });
    },

    remove: function() {
        var selectedItems = Zone.grid.getSelectionModel().selections.items;

        if (!selectedItems.length || !confirm('Are you sure?')) {
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
            url: Axis.getUrl('location/zone/remove'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                Zone.grid.getStore().reload();
            }
        })
    }
};

Ext.onReady(function() {

    Zone.record = Ext.data.Record.create([
        {name: 'id', type: 'int'},
        {name: 'code'},
        {name: 'name'},
        {name: 'country_id', type: 'int'}
    ]);

    var dsCountry = new Ext.data.JsonStore({
        id: 'id',
        fields: ['id', 'name'],
        data: countries
    });

    var ds = new Ext.data.Store({
        autoLoad: true,
        baseParams: {
            limit: 25
        },
        url: Axis.getUrl('location/zone/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Zone.record),
        remoteSort: true,
        pruneModifiedRecords: true,
        sortInfo: {
            field: 'name',
            dir: 'ASC'
        }
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: 'Id'.l(),
            dataIndex: 'id',
            width: 90
        }, {
            header: "Name".l(),
            dataIndex: 'name',
            id: 'name',
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }, {
            header: "Code".l(),
            dataIndex: 'code',
            width: 190,
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }, {
            header: "Country".l(),
            dataIndex: 'country_id',
            width: 190,
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
            },
            filter: {
                resetValue: 'reset',
                store: new Ext.data.JsonStore({
                    fields: ['id', 'name'],
                    data: countries
                })
            }
        }]
    });

    Zone.grid = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'name',
        ds: ds,
        cm: cm,
        plugins: [new Axis.grid.Filter()],
        bbar: new Axis.PagingToolbar({
            store: ds
        }),
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
            handler: Zone.create
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            cls: 'x-btn-text-icon',
            handler: Zone.save
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler: Zone.remove
        }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-text-icon',
            handler: function() {
                Zone.grid.getStore().reload();
            }
        }]
    });

    new Axis.Panel({
        items: [
            Zone.grid
        ]
    });
});