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
        WishlistGrid.el.store.loadData([]);
    },

    loadData: function(data) {
        WishlistGrid.el.store.loadData(data.wishlist);
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
                {name: 'product_id', type: 'int'},
                {name: 'product_name'},
                {name: 'wish_comment'},
                {name: 'created_on', type: 'date', dateFormat: 'Y-m-d h:i:s'}
            ]
        })
    });

    var expander = new Ext.grid.RowExpander({
        listeners: {
            beforeexpand: function(expander, record, body, rowIndex) {
                if (!this.tpl) {
                    this.tpl = new Ext.Template();
                }

                var html = '<div class="account-wishlist-comment box-expander">';
                html += (comment = record.get('wish_comment')) ? comment : '';
                html += '</div>';

                this.tpl.set(html);
            }
        }
    });

    var actions = new Ext.ux.grid.RowActions({
        header:'Actions'.l(),
        actions:[{
            iconCls: 'icon-page-edit',
            tooltip: 'Edit'.l()
        }],
        callbacks: {
            'icon-page-edit': function(grid, record, action, row, col) {
                window.open(Axis.getUrl('catalog_index/index/productId/' + record.get('product_id')));
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
            width: 60
        }, {
            dataIndex: 'product_name',
            id: 'name',
            header: 'Name'.l(),
            width: 100
        }, {
            dataIndex: 'created_on',
            header: 'Date created'.l(),
            renderer: function(v) {
                return Ext.util.Format.date(v);
            },
            width: 120
        }, actions]
    });

    WishlistGrid.el = new Axis.grid.GridPanel({
        autoExpandColumn: 'name',
        border: false,
        cm: cm,
        ds: ds,
        massAction: false,
        plugins: [
            expander,
            actions
        ],
        sm: new Ext.grid.RowSelectionModel(),
        title: 'Wishlist'.l()
    });

    CustomerWindow.addTab(WishlistGrid.el, 60);
    CustomerWindow.dataObjects.push(WishlistGrid);

});
