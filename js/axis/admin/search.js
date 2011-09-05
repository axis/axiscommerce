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

    var storeSearch = new Ext.data.GroupingStore({
        autoLoad: true,
        beaseParams: {
            limit: 25
        },
        reader: new Ext.data.JsonReader({
                root : 'data',
                totalProperty: 'count',
                idProperty: 'id'
            }, [
                {name: 'id', type: 'int'},
                {name: 'num_results', type: 'int'},
                {name: 'hit', type: 'int'},
                {name: 'session_id'},
                {name: 'created_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'query'},
                {name: 'customer_email'},
                {name: 'customer_id', type: 'int'}
            ]
        ),
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        },
        remoteSort: true,
        url: Axis.getUrl('search/list')
    });

    function renderCustomer(value, meta, record) {
        if (value == null) {
            return 'Guest'.l();
        }

        return String.format(
            '<a href="{1}" target="_blank">{0}</a>',
            value,
            Axis.getUrl('account/customer/index/customerId/' + record.data.customer_id)
        );
    }

    var columnsSearch = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true,
            table: 'sl'
        },
        columns: [{
            dataIndex: 'id',
            header: "Id".l(),
            width: 90
        }, {
            dataIndex: 'query',
            header: "Query".l(),
            id: 'query',
            table: 'slq',
            filter: {
                operator: 'LIKE'
            },
            renderer: Axis.escape
        }, {
            header: "Results".l(),
            dataIndex: 'num_results',
            width: 90
        }, {
            header: "Customer".l(),
            width: 190,
            dataIndex: 'customer_email',
            renderer: renderCustomer,
            table: 'ac',
            sortName: 'email',
            filter: {
                name: 'email',
                operator: 'LIKE'
            }
        }, {
            dataIndex: 'hit',
            header: "Hit".l(),
            table: 'slq',
            width: 90
        }, {
            dataIndex: 'created_at',
            header: "Created On".l(),
            renderer: function(value) {
                return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, 'H:i:s');
            },
            width: 130
        }]
    });

    var gridSearch = new Axis.grid.EditorGridPanel({
        id: 'grid-search',
        autoExpandColumn: 'query',
        ds: storeSearch,
        cm: columnsSearch,
        view: new Ext.grid.GroupingView({
            emptyText: 'No records found'.l(),
            groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
        }),
        plugins:[new Axis.grid.Filter()],
        tbar: [{
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler: function() {
                var selectedItems = Ext.getCmp('grid-search')
                    .getSelectionModel()
                    .getSelections();

                if (!selectedItems.length || !confirm('Are you sure?'.l())) {
                    return;
                }

                var data = {};
                for (var i = 0, len = selectedItems.length; i < len; i++) {
                    data[i] = selectedItems[i]['data']['id'];
                }

                Ext.Ajax.request({
                    url: Axis.getUrl('search/delete'),
                    params: {data: Ext.encode(data)},
                    callback: function() {
                        storeSearch.reload();
                    }
                });
               }
        }, '->', {
            text: 'Reload'.l(),
            handler: function() {
                Ext.getCmp('grid-search').getStore().reload();
            },
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/refresh.png'
        }],
        bbar: new Axis.PagingToolbar({
            store: storeSearch
        })
    });

    new Axis.Panel({
        items: [
            gridSearch
        ]
    });
});