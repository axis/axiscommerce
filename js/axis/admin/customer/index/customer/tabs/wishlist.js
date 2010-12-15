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

var WishlistGrid = {

    /**
     * @param {Axis.grid.EditorGridPanel} el
     */
    el: null,

    clearData: function() {
        WishlistGrid.delayedLoader.state = '';
        WishlistGrid.el.store.loadData({
            data: []
        });
    },

    loadData: function(data) {
        WishlistGrid.delayedLoader.state = '';
        if (Ext.getCmp('tab-panel-customer').getActiveTab() == WishlistGrid.el) {
            WishlistGrid.delayedLoader.load();
        }
    }
};

Ext.onReady(function() {

    var ds = new Ext.data.Store({
        url: Axis.getUrl('customer_wishlist/list'),
        baseParams: {
            'limit'                     : 25,
            'filter[customer][field]'   : 'customer_id',
            'filter[customer][value]'   : 0
        },
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            idProperty: 'id',
            fields: [
                {name: 'id', type: 'int'},
                {name: 'product_id', type: 'int'},
                {name: 'product_name'},
                {name: 'wish_comment'},
                {name: 'created_on', type: 'date', dateFormat: 'Y-m-d h:i:s'}
            ]
        }),
        remoteSort: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        }
    });

    var expander = new Ext.grid.RowExpander({
        listeners: {
            beforeexpand: function(expander, record, body, rowIndex) {
                if (!this.tpl) {
                    this.tpl = new Ext.Template();
                }

                var html = '<div class="account-wishlist-comment box-expander">';
                html += '<b>' + 'Comment'.l() + ':</b> ';
                html += (comment = record.get('wish_comment')) ? comment : '';
                html += '</div>';

                this.tpl.set(html);
            }
        }
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true,
            menuDisabled: true
        },
        columns: [expander, {
            dataIndex: 'product_id',
            header: 'Id'.l(),
            width: 90
        }, {
            dataIndex: 'product_name',
            id: 'name',
            header: 'Name'.l(),
            width: 100,
            table: 'cpd',
            sortName: 'name',
            filter: {
                name: 'name'
            },
            renderer: function(value) {
                return String.format(
                    '<a href="{1}" target="_blank">{0} </a>',
                    value,
                    Axis.getUrl('catalog_index/index/productId/' + record.get('product_id'))
                );
            }
        }, {
            dataIndex: 'created_on',
            header: 'Date created'.l(),
            renderer: function(v) {
                return Ext.util.Format.date(v) + ' ' + Ext.util.Format.date(v, 'H:i:s');
            },
            width: 130
        }]
    });

    WishlistGrid.el = new Axis.grid.GridPanel({
        autoExpandColumn: 'name',
        border: false,
        cm: cm,
        ds: ds,
        massAction: false,
        plugins: [
            expander,
            new Axis.grid.Filter()
        ],
        sm: new Ext.grid.RowSelectionModel(),
        title: 'Wishlist'.l(),
        bbar: new Axis.PagingToolbar({
            store: ds
        })
    });

    CustomerWindow.addTab(WishlistGrid.el, 60);
    CustomerWindow.dataObjects.push(WishlistGrid);

    WishlistGrid.delayedLoader = new Axis.DelayedLoader({
        el: WishlistGrid.el,
        ds: ds,
        loadFn: function() {
            if (!Customer.id) {
                return;
            }
            WishlistGrid.el.store.baseParams['filter[customer][value]'] = Customer.id;
            WishlistGrid.el.store.load();
        }
    });

});
