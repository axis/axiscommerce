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

var AddressGrid = {

    /**
     * @param {Axis.grid.EditorGridPanel} el
     */
    el: null,

    /**
     * @param {Object} address
     */
    add: function(address) {
        address.remove = 0;
        address.default_billing = address.default_billing ? 1 : 0;
        address.default_shipping = address.default_shipping ? 1 : 0;
        var record;
        if (!(record = AddressGrid.el.store.getById(address.id))) {
            var record = new AddressGrid.el.store.recordType(address);
            record.markDirty();
            AddressGrid.el.store.modified.push(record);
            AddressGrid.el.store.add(record);
        } else {
            record.markDirty();
            for (key in address) {
                record.set(key, address[key]);
            }
        }
    },

    /**
     * @param {Ext.data.Record} record
     */
    edit: function(record) {
        AddressGrid.getAddressWindow().form.getForm().clear();
        AddressGrid.getAddressWindow().form.getForm().setValues(record.data);
        AddressGrid.getAddressWindow().window.setTitle(
            record.data.firstname
            + ' '
            + record.data.lastname
            + ': '
            + record.data.phone
        );
        AddressGrid.getAddressWindow().show();
    },

    getAddressWindow: function() {
        if (!Customer.addressWindow) {
            Customer.addressWindow = new Axis.AddressWindow();
            Customer.addressWindow.on('okpress', AddressGrid.add);
        }
        return Customer.addressWindow;
    },

    clearData: function() {
        AddressGrid.el.store.loadData([]);
    },

    loadData: function(data) {
        AddressGrid.el.store.loadData(data.address);
    },

    getData: function() {
        var modified = AddressGrid.el.store.getModifiedRecords();

        if (!modified.length) {
            return;
        }

        var data = {};
        for (var i = modified.length - 1; i >= 0; i--) {
            data[modified[i]['id']] = modified[i]['data'];
        }

        return {
            'address': data
        };
    }

};

Ext.onReady(function() {

    var ds = new Ext.data.Store({
        mode: 'local',
        pruneModifiedRecords: true,
        reader: new Ext.data.JsonReader({
            idProperty: 'id',
            fields: [
                {name: 'id', type: 'int'},
                {name: 'firstname'},
                {name: 'lastname'},
                {name: 'company'},
                {name: 'phone'},
                {name: 'fax'},
                {name: 'street_address'},
                {name: 'city'},
                {name: 'country_id', type:'int', mapping: 'country.id'},
                {name: 'postcode'},
                {name: 'zone_id', mapping: 'zone.id'},
                {name: 'default_billing', type:'int'},
                {name: 'default_shipping', type:'int'},
                {name: 'remove', type:'int'},
                {name: 'country_name', mapping: 'country.name'}, // used in row expander
                {name: 'zone_name', mapping: 'zone.name'}
            ]
        })
    });

    var expander = new Ext.grid.RowExpander({
        listeners: {
            beforeexpand: function(expander, record, body, rowIndex) {
                if (!this.tpl) {
                    this.tpl = new Ext.Template();
                }

                var data = [
                    {title: 'Firstname'.l(), dataIndex: 'firstname'},
                    {title: 'Lastname'.l(), dataIndex: 'lastname'},
                    {title: 'Company'.l(),  dataIndex: 'company'},
                    {title: 'Phone'.l(),    dataIndex: 'phone'},
                    {title: 'Fax'.l(),      dataIndex: 'fax'},
                    {title: 'Street'.l(),   dataIndex: 'street_address'},
                    {title: 'City'.l(),     dataIndex: 'city'},
                    {title: 'Country'.l(),  dataIndex: 'country_name'},
                    {title: 'Zip'.l(),      dataIndex: 'postcode'},
                    {title: 'Zone'.l(),     dataIndex: 'zone_name'}
                ];

                var html = '<div class="account-address box-expander">';
                Ext.each(data, function(row) {
                    html += String.format(
                        '<p class="account-address-item expander-row"><label>{0}</label><span>{1}</span></p>',
                        row.title,
                        record.get(row.dataIndex)
                    );
                }, this);
                html += '</div>';
                this.tpl.set(html);
            }
        }
    });

//    var defaultBilling = new Axis.grid.RadioColumn({
//        dataIndex: 'default_billing',
//        header: 'Billing'.l(),
//        width: 60
//    });
//
//    var defaultShipping = new Axis.grid.RadioColumn({
//        dataIndex: 'default_shipping',
//        header: 'Shipping'.l(),
//        width: 60
//    });

    var remove = new Axis.grid.CheckColumn({
        dataIndex: 'remove',
        header: 'Delete'.l(),
        width: 60
    });

    var actions = new Ext.ux.grid.RowActions({
        header:'Actions'.l(),
        actions:[{
            iconCls: 'icon-page-edit',
            tooltip: 'Edit'.l()
        }],
        callbacks: {
            'icon-page-edit': function(grid, record, action, row, col) {
                AddressGrid.edit(record);
            }
        }
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true,
            menuDisabled: true
        },
        columns: [expander, {
            dataIndex: 'firstname',
            header: 'Firstname'.l(),
            editor: new Ext.form.TextField({
                allowBlank: false
            }),
            width: 100
        }, {
            dataIndex: 'lastname',
            header: 'Lastname'.l(),
            editor: new Ext.form.TextField({
                allowBlank: false
            }),
            width: 100
        }, {
            dataIndex: 'phone',
            id: 'phone',
            header: 'Phone'.l(),
            editor: new Ext.form.TextField({
                allowBlank: false
            })
        }, {
            dataIndex: 'city',
            header: 'City'.l(),
            editor: new Ext.form.TextField({
                allowBlank: false
            }),
            width: 110
        }, {
            dataIndex: 'postcode',
            header: 'Zip'.l(),
            editor: new Ext.form.TextField({
                allowBlank: false
            }),
            width: 90
        },
        //defaultBilling,
        //defaultShipping,
        remove,
        actions]
    });

    AddressGrid.el = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'phone',
        border: false,
        cm: cm,
        ds: ds,
        massAction: false,
        plugins: [
            remove,
            expander,
            //defaultBilling,
            //defaultShipping,
            actions
        ],
        sm: new Ext.grid.RowSelectionModel(),
        title: 'Address'.l(),
        trackMouseOver: false,
        tbar: [{
            icon: Axis.skinUrl + '/images/icons/add.png',
            text: 'Add'.l(),
            handler: function() {
                AddressGrid.getAddressWindow().form.getForm().clear();
                AddressGrid.getAddressWindow().show();
            }
        }]
    });

//    AddressGrid.el.on('rowdblclick', function(grid, index, e) {
//        e.stopEvent();
//        AddressGrid.edit(grid.getStore().getAt(index));
//    });

    CustomerWindow.addTab(AddressGrid.el, 30);
    CustomerWindow.dataObjects.push(AddressGrid);

});
