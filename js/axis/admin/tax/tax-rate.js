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

var TaxRate = {

    record: null,

    create: function () {
        TaxRate.grid.stopEditing();
        var record = new TaxRate.record({
            tax_class_id        : '',
            geozone_id          : '',
            customer_group_id   : '',
            rate                : 0,
            description         : '',
            type                : 'new'
        });
        TaxRate.grid.getStore().insert(0, record);
        TaxRate.grid.startEditing(0, 2);
    },

    getSelectedId: function() {
        var selectedItems = TaxRate.grid.getSelectionModel().getSelections();
        if (!selectedItems.length) {
            return false;
        }
        if (selectedItems[0]['data']['id']) {
            return selectedItems[0].id;
        }
        return false;
    },

    save: function() {
        var modified = TaxRate.grid.getStore().getModifiedRecords();
        if (!modified.length) {
            return;
        }

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
                var ds = TaxRate.grid.getStore();
                ds.commitChanges();
                ds.reload();
            }
        });
    },

    remove: function() {
        var selectedItems = TaxRate.grid.getSelectionModel().getSelections();
        if (!selectedItems.length || !confirm('Are you sure?'.l())) {
            return;
        }

        var data = {};
        for (var i = 0; i < selectedItems.length; i++) {
            if (!selectedItems[i]['data']['id']) continue;
            data[i] = selectedItems[i]['data']['id'];
        }

        Ext.Ajax.request({
            url: Axis.getUrl('tax_rate/delete'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                TaxRate.grid.getStore().reload();
            }
        });
    }
};

Ext.onReady(function() {

    Ext.QuickTips.init();

    TaxRate.record = Ext.data.Record.create([
        {name: 'id', type: 'int'},
        {name: 'tax_class_id', type: 'int'},
        {name: 'geozone_id', type: 'int'},
        {name: 'customer_group_id', type: 'int'},
        {name: 'rate', type: 'float'},
        {name: 'description'},
        {name: 'created_on', type: 'date', dateFormat: 'Y-m-d H:i:s'},
        {name: 'modified_on', type: 'date', dateFormat: 'Y-m-d H:i:s'}
    ]);

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
        autoLoad: true,
        baseParams: {
            limit: 25
        },
        pruneModifiedRecords: true,
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, TaxRate.record),
        remoteSort: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        },
        url: Axis.getUrl('tax_rate/list')
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
            header: "Tax Class".l(),
            dataIndex: 'tax_class_id',
            width: 190,
            editor: new Ext.form.ComboBox({
                editable: false,
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
                var record = dsTaxClass.getById(value);
                if (!record) {
                    return "None".l();
                } else {
                    return record.get('name');
                }
            },
            filter: {
                editable: false,
                store: new Ext.data.JsonStore({
                   id: 'id',
                   fields: ['id', 'name'],
                   data: tax_classes
                })
            }
        }, {
            header: "Customer Group".l(),
            dataIndex: 'customer_group_id',
            width: 160,
            editor: new Ext.form.ComboBox({
                editable: false,
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
                var record = dsCustomerGroups.getById(value);
                if (!record) {
                    return "None".l();
                } else {
                    return record.get('name');
                }
            },
            filter: {
                editable: false,
                store: new Ext.data.JsonStore({
                    id: 'id',
                    fields: ['id', 'name'],
                    data: customer_groups
                })
            }
        }, {
            header: "Geozone".l(),
            dataIndex: 'geozone_id',
            width: 160,
            editor: new Ext.form.ComboBox({
                editable: false,
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
                var record = dsGeozone.getById(value);
                if (!record) {
                    return "None".l();
                } else {
                    return record.get('name');
                }
            },
            filter: {
                editable: false,
                store: new Ext.data.JsonStore({
                    id: 'id',
                    fields: ['id', 'name'],
                    data: zones
                })
            }
        }, {
            header: "Rate(%)".l(),
            dataIndex: 'rate',
            id: 'rate',
            width: 120,
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

    TaxRate.grid = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'rate',
        ds: ds,
        cm: cm,
        plugins: [new Axis.grid.Filter()],
        bbar: new Axis.PagingToolbar({
            store: ds
        }),
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler: TaxRate.create
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler: TaxRate.save
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: TaxRate.remove
        }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: function() {
                TaxRate.grid.getStore().reload();
            }
        }]
    });

    new Axis.Panel({
        items: [
            TaxRate.grid
        ]
    });
});
