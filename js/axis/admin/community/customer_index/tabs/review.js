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

var ReviewGrid = {

    /**
     * @param {Axis.grid.EditorGridPanel} el
     */
    el: null,

    getData: function() {
        return;
    },

    clearData: function() {
        ReviewGrid.delayedLoader.state = '';
        ReviewGrid.el.store.loadData({
            data: []
        });
    },

    loadData: function(data) {
        ReviewGrid.delayedLoader.state = '';
        if (Ext.getCmp('tab-panel-customer').getActiveTab() == ReviewGrid.el) {
            ReviewGrid.delayedLoader.load();
        }
        return;
    }
};

Ext.onReady(function() {

    var ds = new Ext.data.Store({
        url: Axis.getUrl('community/review/list'),
        baseParams: {
            'limit': 25,
            'filter[customer][field]': 'cr.customer_id',
            'filter[customer][value]': 0
        },
        reader: new Ext.data.JsonReader({
            root: 'data',
            idProperty: 'id',
            totalProperty: 'count',
            fields: [
                {name: 'id', type: 'int'},
                {name: 'title'},
                {name: 'product_name'},
                {name: 'summary'},
                {name: 'pros'},
                {name: 'cons'}
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

                var reviewData = [
                    {title: 'Pros'.l(),     dataIndex: 'pros'},
                    {title: 'Cons'.l(),     dataIndex: 'cons'},
                    {title: 'Summary'.l(),  dataIndex: 'summary'}
                ];

                var html = '<div class="account-review box-expander">';
                Ext.each(reviewData, function(row) {
                    html += String.format(
                        '<div class="review-item expander-row"><label>{0}:</label><div>{1}</div></div>',
                        row.title,
                        (value = record.get(row.dataIndex)) ? Axis.escape(value) : ''
                    );
                }, this);
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
            dataIndex: 'id',
            header: 'Id'.l(),
            width: 90
        }, {
            dataIndex: 'title',
            id: 'title',
            header: 'Title'.l(),
            renderer: Axis.escape
        }, {
            dataIndex: 'product_name',
            header: 'Product Name'.l(),
            width: 250,
            table: 'cpd',
            sortName: 'name',
            filter: {
                name: 'name'
            }
        }]
    });

    ReviewGrid.el = new Axis.grid.GridPanel({
        autoExpandColumn: 'title',
        cm: cm,
        ds: ds,
        massAction: false,
        plugins: [
            expander,
            new Axis.grid.Filter()
        ],
        sm: new Ext.grid.RowSelectionModel(),
        title: 'Reviews'.l(),
        bbar: new Axis.PagingToolbar({
            store: ds
        })
    });

    CustomerWindow.addTab(ReviewGrid.el, 80);
    CustomerWindow.dataObjects.push(ReviewGrid);

    ReviewGrid.delayedLoader = new Axis.DelayedLoader({
        el: ReviewGrid.el,
        ds: ds,
        loadFn: function() {
            if (!Customer.id) {
                return;
            }
            ReviewGrid.el.store.baseParams['filter[customer][value]'] = Customer.id;
            ReviewGrid.el.store.load();
        }
    });
});
