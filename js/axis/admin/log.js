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

    var ds = new Ext.data.GroupingStore({
        autoLoad: true,
        baseParams: {
            limit: 25
        },
        reader: new Ext.data.JsonReader({
                root : 'data',
                totalProperty: 'count'
            }, [
                {name: 'url'},
                {name: 'hit', type: 'int'},
                {name: 'date', type: 'date', dateFormat: 'Y-m-d'}
            ]
        ),
        remoteSort: true,
        sortInfo: {
            field: 'date',
            direction: 'DESC'
        },
        url: Axis.getUrl('log/list')
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            dataIndex: 'url',
            header: 'Url'.l(),
            id: 'url',
            filter: {
                operator: 'LIKE'
            }
        }, {
            dataIndex: 'hit',
            header: 'Hit'.l(),
            table: '',
            width: 90
        }, {
            dataIndex: 'date',
            header: 'Date'.l(),
            renderer: function(value) {
                return Ext.util.Format.date(value);
            },
            table: '',
            width: 130
        }]
    });

    var grid = new Axis.grid.GridPanel({
        autoExpandColumn: 'url',
        ds: ds,
        cm: cm,
        id: 'grid-log',
        plugins: [new Axis.grid.Filter()],
        tbar: ['->', {
            text: 'Reload'.l(),
            handler: function() {
                Ext.getCmp('grid-log').getStore().reload();
            },
            icon: Axis.skinUrl + '/images/icons/refresh.png'
        }],
        bbar: new Axis.PagingToolbar({
            store: ds
        })
    });

    new Axis.Panel({
        items: [
            grid
        ]
    });
});