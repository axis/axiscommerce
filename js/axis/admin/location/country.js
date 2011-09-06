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

var Country = {

    activeId: 0,

    record: null,

    create: function (){
        Country.grid.stopEditing();
        var record = new Country.record({
            name: '',
            iso_code_2: '',
            iso_code_3: '',
            address_format_id: ''
        });
        Country.grid.getStore().insert(0, record);
        Country.grid.startEditing(0, 2);
    },

    save: function() {
        var modified = Country.grid.getStore().getModifiedRecords();

        if (!modified.length) {
            return;
        }

        var data = {};

        for (var i = 0; i < modified.length; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }

        Ext.Ajax.request({
            url: Axis.getUrl('location/country/batch-save'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                var ds = Country.grid.getStore();
                ds.commitChanges();
                ds.reload();
            }
        });
    },

    remove: function() {
        var selectedItems = Country.grid.getSelectionModel().selections.items;

        if (!selectedItems.length || !confirm('Are you sure?'.l())) {
            return;
        }

        var data = {};

        for (var i = 0; i < selectedItems.length; i++) {
            if (!selectedItems[i]['data']['id']) continue;
            data[i] = selectedItems[i]['data']['id'];
        }

        Ext.Ajax.request({
            url: Axis.getUrl('location/country/remove'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                Country.grid.getStore().reload();
            }
        });
    }
};

Ext.onReady(function() {

    Ext.QuickTips.init();

    Country.record = Ext.data.Record.create([
        {name: 'id', type: 'int'},
        {name: 'name'},
        {name: 'iso_code_2'},
        {name: 'iso_code_3'},
        {name: 'address_format_id', type: 'int'}
    ]);

    var ds = new Ext.data.Store({
        autoLoad: true,
        baseParams: {
            limit: 25
        },
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Country.record),
        remoteSort: true,
        sortInfo: {
            field: 'name',
            direction: 'ASC'
        },
        url: Axis.getUrl('location/country/list')
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: 'Id'.l(),
            dataIndex: 'id'
        }, {
            header: "Name".l(),
            id: 'name',
            dataIndex: 'name',
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }, {
            header: "ISO 2".l(),
            dataIndex: 'iso_code_2',
            width: 100,
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }, {
            header: "ISO 3".l(),
            dataIndex: 'iso_code_3',
            width: 100,
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }, {
            header: "Address Format".l(),
            dataIndex: 'address_format_id',
            width: 130,
            editor: new Ext.form.ComboBox({
                editable: false,
                typeAhead: true,
                triggerAction: 'all',
                store: new Ext.data.ArrayStore({
                    data: addressFormats, // see index.phtml
                    fields: ['id', 'name']
                }),
                displayField: 'name',
                valueField: 'id',
                mode: 'local'
            }),
            renderer: function(v) {
                var i = 0;
                while (addressFormats[i]) {
                    if (v == addressFormats[i][0]) {
                        return addressFormats[i][1];
                    }
                    i++;
                }
                return "None";
            },
            filter: {
                editable: false,
                store: new Ext.data.ArrayStore({
                    data: addressFormats, // see index.phtml
                    fields: ['id', 'name']
                })
            }
        }]
    });

    Country.grid = new Axis.grid.EditorGridPanel({
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
            handler : Country.create
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler : Country.save
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler : Country.remove
        }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler : function() {
                Country.grid.getStore().reload();
            }
        }]
    });

    new Axis.Panel({
        items: [
            Country.grid
        ]
    });
});