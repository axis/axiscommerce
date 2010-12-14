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

Ext.onReady(function() {

    var baseParams = {
        limit: 10
    }
    if (currentSiteId != 0 ) {
        baseParams['siteId'] = currentSiteId;
    }

    var ds = new Ext.data.Store({
        baseParams: baseParams,
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Ext.data.Record.create([
            {name: 'id', type: 'int'},
            {name: 'viewed', type: 'int'},
            {name: 'price', type: 'float'},
            {name: 'name'}
        ])),
        remoteSort: true,
        sortInfo: {
            field: 'viewed',
            direction: 'DESC'
        },
        url: Axis.getUrl('catalog_index/list-viewed')
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: false,
            menuDisabled: true
        },
        columns: [{
            header: "Product".l(),
            id: 'product',
            dataIndex: 'name',
            renderer: function(value, meta, record) {
                return String.format(
                    '<a href="{1}" target="_blank">{0}</a>',
                    value,
                    Axis.getUrl('catalog_index/index/productId/' + record.get('id'))
                );
            }
        }, {
            header: "Price".l(),
            width: 90,
            dataIndex: 'price'
        }, {
            header: "Viewed".l(),
            width: 70,
            dataIndex: 'viewed'
        }]
    });

    var grid = new Axis.grid.GridPanel({
        autoExpandColumn: 'product',
        title: 'Best viewed'.l(),
        ds: ds,
        cm: cm,
        border: false,
        massAction: false,
        bbar: []
    });

    StatisticsPanel.addTab(grid, 20);

    new Axis.DelayedLoader({
        el: grid,
        ds: ds
    });
});