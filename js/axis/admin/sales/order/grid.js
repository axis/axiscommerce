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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

Ext.onReady(function() {

    Ext.QuickTips.init();

    var ds = new Ext.data.Store({
        autoLoad: true,
        baseParams: {
            limit: 25
        },
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Order.record),
        remoteSort: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        },
        url: Axis.getUrl('sales_order/list')
    });

    var actions = new Ext.ux.grid.RowActions({
        actions: [{
            iconCls: 'icon-page-edit',
            tooltip: 'Edit'.l()
        }, {
            iconCls: 'icon-page-delete',
            tooltip: 'Delete'.l()
        }],
        callbacks: {
            'icon-page-edit': function(grid, record, action, row, col) {
                Order.load(record.id);
            },
            'icon-page-delete': function(grid, record, action, row, col) {
                var orderIds = {};
                orderIds[0] = record.id;
                Order.remove(orderIds);
            }
        }
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: "Id".l(),
            dataIndex: 'id',
            width: 90
        }, {
            header: "Number".l(),
            dataIndex: 'number',
            width: 110,
            filter: {
                operator: 'LIKE'
            }
        }, {
            header: "Customer".l(),
            id: 'customer_name',
            dataIndex: 'customer_name',
            renderer: function (value, meta, record) {
                var customerId = record.get('customer_id');
                if (!customerId) {
                    return 'Guest'.l();
                }
                return String.format(
                    '<a href="{1}" target="_blank">{0}</a>',
                    value,
                    Axis.getUrl('customer_index/index/customerId/' + customerId)
                );
            },
            filter: {
                operator: 'LIKE'
            }
        }, {
            header: "Email".l(),
            dataIndex: 'customer_email',
            renderer: function (value, meta, record) {
                var customerId = record.get('customer_id');
                if (!customerId) {
                    return value;
                }
                return String.format(
                    '<a href="{1}" target="_blank">{0}</a>',
                    value,
                    Axis.getUrl('customer_index/index/customerId/' + customerId)
                );
            },
            width: 210,
            table: 'ac',
            sortName: 'email',
            filter: {
                name: 'email',
                operator: 'LIKE'
            }
        }, {
            align: 'right',
            header: "Total Base",
            dataIndex: 'order_total_base',
            width: 150,
            sortName: 'order_total',
            filter: {
                name: 'order_total',
                xtype: 'numberfield'
            }
        }, {
            align: 'right',
            header: "Total Purchased",
            dataIndex: 'order_total_customer',
            width: 150,
            table: '',
            filter: {
                xtype: 'numberfield'
            }
        }, {
            header: "Site".l(),
            dataIndex: 'site_id',
            renderer: function(id) {
                if (typeof(sites[id]) == undefined) {
                    return 'Undefined'.l();
                }
                return sites[id];
            },
            width: 130,
            filter: {
                store: new Ext.data.ArrayStore({
                    data: statusSites,
                    fields: ['id', 'name']
                })
            }
        }, {
            header: "Date".l(),
            dataIndex: 'date_purchased_on',
            width: 135,
            renderer: function (value) {
                return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, 'H:i:s');
            }
        }, {
            header: "Status".l(),
            dataIndex: 'order_status_id',
            width: 130,
            renderer: function (statusId) {
                for (var i in orderStatuses) {
                    if (orderStatuses[i]['status_id'] == statusId) {
                        return orderStatuses[i]['status_name'];
                    }
                }
                return 'unknown';
            },
            filter: {
                store: new Ext.data.ArrayStore({
                    data: statusOrder,
                    fields: ['id', 'name']
                }),
                resetValue: 'reset'
            }
        }, actions]
    });

    var grid = new Axis.grid.GridPanel({
        ds: ds,
        cm: cm,
        autoExpandColumn: 'customer_name',
        id: 'grid-order',
        bbar: new Axis.PagingToolbar({
            store: ds
        }),
        plugins:[
            actions,
            new Axis.grid.Filter()
        ],
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler : function(){
                Order.add();
            }
        },/*{
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            cls: 'x-btn-text-icon',
            handler : batchSave
        },*/{
            text: 'Edit'.l(),
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            handler: function() {
                var selected = Ext.getCmp('grid-order')
                    .getSelectionModel().getSelected();
                if (!selected) {
                    return;
                }
                Order.load(selected.id);
            }
        }, {
            text: 'Print'.l(),
            icon: Axis.skinUrl + '/images/icons/printer_add.png',
            menu: {
                items: [{
                    text: 'Print Invoces'.l(),
                    handler: function(menuItem, cheked) {
                        if (Order.beforePrint()) {
                            $('#print-invoice').val(true);
                            $('#print-form').submit();
                        }
                    }
                }, {
                    text: 'Print Packingslips'.l(),
                    handler: function(menuItem, cheked) {
                        if (Order.beforePrint()) {
                            $('#print-packingslip').val(true);
                            $('#print-form').submit();
                        }
                    }
                }, {
                    text: 'Print Invoces & Packingslips'.l(),
                    handler: function(menuItem, cheked) {
                        if (Order.beforePrint()) {
                            $('#print-invoice').val(true);
                            $('#print-packingslip').val(true);
                            $('#print-form').submit();
                        }
                    }
                }, {
                    text: 'Print Label Billing'.l(),
                    handler: function(menuItem, cheked) {
                        if (Order.beforePrint()) {
                            $('#print-label').val(true);
                            $('#print-form').submit();
                        }
                    }
                }, {
                    text: 'Print Label Shipping'.l(),
                    handler: function(menuItem, cheked) {
                        if (Order.beforePrint()) {
                            $('#print-label').val(true);
                            $('#print-label-address-type').val('shipping');
                            $('#print-form').submit();
                        }
                    }
                }]
             }
         }, {
            text: 'Pdf'.l(),
            icon: Axis.skinUrl + '/images/icons/page_white_acrobat.png',
            menu: {
                items: [{
                    text: 'Print Invoces'.l(),
                    handler: function(menuItem, cheked) {
                        if (Order.beforePrint('pdf')) {
                            $('#print-invoice').val(true);
                            $('#print-form').submit();
                        }
                    }
                }, {
                    text: 'Print Packingslips'.l(),
                    handler: function(menuItem, cheked) {
                        if (Order.beforePrint('pdf')) {
                            $('#print-packingslip').val(true);
                            $('#print-form').submit();
                        }
                    }
                }, {
                    text: 'Print Invoces & Packingslips'.l(),
                    handler: function(menuItem, cheked) {
                        if (Order.beforePrint('pdf')) {
                            $('#print-invoice').val(true);
                            $('#print-packingslip').val(true);
                            $('#print-form').submit();
                        }
                    }
                }, {
                    text: 'Print Label Billing'.l(),
                    handler: function(menuItem, cheked) {
                        if (Order.beforePrint('pdf')) {
                            $('#print-label').val(true);
                            $('#print-form').submit();
                        }
                    }
                }, {
                    text: 'Print Label Shipping'.l(),
                    handler: function(menuItem, cheked) {
                        if (Order.beforePrint('pdf')) {
                            $('#print-label').val(true);
                            $('#print-label-address-type').val('shipping');
                            $('#print-form').submit();
                        }
                    }
                }]
             }
         }, '-', {
                text: 'Delete'.l(),
                icon: Axis.skinUrl + '/images/icons/delete.png',
                handler : function(){
                    var selectedItems = Ext.getCmp('grid-order')
                        .getSelectionModel().selections.items;

                    if (!selectedItems.length) {
                        return false;
                    }
                    var orderIds = {};
                    for (var i = 0; i < selectedItems.length; i++) {
                        if (!selectedItems[i]['data']['id']) {
                            continue;
                        }
                        orderIds[i] = selectedItems[i]['data']['id'];
                    }
                    Order.remove(orderIds);
                }
            },'->', {
                text: 'Reload'.l(),
                handler: function (){
                    Ext.getCmp('grid-order').getStore().reload();
                },
                icon: Axis.skinUrl + '/images/icons/refresh.png'
            }
        ]
    });

    grid.on('rowdblclick', function(grid, index) {
        Order.load(grid.getStore().getAt(index).id);
    });

    if (typeof(orderId) !== "undefined") {
        Order.load(orderId);
    }
});