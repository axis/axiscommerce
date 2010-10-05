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

var ShoppingCartGrid = {

    /**
     * @param {Axis.grid.EditorGridPanel} el
     */
    el: null,

    clearData: function() {
        ShoppingCartGrid.el.store.loadData([]);
    },

    loadData: function(data) {
        ShoppingCartGrid.el.store.loadData(data.shopping_cart);
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
                {name: 'shopping_cart_id', type: 'int'},
                {name: 'product_id', type: 'int'},
                {name: 'sku'},
                {name: 'name'},
                {name: 'attributes'},
                {name: 'final_price', type: 'float'},
                {name: 'quantity', type: 'float'}
            ]
        })
    });

    var expander = new Ext.grid.RowExpander({
        listeners: {
            beforeexpand: function(expander, record, body, rowIndex) {
                if (!this.tpl) {
                    this.tpl = new Ext.Template();
                }

                var html = '<div class="product-attributes box-expander">';
                Ext.each(record.get('attributes'), function(row) {
                    html += String.format(
                        '<p class="product-attribute expander-row"><label>{0}</label><span>{1}</span></p>',
                        row.name,
                        row.value
                    );
                }, this);
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
            dataIndex: 'name',
            header: 'Name'.l(),
            width: 100
        }, {
            dataIndex: 'sku',
            header: 'SKU'.l(),
            width: 120
        }, {
            dataIndex: 'quantity',
            header: 'Q-ty'.l(),
            width: 120
        }, {
            dataIndex: 'final_price',
            header: 'Price'.l(),
            width: 120
        }, actions]
    });

    ShoppingCartGrid.el = new Axis.grid.GridPanel({
        border: false,
        cm: cm,
        ds: ds,
        massAction: false,
        plugins: [
            expander,
            actions
        ],
        viewConfig: {
            emptyText: 'No records found'.l(),
            forceFit: true
        },
        sm: new Ext.grid.RowSelectionModel(),
        title: 'Shopping Сart'.l()
    });

    CustomerWindow.addTab(ShoppingCartGrid.el, 50);
    CustomerWindow.dataObjects.push(ShoppingCartGrid);

});
