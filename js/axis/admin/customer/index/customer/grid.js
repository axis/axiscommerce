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

var CustomerGrid = {

    /**
     * @property {Axis.grid.EditorGridPanel} el
     */
    el: null,

    /**
     * @property {integer} pageSize
     */
    pageSize: 25,

    /**
     * @property {Axis.data.Record} record
     */
    record: null,

    add: function() {
        Customer.add();
    },

    edit: function(record) {
        var customer = record || CustomerGrid.el.selModel.getSelected();
        Customer.load(customer.get('id'));
    },

    reload: function() {
        CustomerGrid.el.store.reload();
    },

    /**
     * @param {Array} records [Ext.data.Record]
     */
    remove: function(records) {
        var selectedItems = records || CustomerGrid.el.selModel.getSelections();

        if (!selectedItems.length || !confirm('Are you sure?'.l())) {
            return;
        }

        var data = {};
        for (var i = 0; i < selectedItems.length; i++) {
            data[i] = selectedItems[i].id;
        }

        Ext.Ajax.request({
            url: Axis.getUrl('customer_index/delete'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                CustomerGrid.reload();
            }
        });
    },

    save: function() {
        var modified = CustomerGrid.el.store.getModifiedRecords();
        if (!modified.length) {
            return false;
        }

        var data = {};
        for (var i = 0, n = modified.length; i < n; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }

        Ext.Ajax.request({
            url: Axis.getUrl('customer_index/batch-save/'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                CustomerGrid.el.store.commitChanges();
                CustomerGrid.reload();
            }
        })
    }
};

Ext.onReady(function() {

    CustomerGrid.record = Ext.data.Record.create([
        {name: 'id', type: 'int'},
        {name: 'email'},
        {name: 'firstname'},
        {name: 'lastname'},
        {name: 'site_id', type: 'int'},
        {name: 'group_id', type: 'int'},
        {name: 'created_at', type: 'date', dateFormat: 'Y-m-d'},
        {name: 'is_active', type: 'int'}
    ]);

    var ds = new Ext.data.Store({
        autoLoad: true,
        baseParams: {
            limit: CustomerGrid.pageSize
        },
        reader: new Ext.data.JsonReader({
            idProperty: 'id',
            root: 'data',
            totalProperty: 'count'
        }, CustomerGrid.record),
        remoteSort: true,
        url: Axis.getUrl('account/customer/list')
    });

    var actions = new Ext.ux.grid.RowActions({
        actions:[{
            iconCls: 'icon-page-edit',
            tooltip: 'Edit'.l()
        }, {
            iconCls: 'icon-page-delete',
            tooltip: 'Delete'.l()
        }],
        callbacks: {
            'icon-page-edit': function(grid, record, action, row, col) {
                CustomerGrid.edit(record);
            },
            'icon-page-delete': function(grid, record, action, row, col) {
                CustomerGrid.remove([record]);
            }
        }
    });

    var status = new Axis.grid.CheckColumn({
        dataIndex: 'is_active',
        header: 'Status'.l(),
        width: 100,
        filter: {
            editable: false,
            resetValue: 'reset',
            store: new Ext.data.ArrayStore({
                data: [[0, 'Disabled'.l()], [1, 'Enabled'.l()]],
                fields: ['id', 'name']
            })
        }
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            dataIndex: 'id',
            header: 'Id'.l(),
            width: 90
        }, {
            dataIndex: 'firstname',
            header: 'Firstname'.l(),
            editor: new Ext.form.TextField({
                allowBlank: false
            }),
            width: 180
        }, {
            dataIndex: 'lastname',
            header: 'Lastname'.l(),
            editor: new Ext.form.TextField({
                allowBlank: false
            }),
            width: 180
        }, {
            dataIndex: 'email',
            header: 'Email'.l(),
            id: 'email',
            editor: new Ext.form.TextField({
                allowBlank: false,
                vtype: 'email'
            })
        }, {
            dataIndex: 'group_id',
            editor: Customer.groupCombo,
            header: 'Group'.l(),
            renderer: function(value) {
                var store = Ext.StoreMgr.lookup('store-customer-group');
                if (store && (record = store.getById(value))) {
                    return record.get('name');
                }
                return value;
            },
            width: 120,
            filter: {
                editable: false,
                resetValue: 'reset',
                store: new Ext.data.ArrayStore({
                    data: customerGroups,
                    fields: ['id', 'name']
                })
            }
        }, {
            dataIndex: 'site_id',
            editor: Customer.siteCombo,
            header: 'Site'.l(),
            renderer: function(value) {
                var store = Ext.StoreMgr.lookup('store-site-id');
                if (store && (record = store.getById(value))) {
                    return record.get('name');
                }
                return value;
            },
            width: 120,
            filter: {
                editable: false,
                resetValue: 'reset',
                store: new Ext.data.ArrayStore({
                    data: sites,
                    fields: ['id', 'name']
                })
            }
        }, {
            dataIndex: 'created_at',
            header: 'Date created'.l(),
            renderer: function(v) {
                return Ext.util.Format.date(v);
            },
            width: 130
        },
        status,
        actions]
    });

    CustomerGrid.el = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'email',
        ds: ds,
        cm: cm,
        plugins: [
            actions,
            status,
            new Axis.grid.Filter()
        ],
        listeners: {
            'rowdblclick': function(grid, index, e) {
                CustomerGrid.edit(CustomerGrid.el.store.getAt(index));
            }
        },
        bbar: new Axis.PagingToolbar({
            pageSize: CustomerGrid.pageSize,
            store: ds
        }),
        tbar: [{
            handler: CustomerGrid.add,
            icon: Axis.skinUrl + '/images/icons/add.png',
            text: 'Add'.l()
        }, {
            handler: CustomerGrid.save,
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            text: 'Save'.l()
        }, {
            handler: function() {
                CustomerGrid.remove();
            },
            icon: Axis.skinUrl + '/images/icons/delete.png',
            text: 'Delete'.l()
        }, '->', {
            handler: CustomerGrid.reload,
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            text: 'Reload'.l()
        }]
    });
});
