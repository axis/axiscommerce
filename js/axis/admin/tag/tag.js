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


Ext.onReady(function () {

    Ext.QuickTips.init();

    var storeTag = new Ext.data.GroupingStore({
        autoLoad: true,
        baseParams: {
            limit: 25
        },
        reader: new Ext.data.JsonReader({
                idProperty: '', // prevent losing of duplicate tags
                root : 'data',
                totalProperty: 'count'
            }, [
                {name: 'id', type: 'int'},
                {name: 'product_id', type: 'int'},
                {name: 'product_name'},
                {name: 'name'},
                {name: 'customer_email'},
                {name: 'customer_id'},
                {name: 'status'}
            ]
        ),
        remoteSort: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        },
        url: Axis.getUrl('tag/list')
    });

    function renderCustomer(value, meta, record) {
        if (record.data.customer_id) {
            return String.format(
                '<a href="{1}">{0}</a>',
                value,
                Axis.getUrl('account/customer/index/customerId/' + record.data.customer_id)
            );
        } else if (!value) {
            return 'Undefined'.l();
        }
        return value;
    }

    function renderProduct(value, meta, record) {
        return String.format(
            '<a href="{1}" target="_blank">{0}</a>',
            value,
            Axis.getUrl('catalog_index/index/productId/' + record.data.product_id)
        );
    }

    var columnsTag = new Ext.grid.ColumnModel({
        defaults: {
            sortable    : true,
            groupable   : false
        },
        columns: [{
            header      : "Id".l(),
            dataIndex   : 'id',
            width       : 90
        }, {
            header      : "Tag".l(),
            dataIndex   : 'name',
            groupable   : true,
            width       : 170,
            renderer    : Axis.escape,
            filter: {
                operator: 'LIKE'
            }
        }, {
            dataIndex   : 'product_name',
            groupable   : true,
            header      : "Product Name".l(),
            id          :'product_name',
            renderer    : renderProduct,
            width       : 145,
            table       : 'cpd',
            sortName    : 'name',
            filter: {
                name    : 'name',
                operator: 'LIKE'
            }
        }, {
            header      : "Customer".l(),
            sortable    : true,
            dataIndex   : 'customer_email',
            renderer    : renderCustomer,
            width       : 200,
            table       : 'ac',
            sortName    : 'email',
            filter: {
                name    : 'email',
                operator: 'LIKE'
            }
        }, {
            header      : "Status".l(),
            sortable    : true,
            dataIndex   : 'status',
            editor      : new Ext.form.ComboBox({
                triggerAction   : 'all',
                transform       : 'status-combo',
                lazyRender      : true,
                typeAhead       : true,
                forceSelection  : false,
                editable        : false
            }),
            width       : 170,
            renderer    : function(value) {
                var i = 0;
                while (statuses[i]) {
                    if (value == statuses[i][0]) {
                        return statuses[i][1];
                    }
                    i++;
                }
                return value;
            },
            filter      : {
                editable: false,
                store: new Ext.data.ArrayStore({
                    data    : statuses,
                    fields  : ['id', 'name']
                }),
                valueField  : 'id',
                displayField: 'name',
                resetValue  : 'reset',
                idIndex     : 0
            }
        }]
    });

    var changeStatusMenu = new Ext.menu.Menu({
        id: changeStatusMenu,
        items: menuStatus
    });

    var gridTag = new Axis.grid.EditorGridPanel({
        id: 'gridTag',
        autoExpandColumn: 'product_name',
        store: storeTag,
        cm: columnsTag,
        view: new Ext.grid.GroupingView({
            emptyText: 'No records found'.l(),
            groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
        }),
        plugins:[new Axis.grid.Filter()],
        tbar: [{
            text: 'Delete',
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler : remove
        }, {
            text: 'Change Status to'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/menu_action.png',
            tooltip: {text: 'Change status of selected comments', title: 'Change Status'},
            menu: changeStatusMenu
        }, {
            text: 'Save'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler: save
        }, '->', {
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-icon',
            handler: function() {
                gridTag.getStore().reload();
            }
        }],
        bbar: new Axis.PagingToolbar({
            store: storeTag
        })
    });

    new Axis.Panel({
        items: [
            gridTag
        ]
    });
});


function save() {
    var data = {};
    var modified = Ext.getCmp('gridTag').getStore().getModifiedRecords();
    var length = modified.length;
    if (length < 1) return;

    for (var i = 0; i < length; i++) {
        data[modified[i]['id']] = modified[i]['data'];
    }
    Ext.Ajax.request({
        url: Axis.getUrl('tag/batch-save'),
        params: {data: Ext.encode(data)},
        callback: function() {
            Ext.getCmp('gridTag').getStore().commitChanges();
            Ext.getCmp('gridTag').getStore().reload();
        }
    });
}

function remove() {
    var selectedItems = Ext.getCmp('gridTag').getSelectionModel().selections.items;
    if (!selectedItems.length || !confirm('Are you sure?'.l())) {
        return;
    }

    var data = {};

    for (var i = 0; i < selectedItems.length; i++) {
        data[i] = selectedItems[i].get('id');
    }

    Ext.Ajax.request({
        url: Axis.getUrl('tag/remove'),
        params: {data:  Ext.encode(data)},
        callback: function() {
            Ext.getCmp('gridTag').getStore().reload();
        }
    });
}
function setStatus(status) {
    var selected = Ext.getCmp('gridTag').getSelectionModel().getSelections();
    for (var i = 0; i < selected.length; i++) {
        selected[i].set('status', status);
    }
}