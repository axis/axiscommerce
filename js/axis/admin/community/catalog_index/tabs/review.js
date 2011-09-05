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
        if (Ext.getCmp('tab-panel-product').getActiveTab() == ReviewGrid.el) {
            ReviewGrid.delayedLoader.load();
        }
        return;
    }
};

Ext.onReady(function() {

    var ds = new Ext.data.Store({
        url: Axis.getUrl('community_review/get-list'),
        baseParams: {
            limit: 25
        },
        reader: new Ext.data.JsonReader({
            root: 'data',
            idProperty: 'id',
            totalProperty: 'count',
            fields: [
                {name: 'id', type: 'int'},
                {name: 'title'},
                {name: 'author'},
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
            sortable: true
        },
        columns: [expander, {
            dataIndex: 'id',
            header: 'Id'.l(),
            width: 90
        }, {
            dataIndex: 'title',
            id: 'title',
            header: 'Title'.l(),
            renderer: Axis.escape,
            width: 100
        }, {
            dataIndex: 'author',
            header: 'Author'.l(),
            width: 250
        }]
    });

    ReviewGrid.el = new Axis.grid.GridPanel({
        autoExpandColumn: 'title',
        border: false,
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

    ProductWindow.addTab(ReviewGrid.el, 100);
    ProductWindow.dataObjects.push(ReviewGrid);

    ReviewGrid.delayedLoader = new Axis.DelayedLoader({
        el: ReviewGrid.el,
        ds: ds,
        loadFn: function() {
            if (!Product.id) {
                return;
            }
            ReviewGrid.el.store.baseParams['filter[product][field]'] = 'product_id';
            ReviewGrid.el.store.baseParams['filter[product][value]'] = Product.id;
            ReviewGrid.el.store.reload();
        }
    });
});
