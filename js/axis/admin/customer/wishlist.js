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

Ext.onReady(function() {

    Ext.QuickTips.init();

    var expander = new  Ext.grid.RowExpander({
         tpl : new Ext.Template(
             '<p><b>' + 'Comment'.l() + ':</b> {wish_comment}</p>'
         )
    });

    var ds = new Ext.data.GroupingStore({
        autoLoad: true,
        baseParams: {
            limit: 25
        },
        url: Axis.getUrl('customer_wishlist/list'),
        reader: new Ext.data.JsonReader({
            root : 'data',
            totalProperty: 'count',
            id: 'id'
        }, [
            {name: 'id', type: 'int'},
            {name: 'product_id', type: 'int'},
            {name: 'product_name'},
            {name: 'created_on', type: 'date', dateFormat: 'Y-m-d H:i:s'},
            {name: 'wish_comment'},
            {name: 'customer_email'},
            {name: 'customer_id', type: 'int'}
        ]),
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        },
        remoteSort: true
    });

    function renderCustomer(value, meta, record) {
        return String.format(
            '<a href="{1}" target="_blank" >{0}</a>',
            value,
            Axis.getUrl('account/customer/index/customerId/' + record.data.customer_id)
        );
    }

    function renderProduct(value, meta, record) {
        return String.format(
            '<a href="{1}" target="_blank">{0} </a>',
            value,
            Axis.getUrl('catalog_index/index/productId/' + record.data.product_id)
        );
    }

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [expander, {
            header: "Id".l(),
            width: 90,
            dataIndex: 'id',
            groupable:false
        }, {
            header: "Product Name".l(),
            id: 'product_name',
            width: 145,
            dataIndex: 'product_name',
            renderer: renderProduct,
            table: 'cpd',
            sortName: 'name',
            filter: {
                name: 'name'
            }
        }, {
            header: "Customer".l(),
            width: 190,
            dataIndex: 'customer_email',
            renderer: renderCustomer,
            table: 'ac',
            sortName: 'email',
            filter: {
                name: 'email'
            }
        }, {
            header: "Created On".l(),
            width: 145,
            dataIndex: 'created_on',
            renderer: function(value) {
                return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, 'H:i:s');
            },
            groupable:false
        }]
    });

    var grid = new Axis.grid.GridPanel({
        autoExpandColumn: 'product_name',
        ds: ds,
        cm: cm,
        massAction: false,
        sm: new Ext.grid.RowSelectionModel({
            singleSelect:true
        }),
        view: new Ext.grid.GroupingView({
            emptyText: 'No records found'.l(),
            groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
        }),
        tbar: ['->', {
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-icon',
            handler: function() {
                gridWishlist.getStore().reload();
            }
        }],
        bbar: new Axis.PagingToolbar({
            store: ds
        }),
        plugins:[
            expander,
            new Axis.grid.Filter()
        ]
    });

    new Axis.Panel({
        items: [
            grid
        ]
    });
});



