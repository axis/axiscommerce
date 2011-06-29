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

    var baseParams = {
        limit: 10
    };
    if (currentSiteId) {
        baseParams['filter[1][field]'] = 'site_id';
        baseParams['filter[1][value]'] = currentSiteId;
    }

    var ds = new Ext.data.Store({
        baseParams: baseParams,
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Ext.data.Record.create([
            {name: 'id', type: 'int'},
            {name: 'num_results', type: 'int'},
            {name: 'hit', type: 'int'},
            {name: 'session_id'},
            {name: 'created_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
            {name: 'query'},
            {name: 'customer_email'},
            {name: 'customer_id', type: 'int'}
        ])),
        remoteSort: true,
        sortInfo: {
            field: 'created_at',
            direction: 'DESC'
        },
        url: Axis.getUrl('search/list')
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: false,
            menuDisabled: true
        },
        columns: [{
            header: "Query".l(),
            id: 'query',
            dataIndex: 'query'
        }, {
            header: "Results".l(),
            width: 50,
            dataIndex: 'num_results'
        }, {
            header: "Hit".l(),
            width: 25,
            dataIndex: 'hit'
        }, {
            header: "Created On".l(),
            width: 130,
            renderer: function(value) {
                return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, 'H:i:s');
            },
            dataIndex: 'created_at',
        }]
    });

    var grid = new Axis.grid.GridPanel({
        autoExpandColumn: 'query',
        title: 'Search Terms'.l(),
        ds: ds,
        cm: cm,
        border: false,
        massAction: false,
        bbar: []
    });

    StatisticsPanel.addTab(grid, 30);

    new Axis.DelayedLoader({
        el: grid,
        ds: ds
    });
});