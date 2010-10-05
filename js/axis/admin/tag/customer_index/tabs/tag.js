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

var TagGrid = {

    /**
     * @param {Axis.grid.EditorGridPanel} el
     */
    el: null,

    getData: function() {
        return;
    },

    clearData: function() {
        TagGrid.delayedLoader.state = '';
        TagGrid.el.store.loadData({
            data: []
        });
    },

    loadData: function(data) {
        TagGrid.delayedLoader.state = '';
        if (Ext.getCmp('tab-panel-customer').getActiveTab() == TagGrid.el) {
            TagGrid.delayedLoader.load();
        }
        return;
    }
};

Ext.onReady(function() {

    var ds = new Ext.data.Store({
        url: Axis.getUrl('tag_index/list'),
        baseParams: {
            'limit'                      : 100,
            'filter[0][data][type]'      : 'int',
            'filter[0][data][comparison]': 'eq',
            'filter[0][field]'           : 'customer_id',
            'filter[0][data][value]'     : 0
        },
        reader: new Ext.data.JsonReader({
            root: 'data',
            idProperty: 'id',
            fields: [
                {name: 'id', type: 'int'},
                {name: 'tag'},
                {name: 'product_name'}
            ]
        }),
        remoteSort: true
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true,
            menuDisabled: true
        },
        columns: [{
            dataIndex: 'id',
            header: 'Id'.l(),
            width: 60
        }, {
            dataIndex: 'tag',
            id: 'tag',
            header: 'Tag'.l(),
            width: 100
        }, {
            dataIndex: 'product_name',
            header: 'Product Name'.l(),
            width: 250
        }]
    });

    TagGrid.el = new Axis.grid.GridPanel({
        autoExpandColumn: 'tag',
        border: false,
        cm: cm,
        ds: ds,
        massAction: false,
        sm: new Ext.grid.RowSelectionModel(),
        title: 'Tags'.l()
    });

    CustomerWindow.addTab(TagGrid.el, 70);
    CustomerWindow.dataObjects.push(TagGrid);

    TagGrid.delayedLoader = new Axis.DelayedLoader({
        el: TagGrid.el,
        ds: ds,
        loadFn: function() {
            if (!Customer.id) {
                return;
            }
            TagGrid.el.store.load({
                params: {
                    'filter[0][data][value]': Customer.id
                }
            });
        }
    });
});
