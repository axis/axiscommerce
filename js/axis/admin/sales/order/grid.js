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

Ext.onReady(function(){

    Ext.QuickTips.init();

    var filters = new Ext.ux.grid.GridFilters({
        filters: [
            {type: 'numeric', dataIndex: 'id'},
            {type: 'string',  dataIndex: 'number'},
            {type: 'string',  dataIndex: 'customer_name'},
            {type: 'string',  dataIndex: 'customer_email'},
            {type: 'numeric', dataIndex: 'order_total_base'},
            {type: 'numeric', dataIndex: 'order_total'},
            {type: 'numeric', dataIndex: 'customer_id'},
            {type: 'date',    dataIndex: 'date_purchased_on', dateFormat: 'Y-m-d H:i:s'},
            {
                type: 'list',
                dataIndex: 'order_status_id',
                options: statusOrder,
                phpMode: true
            },{
                type: 'list',
                dataIndex: 'site_id',
                options: statusSites,
                phpMode: true
            }
        ]
    });

    var ds = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('sales_order/list')
        }),

        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Order.record),

        remoteSort: true,
        pruneModifiedRecords: true
    });

    var actions = new Ext.ux.grid.RowActions({
        // header:'Actions'.l(),
        actions:[{
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
        columns: [{
            header: "Id".l(),
            dataIndex: 'id',
            width: 90,
            sortable: true
        }, {
            header: "Number".l(),
            dataIndex: 'number',
            width: 110
        }, {
            header: "Site".l(),
            dataIndex: 'site_id',
            width: 130,
            renderer: function(id) {
                if (typeof(sites[id]) == undefined) {
                    return 'Undefined'.l();
                }
                return sites[id];
            }
        }, {
            header: "Customer".l(),
            id: 'customer_name',
            dataIndex: 'customer_name'
        }, {
            header: "Email".l(),
            width: 210,
            sortable: true,
            dataIndex: 'customer_email',
            renderer: 	function (value, meta, record) {

                var customerId = record.data.customer_id;
                if ("0" === customerId || !customerId) {
                    return value;
                }
                meta.attr = 'ext:qtip="Open in new window ' + value + '"';
                var customerAction = Axis.getUrl('customer_index/index/customerId/.customerId.');
                return String.format(
                    '<a href="{1}" class="grid-link-icon user" target="_blank" >{0}</a>',
                    value,
                    customerAction.replace(/\.customerId\./, record.data.customer_id)
                );
           }
        }, {
            header: "Date".l(),
            dataIndex: 'date_purchased_on',
            width: 180,
            renderer: function (value) {
                return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, 'H:i:s');
            }
        }, {
            header: "Total Base",
            dataIndex: 'order_total_base',
            width: 150
        }, {
            header: "Total Purchased",
            dataIndex: 'order_total',
            width: 150
        }, {
            header: "Status".l(),
            dataIndex: 'order_status_id',
            width: 140,
            renderer: function (statusId) {
                for (var i in orderStatuses) {
                    if (orderStatuses[i]['status_id'] == statusId) {
                        return orderStatuses[i]['status_name'];
                    }
                }
                return 'unknown';
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
            filters,
            new Ext.ux.grid.Search({
                mode:'local',
                iconCls:false,
                dateFormat:'Y-m-d',
                width: 150,
                minLength:2
            })
        ],
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
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
            cls: 'x-btn-text-icon',
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
            cls: 'x-btn-text-icon',
            menu : {items: [{
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
                }
            ]}}, '-', {
                text: 'Delete'.l(),
                icon: Axis.skinUrl + '/images/icons/delete.png',
                cls: 'x-btn-text-icon',
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
                iconCls: 'btn-text-icon',
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
    ds.load({params:{start:0, limit:25}});

}, this);