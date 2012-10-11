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

var RelatedGrid = {

    /**
     * @param {Axis.grid.EditorGridPanel} el
     */
    el: null,

    getData: function() {
        return;
    },

    clearData: function() {
        RelatedGrid.delayedLoader.state = '';
        RelatedGrid.el.store.baseParams['id'] = 0;
        RelatedGrid.el.store.loadData({
            data: []
        });
    },

    loadData: function(data) {
        RelatedGrid.delayedLoader.state = '';
        if (Ext.getCmp('tab-panel-product').getActiveTab() == RelatedGrid.el) {
            RelatedGrid.delayedLoader.load();
        }
    },

    getData: function() {
        var modified = RelatedGrid.el.store.getModifiedRecords();

        if (!modified.length) {
            return;
        }

        var data = {};
        for (var i = modified.length - 1; i >= 0; i--) {
            // modified[i]['data'];
            data[modified[i].id] = {
                related_product_id: modified[i].id,
                status            : modified[i].get('status'),
                sort_order        : modified[i].get('sort_order')
            };
        }

        return {
            'related': data
        };
    }
};

Ext.onReady(function() {

    var ds = new Ext.data.Store({
        url: Axis.getUrl('catalog/product/list-related'),
        baseParams: {
            limit: 25
        },
        pruneModifiedRecords: true,
        reader: new Ext.data.JsonReader({
            root: 'data',
            idProperty: 'id',
            totalProperty: 'count',
            fields: [
                {name: 'id', type: 'int'},
                {name: 'name'},
                {name: 'sku'},
                // {name: 'price', type: 'float'},
                {name: 'status', type: 'int'},
                {name: 'sort_order', type: 'int'}
            ]
        }),
        remoteSort: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        }
    });

    var status = new Axis.grid.CheckColumn({
        dataIndex: 'status',
        header: 'Status'.l(),
        width: 100,
        filter: {
            table: '',
            clause: 'having',
            editable: false,
            resetValue: 'reset',
            value: 1,
            store: new Ext.data.ArrayStore({
                data: [[0, 'No'.l()], [1, 'Yes'.l()]],
                fields: ['id', 'name']
            })
        }
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            dataIndex: 'id',
            header: 'Id'.l(),
            width: 90
        }, {
            dataIndex: 'name',
            id: 'name',
            header: 'Title'.l(),
            renderer: Axis.escape,
            table: 'cpd', // filters
            width: 100
        }, {
            dataIndex: 'sku',
            header: 'SKU'.l(),
            width: 160
        }, status, {
            align: 'right',
            dataIndex: 'sort_order',
            header: 'Sort Order'.l(),
            table: 'cpr', // filters
            width: 80,
            editor: new Ext.form.NumberField({
                allowBlank: true,
                allowNegative: false,
                maxValue: 255
            })
        }]
    });

    RelatedGrid.el = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'name',
        border: false,
        cm: cm,
        ds: ds,
        massAction: false,
        plugins: [
            status,
            new Axis.grid.Filter()
        ],
        sm: new Ext.grid.RowSelectionModel(),
        title: 'Related Products'.l(),
        bbar: new Axis.PagingToolbar({
            store: ds
        })
    });

    ProductWindow.addTab(RelatedGrid.el, 55);
    ProductWindow.dataObjects.push(RelatedGrid);

    RelatedGrid.delayedLoader = new Axis.DelayedLoader({
        el: RelatedGrid.el,
        ds: ds,
        loadFn: function() {
            RelatedGrid.el.store.baseParams['id'] = Product.id;
            RelatedGrid.el.store.reload();
        }
    });
});
