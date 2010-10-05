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

Ext.onReady(function (){
    Ext.QuickTips.init();
    var expander = new  Ext.grid.RowExpander({
         tpl : new Ext.Template(
             '<p><b>Comment:</b> {wish_comment}</p>'
         )
    });

    var filters = new Ext.ux.grid.GridFilters({
        filters: [
            {type: 'numeric', dataIndex: 'id'},
            {type: 'string', dataIndex: 'customer_email'},
            {type: 'string', dataIndex: 'product_name'},
            {type: 'string', dataIndex: 'wish_comment'},
            {type: 'date',   dataIndex: 'created_on', dateFormat: 'Y-m-d'}
        ]
    });

    var storeWishlist = new Ext.data.GroupingStore({
        url: Axis.getUrl('customer_wishlist/list'),
         reader: new Ext.data.JsonReader({
                  root : 'wishlist',
                  totalProperty: 'count',
                  id: 'id'
             },
             ['id', 'product_id', 'product_name', 'created_on', 'wish_comment', 'customer_email', 'customer_id']
         ),
         sortInfo: {field: 'product_id', direction: "ASC"},
         remoteSort: true
     });

     function renderCustomer(value, meta, record) {
        meta.attr = 'ext:qtip="Open in new window ' + value + '"';
        var customerAction =  Axis.getUrl('customer_index/index/customerId/.customerId.');
        return String.format(
            '<a href="{1}" class="grid-link-icon user" target="_blank" >{0}</a>',
            value, customerAction.replace(/\.customerId\./, record.data.customer_id));
     }

     function renderProduct(value, meta, record) {
        meta.attr = 'ext:qtip="Open in new window ' + value + '"';
        var productAction =  Axis.getUrl('catalog_index/index/productId/.productId.');
        return String.format(
            '<a href="{1}" class="grid-link-icon product" target="_blank">{0} </a>',
            value, productAction.replace(/\.productId\./, record.data.product_id));
     }

     var columnsWishlist = new Ext.grid.ColumnModel([
         expander, {
             header: "Id".l(),
             width: 30,
             sortable: true,
             dataIndex: 'id',
             groupable:false
         }, {
             header: "Product Name".l(),
             id:'product_name',
             width: 145,
             sortable: true,
             dataIndex: 'product_name',
             renderer: renderProduct
         }, {
             header: "Customer".l(),
             width: 170,
             sortable: true,
             dataIndex: 'customer_email',
             renderer: renderCustomer
         }, {
             header: "Comment".l(),
             width: 170,
             dataIndex: 'wish_comment',
             groupable:false
         }, {
             header: "Created On".l(),
             width: 145,
             sortable: true,
             dataIndex: 'created_on',
             groupable:false
         }
     ]);

     gridWishlist = new Axis.grid.GridPanel({
        ds: storeWishlist,
        cm: columnsWishlist,
        sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
        view: new Ext.grid.GroupingView({
            forceFit:true,
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
            store: storeWishlist
        }),
        plugins:[
            filters,
            expander,
            new Ext.ux.grid.Search({
                 mode: 'local',
                 iconCls: false,
                 dateFormat: 'Y-m-d',
                 width: 150,
                 minLength: 2
            })
        ]
    });

    new Axis.Panel({
        items: [
            gridWishlist
        ]
    });

    function setWishlist(id) {
        var store = gridWishlist.getStore();
        store.lastOptions = {params:{start:0, limit:25}};
        gridWishlist.filters.filters.get('id').setValue({'eq': id});
    }

    if (typeof(wishlistId) !== "undefined") {
        setWishlist(wishlistId);
    } else {
        storeWishlist.load({
            params: {
                start: 0,
                limit: 25
            }
        });
    }
});



