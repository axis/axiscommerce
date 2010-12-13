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

    var sites = new Ext.FormPanel({
        standardSubmit: true,
        url: Axis.getUrl('index'),
        title: 'Sites'.l(),
        header: false,
        bodyStyle: {
            padding: '20px 10px 13px',
            margin: '0 0 7px 0'
        },
        items:[new Ext.form.ComboBox({
            hideLabel: true,
            editable: false,
            transform: 'site_id',
            triggerAction: 'all',
            anchor: '100%',
            listeners: {
                select: function(combo, record, index) {
                    sites.getForm().submit();
                }
            }
        })]
    });

    var quickSummary = new Ext.Panel({
        title: 'Quick Summary'.l(),
        border: true,
        bodyStyle: {
            padding: '10px',
            margin: '0 0 7px 0'
        },
        items:[
            {contentEl: 'quick-summary-content', border: false}
        ]
    });

    var orderRecord = new Ext.data.Record.create([
        {name: 'id', type: 'int'},
        {name: 'site_id', type: 'int'},
        {name: 'date_purchased_on', type: 'date', dateFormat: 'Y-m-d H:i:s'},
        {name: 'billing_name'},
        {name: 'delivery_name'},
        {name: 'customer_name'},
        {name: 'customer_email'},
        {name: 'customer_id', type: 'int'},
        {name: 'order_status_id', type: 'int'},
        {name: 'order_total_base'}
    ]);

    var dsOrder = new Ext.data.Store({
        url: Axis.getUrl('sales_order/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, orderRecord),
        remoteSort: true,
        sortInfo: {
            field: 'date_purchased_on',
            direction: 'DESC'
        }
    });

    var cmOrder = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: "Customer".l(),
            id: 'customer',
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
        }, {
            header: "Order Total".l(),
            dataIndex: 'order_total_base',
            sortName: 'order_total',
            width: 130
        }, {
            header: "Purchased On".l(),
            dataIndex: 'date_purchased_on',
            renderer: function(value) {
                return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, 'H:i:s');
            },
            width: 130
        }]
    });

    var ordersGrid = new Axis.grid.GridPanel({
        title: 'Orders'.l(),
        autoExpandColumn: 'customer',
        id: 'grid-orders',
        ds: dsOrder,
        cm: cmOrder,
        border: false,
        massAction: false,
        bbar: []
    });

    var dsCustomer = new Ext.data.Store({
        url: Axis.getUrl('customer_index/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, [
            {name: 'id', type: 'int'},
            {name: 'email'},
            {name: 'created_at', type: 'date', dateFormat: 'Y-m-d'},
        ]),
        remoteSort: true,
        sortInfo: {
            field: 'created_at',
            direction: 'DESC'
        }
    });

    var cmCustomer = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: "Customer".l(),
            id: 'customer',
            dataIndex: 'email',
            renderer: function (value, meta, record) {
                return String.format(
                    '<a href="{1}" target="_blank">{0}</a>',
                    value,
                    Axis.getUrl('customer_index/index/customerId/' + record.get('id'))
                );
            },
        }, {
            header: "Date created".l(),
            dataIndex: 'created_at',
            renderer: function(value) {
                return Ext.util.Format.date(value);
            },
            width: 100
        }]
    });

    var customerGrid = new Axis.grid.GridPanel({
        title: 'Customers'.l(),
        autoExpandColumn: 'customer',
        ds: dsCustomer,
        cm: cmCustomer,
        border: false,
        massAction: false,
        bbar:[]
    });

    var dsContact = new Ext.data.Store({
        url: Axis.getUrl('contacts_index/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Ext.data.Record.create([
            {name: 'id', type: 'int'},
            {name: 'email'},
            {name: 'subject'},
            {name: 'message'},
            {name: 'custom_info'},
            {name: 'department_name'},
            {name: 'created_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
            {name: 'department_id', type: 'int'},
            {name: 'datetime'},
            {name: 'message_status'}
        ])),
        remoteSort: true,
        sortInfo: {
            field: 'created_at',
            direction: 'DESC'
        }
    });

    var cmContact = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: "Email".l(),
            dataIndex: 'email',
            width: 150
        },{
            id: 'subject',
            header: "Subject".l(),
            dataIndex: 'subject'
        },{
            header: "Created On".l(),
            dataIndex: 'created_at',
            renderer: function(value) {
                return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, 'H:i:s');
            },
            width: 130
        }]
    });

    var contactGrid = new Axis.grid.GridPanel({
        autoExpandColumn: 'subject',
        title: 'Messages'.l(),
        ds: dsContact,
        cm: cmContact,
        border: false,
        massAction: false,
        bbar: []
    });

    var dsSearch = new Ext.data.Store({
        url: Axis.getUrl('search/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Ext.data.Record.create([
            {name: 'id', type: 'int'},
            {name: 'num_results', type: 'int'},
            {name: 'hit', type: 'int'},
            {name: 'session_id'},
            {name: 'created_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
            {name: 'query'},
            {name: 'customer_email'},
            {name: 'customer_id', type: 'int'}
        ])),
        remoteSort: true,
        sortInfo: {
            field: 'created_at',
            direction: 'DESC'
        }
    });

    var cmSearch = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: "Query".l(),
            id: 'query',
            dataIndex: 'query'
        }, {
            header: "Results".l(),
            width: 50,
            dataIndex: 'num_results'
        }, {
            header: "Hit".l(),
            width: 25,
            dataIndex: 'hit'
        }, {
            header: "Created On".l(),
            width: 130,
            renderer: function(value) {
                return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, 'H:i:s');
            },
            dataIndex: 'created_at',
        }]
    });

    var searchGrid = new Axis.grid.GridPanel({
        autoExpandColumn: 'query',
        title: 'Search Terms'.l(),
        ds: dsSearch,
        cm: cmSearch,
        border: false,
        massAction: false,
        bbar: []
    });

    var dsBestView = new Ext.data.Store({
        url: Axis.getUrl('catalog_index/list-viewed'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Ext.data.Record.create([
            {name: 'id', type: 'int'},
            {name: 'viewed', type: 'int'},
            {name: 'price', type: 'float'},
            {name: 'name'}
        ])),
        remoteSort: true,
        sortInfo: {
            field: 'viewed',
            direction: 'DESC'
        }
    });

    var cmBestView = new Ext.grid.ColumnModel({
        defaults: {
            sortable: false,
            menuDisabled: true
        },
        columns: [{
            header: "Product".l(),
            id: 'product',
            dataIndex: 'name'
        }, {
            header: "Price".l(),
            width: 90,
            dataIndex: 'price'
        }, {
            header: "Viewed".l(),
            width: 70,
            dataIndex: 'viewed'
        }]
    });

    var bestViewGrid = new Axis.grid.GridPanel({
        title: 'Best viewed'.l(),
        autoExpandColumn: 'product',
        ds: dsBestView,
        cm: cmBestView,
        border: false,
        massAction: false,
        bbar: []
    });

    var dsBestseller = new Ext.data.Store({
        url: Axis.getUrl('catalog_index/list-bestseller'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Ext.data.Record.create([
            {name: 'id', type: 'int'},
            {name: 'ordered_qty'},
            {name: 'ordered', type: 'int'},
            {name: 'price', type: 'float'},
            {name: 'name'}
        ])),
        remoteSort: true,
        sortInfo: {
            field: 'ordered',
            direction: 'DESC'
        }
    });

    var cmBestseller = new Ext.grid.ColumnModel({
        defaults: {
            sortable: false,
            menuDisabled: true
        },
        columns: [{
            header: "Product".l(),
            id: 'product',
            dataIndex: 'name'
        }, {
            header: "Price".l(),
            width: 90,
            dataIndex: 'price'
        }, {
            header: "Ordered Qty".l(),
            width: 75,
            dataIndex: 'ordered_qty'
        }, {
            header: "Ordered".l(),
            width: 70,
            dataIndex: 'ordered'
        }]
    });

    var bestsellerGrid = new Axis.grid.GridPanel({
        autoExpandColumn: 'product',
        ds: dsBestseller,
        cm: cmBestseller,
        border: false,
        title: 'Bestsellers'.l(),
        massAction: false,
        bbar: []
    });

    params = {
        'start' : 0,
        'limit' : 10
    };
    if (currentSiteId) {
        params['filter[1][field]'] = 'site_id';
        params['filter[1][value]'] = currentSiteId;
    }
    ordersGrid.on('rowdblclick', function(grid, rowIndex, e) {
        var row = ordersGrid.getStore().getAt(rowIndex);
        window.location = Axis.getUrl('sales_order/index/orderId/' + row.id);
    });
    dsOrder.baseParams = params;
    dsOrder.load();

    customerGrid.on('rowdblclick', function(grid, rowIndex, e) {
        var row = customerGrid.getStore().getAt(rowIndex);
        window.location = Axis.getUrl('customer_index/index/customerId/' + row.id);
    });
    dsCustomer.baseParams = params;
    dsCustomer.load();

    contactGrid.on('rowdblclick', function(grid, rowIndex, e) {
        var row = contactGrid.getStore().getAt(rowIndex);
        window.location = Axis.getUrl('contacts_index/index/mailId/' + row.id);
    });
    dsContact.baseParams = params;
    dsContact.load();

    searchGrid.on('rowdblclick', function(grid, rowIndex, e) {
        var row = searchGrid.getStore().getAt(rowIndex);
        window.location = Axis.getUrl('search/index/searchId/' + row.id);
    });
    dsSearch.baseParams = params;
    dsSearch.load();

    params = {
        'start' : 0,
        'limit' : 10
    }
    if (currentSiteId != 0 ) {
         params['siteId'] = currentSiteId;
    }
    bestViewGrid.on('rowdblclick', function(grid, rowIndex, e) {
        var row = bestViewGrid.getStore().getAt(rowIndex);
        window.location = Axis.getUrl('catalog_index/index/productId/' + row.id);
    });
    dsBestView.baseParams = params;
    dsBestView.load();

    bestsellerGrid.on('rowdblclick', function(grid, rowIndex, e) {
        var row = bestsellerGrid.getStore().getAt(rowIndex);
        window.location = Axis.getUrl('catalog_index/index/productId/' + row.id);
    });
    dsBestseller.baseParams = params;
    dsBestseller.load();

    var orderTabs = new Ext.TabPanel({
        activeTab: 0,
        flex: 1,
        plain: true,
        bodyStyle: {
            margin: '0 0 7px 0'
        },
        items:[
            ordersGrid,
            customerGrid,
            contactGrid
        ]
    });

    var productsTabs = new Ext.TabPanel({
        activeTab: 0,
        flex: 1,
        plain: true,
        items:[
            bestViewGrid,
            bestsellerGrid,
            searchGrid
        ]
    });

    var panelWest = new Ext.Panel({
        border: false,
        collapsible: true,
        collapseMode: 'mini',
        split: true,
        header: false,
        id: 'panel-west',
        region: 'west',
        layout: 'vbox',
        layoutConfig: {
            align : 'stretch'
        },
        width: 500,
        items: [
            sites,
            quickSummary,
            orderTabs,
            productsTabs
        ]
    });

    new Axis.Panel({
        items: [
            panelWest,
            Ext.getCmp('panel-chart')
        ]
    });
});