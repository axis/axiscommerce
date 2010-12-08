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

var action = {
    'product'  : Axis.getUrl('catalog_index/index/siteId/.siteId./productId/.id.'),
    'review'   : Axis.getUrl('review_index/index/reviewId/.id.'),
    'order'    : Axis.getUrl('sales_order/index/orderId/.id.'),
    'wishlist' : Axis.getUrl('customer_wishlist/index/wishlistId/.id.'),
    'customer' : Axis.getUrl('customer_index/index/customerId/.id.'),
    'contact'  : Axis.getUrl('contacts_index/index/mailId/.id.'), //@todo it`s no work
    'search'   : Axis.getUrl('search/index/searchId/.id.')

};
var params;
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
        {name: 'id'},
        {name: 'site_id'},
        {name: 'date_purchased_on'},
        {name: 'billing_name'},
        {name: 'delivery_name'},
        {name: 'customer_email'},
        {name: 'customer_id'},
        {name: 'order_status_id'},
        {name: 'order_total'}
    ]);

    var dsOrder = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            method: 'post',
            url: Axis.getUrl('sales_order/list')
        }),
        baseParams: params,
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, orderRecord),

        remoteSort: true,
        pruneModifiedRecords: true
    });

    var dsOrderCustomer = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            method: 'post',
            url: Axis.getUrl('sales_order/list')
        }),
        baseParams:params,
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, orderRecord),

        remoteSort: true,
        pruneModifiedRecords: true
    });
    var cmOrder = new Ext.grid.ColumnModel([
        {
            header: "Customer".l(),
            sortable: true,
            id: 'customer',
            dataIndex: 'customer_email',
            renderer:   function (value, meta, record) {
            meta.attr = 'ext:qtip="Open in new window ' + value + '"';
            return String.format(
                    '<a href="{1}" target="_blank">{0}</a>',
                    value, action.customer.replace(/\.id\./, record.data.customer_id));
            }
        },{
            header: "Purchased On".l(),
            dataIndex: 'date_purchased_on',
            width: 130
        },{
            header: "Order Total".l(),
            dataIndex: 'order_total',
            width: 130
        }
    ]);

    var gridOrders = new Ext.grid.EditorGridPanel({
        title: 'Orders'.l(),
        autoExpandColumn: 'customer',
        id: 'grid-orders',
        ds: dsOrder,
        cm: cmOrder,
        border: false,
        viewConfig: {
            emptyText: 'No records found'.l()
        },
        bbar:['->',
            new Ext.Button({
                cls: 'x-btn-text',
                text: 'All orders'.l(),
                handler: function() {
                    document.location.href =
                        Axis.getUrl('sales_order/index/');
                }
            })
        ]
    });

    var ordersCustomerGrid = new Ext.grid.EditorGridPanel({
        title: 'Customers'.l(),
        autoExpandColumn: 'customer',
        ds: dsOrderCustomer,
        cm: cmOrder,
        border: false,
        viewConfig: {
            emptyText: 'No records found'.l()
        },
        bbar:['->',
            new Ext.Button({
                cls: 'x-btn-text',
                text: 'All orders'.l(),
                handler: function() {
                    document.location.href =
                        Axis.getUrl('sales_order/index/');
                }
            })
        ]
    });

    var dsContact = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            method: 'post',
            url: Axis.getUrl('contacts_index/list')
        }),
        baseParams:params,
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Ext.data.Record.create([
            {name: 'id'},
            {name: 'email'},
            {name: 'subject'},
            {name: 'message'},
            {name: 'custom_info'},
            {name: 'department_name'},
            {name: 'created_at'},
            {name: 'department_id'},
            {name: 'datetime'},
            {name: 'message_status'}
        ])),

        remoteSort: true,
        pruneModifiedRecords: true
    });
    var cmContact = new Ext.grid.ColumnModel([
        {
            header: "Email".l(),
            dataIndex: 'email',
            width: 150,
            sortable: true
        },{
            id: 'subject',
            header: "Subject".l(),
            dataIndex: 'subject',
            sortable: true
        },{
            header: "Data & Time".l(),
            dataIndex: 'created_at',
            width: 130,
            sortable: true
        }
    ]);
    var contactGrid = new Ext.grid.EditorGridPanel({
        autoExpandColumn: 'subject',
        title: 'Messages'.l(),
        ds: dsContact,
        cm: cmContact,
        border: false,
        autoScroll: true,
        viewConfig: {
            emptyText: 'No records found'.l()
        },
        bbar: ['->',
            new Ext.Button({
                 cls: 'x-btn-text',
                text: 'All contacts'.l(),
                handler: function() {
                    document.location.href =
                        Axis.getUrl('contacts_index');
                }
            })
        ]
    });

    var dsSearch = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
             method: 'post',
            url: Axis.getUrl('search/list')
        }),
        baseParams: params,
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Ext.data.Record.create([
            {name: 'id'},
            {name: 'num_results'},
            {name: 'hit'},
            {name: 'session_id'},
            {name: 'created_at'},
            {name: 'query'},
            {name: 'customer_email'},
            {name: 'customer_id'}
        ])),

        remoteSort: true,
        pruneModifiedRecords: true
    });
    var cmSearch = new Ext.grid.ColumnModel([
        {
            header: "Query".l(),
            width: 50,
            sortable: true,
            dataIndex: 'query'
        }, {
            header: "Results".l(),
            width: 45,
            sortable: true,
            dataIndex: 'num_results'
        }, {
            header: "Customer".l(),
            width: 165,
            sortable: true,
            dataIndex: 'customer_email',
            renderer: function (value, meta, record) {
                 var link = action.customer.replace(/\.id\./, record.data.customer_id);
                if (value == null) {
                    value = ' Guest';
                    link = '#';
                }
                meta.attr = 'ext:qtip="Open in new window ' + value + '"';
                return String.format(
                        '<a href="{1}" target="_blank">{0}</a>',
                        value, link);
            }
        }, {
            header: "Hit".l(),
            width: 25,
            dataIndex: 'hit',
            sortable: true,
            groupable:false
        }, {
            header: "Created On".l(),
            width: 130,
            sortable: true,
            dataIndex: 'created_at',
            groupable:false
        }

    ]);
    var searchGrid = new Ext.grid.EditorGridPanel({
        ds: dsSearch,
        cm: cmSearch,
        border: false,
        viewConfig: {
            forceFit: true,
            emptyText: 'No records found'.l()
        },
        title: 'Search Terms'.l(),
        bbar: new Ext.Toolbar({
            items:['->',
                new Ext.Button({
                    cls: 'x-btn-text',
                    text: 'All searches'.l(),
                    handler: function() {
                        document.location.href =  Axis.getUrl('search');
                    }
                })
            ]
        })
    });

    var dsBestView = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            method: 'post',
            url: Axis.getUrl('catalog_index/list-viewed')
        }),
        baseParams: params,
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Ext.data.Record.create([
            {name: 'id'},
            {name: 'viewed'},
            {name: 'price'},
            {name: 'name'}
        ])),
        remoteSort: true,
        pruneModifiedRecords: true
    });

    var cmBestView = new Ext.grid.ColumnModel([
        {
            header: "Product".l(),
            id: 'product',
            sortable: true,
            dataIndex: 'name'
        }, {
            header: "Price".l(),
            width: 90,
            sortable: true,
            dataIndex: 'price'
        }, {
            header: "Viewed".l(),
            width: 70,
            sortable: true,
            dataIndex: 'viewed'
        }
    ]);

    var bestViewGrid = new Ext.grid.EditorGridPanel({
        title: 'Best viewed'.l(),
        autoExpandColumn: 'product',
        ds: dsBestView,
        cm: cmBestView,
        border: false,
        viewConfig: {
            emptyText: 'No records found'.l()
        },
        bbar: []
    });

    var dsBestseller = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            method: 'post',
            url: Axis.getUrl('catalog_index/list-bestseller')
        }),
        baseParams:params,
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Ext.data.Record.create([
            {name: 'id'},
            {name: 'ordered_qty'},
            {name: 'ordered'},
            {name: 'price'},
            {name: 'name'}
        ])),

        remoteSort: true,
        pruneModifiedRecords: true
    });
    var cmBestseller = new Ext.grid.ColumnModel([
        {
            header: "Product".l(),
            id: 'product',
            sortable: true,
            dataIndex: 'name'

        },{
            header: "Price".l(),
            width: 90,
            sortable: true,
            dataIndex: 'price'
        },{
            header: "Ordered Qty".l(),
            width: 70,
            dataIndex: 'ordered_qty'
        },{
            header: "Ordered".l(),
            width: 70,
            sortable: true,
            dataIndex: 'ordered'
        }

    ]);
    var bestsellerGrid = new Ext.grid.EditorGridPanel({
        ds: dsBestseller,
        cm: cmBestseller,
        autoExpandColumn: 'product',
        border: false,
        title: 'Bestsellers'.l(),
        viewConfig: {
            forceFit:true,
            emptyText: 'No records found'.l()
        },
        bbar: []
    });

    params = {
        'start' : 0,
        'limit' : 5,
        'dir'   : 'DESC',
        'sort'  : 'date_purchased_on'
    };
    if (onlyDate) {
        params['filter[0][data][comparison]'] = 'gt';
        params['filter[0][data][type]']       = 'date';
        params['filter[0][data][value]']      = date;
        params['filter[0][field]']            = 'date_purchased_on';
    }
    if (currentSiteId != 0 )
    {
        params['filter[1][data][type]']       = 'numeric';
        params['filter[1][data][comparison]'] = 'eq';
        params['filter[1][field]']            = 'site_id';
        params['filter[1][data][value]']      = currentSiteId;
    }
    gridOrders.on('rowdblclick', function(grid, rowIndex, e) {
        var row = gridOrders.getStore().getAt(rowIndex);
        window.location = action.order.replace(/\.id\./, row.id);
    });
    dsOrder.baseParams = params;
    dsOrder.load();

    params['filter[2][data][type]']       = 'numeric';
    params['filter[2][data][comparison]'] = 'noteq';
    params['filter[2][field]']            = 'customer_id';
    params['filter[2][data][value]']      = 0;
    ordersCustomerGrid.on('rowdblclick', function(grid, rowIndex, e) {
        var row = ordersCustomerGrid.getStore().getAt(rowIndex);
        window.location = action.order.replace(/\.id\./, row.id);
    });
    dsOrderCustomer.baseParams = params;
    dsOrderCustomer.load();

    delete params['filter[2][data][type]'];
    delete params['filter[2][data][comparison]'];
    delete params['filter[2][field]'];
    delete params['filter[2][data][value]'];
    params['sort'] = 'created_at';
    if (onlyDate) {
        params['filter[0][field]'] = 'created_at';
    }
    contactGrid.on('rowdblclick', function(grid, rowIndex, e) {
        var row = contactGrid.getStore().getAt(rowIndex);
        window.location = action.contact.replace(/\.id\./, row.id);
    });
    dsContact.baseParams = params;
    dsContact.load();
    if (onlyDate) {
        params['filter[0][field]'] = 'created_at';
    }
    searchGrid.on('rowdblclick', function(grid, rowIndex, e) {
        var row = searchGrid.getStore().getAt(rowIndex);
        window.location = action.search.replace(/\.id\./, row.id);
    });
    dsSearch.baseParams = params;
    dsSearch.load();

    params = {
        'start' : 0,
        'limit' : 5,
        'dir'   :  'DESC'
    }
    if (currentSiteId != 0 )
    {
         params['siteId'] = currentSiteId;
    }
    bestViewGrid.on('rowdblclick', function(grid, rowIndex, e) {
        var row = bestViewGrid.getStore().getAt(rowIndex);
        window.location = action.product
            .replace(/\.id\./, row.id)
            .replace(/\.siteId\./,currentSiteId);
    });
    dsBestView.baseParams = params;
    dsBestView.load();

    bestsellerGrid.on('rowdblclick', function(grid, rowIndex, e) {
        var row = bestsellerGrid.getStore().getAt(rowIndex);
        window.location = action.product
            .replace(/\.id\./, row.id)
            .replace(/\.siteId\./,currentSiteId);
    });
    delete params['dir'];
    dsBestseller.baseParams = params;
    dsBestseller.load();

    // basic tabs 1, built from existing content
    var orderTabs = new Ext.TabPanel({
        activeTab: 0,
        flex: 1,
        plain: true,
        bodyStyle: {
            margin: '0 0 7px 0'
        },
        items:[
            gridOrders, ordersCustomerGrid, contactGrid
        ]
    });

    var productsTabs = new Ext.TabPanel({
        activeTab: 0,
        flex: 1,
        plain: true,
        items:[bestViewGrid, bestsellerGrid, searchGrid]
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
        width: 450,
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