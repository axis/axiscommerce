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
            {name: 'email'},
            {name: 'subject'},
            {name: 'message'},
            {name: 'custom_info'},
            {name: 'department_name'},
            {name: 'created_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
            {name: 'department_id', type: 'int'},
            {name: 'datetime'},
            {name: 'message_status'}
        ])),
        remoteSort: true,
        sortInfo: {
            field: 'created_at',
            direction: 'DESC'
        },
        url: Axis.getUrl('contacts_index/list')
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: false,
            menuDisabled: true
        },
        columns: [{
            header: "Email".l(),
            dataIndex: 'email',
            // renderer: function(value, meta, record) {
            //     return String.format(
            //         '<a href="{1}" target="_blank">{0}</a>',
            //         value,
            //         Axis.getUrl('contacts_index/index/mailId/' + record.get('id'))
            //     );
            // },
            width: 150
        },{
            id: 'subject',
            header: "Subject".l(),
            dataIndex: 'subject'
        },{
            header: "Created On".l(),
            dataIndex: 'created_at',
            renderer: function(value) {
                return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, 'H:i:s');
            },
            width: 130
        }]
    });

    var grid = new Axis.grid.GridPanel({
        autoExpandColumn: 'subject',
        title: 'Messages'.l(),
        ds: ds,
        cm: cm,
        border: false,
        massAction: false,
        bbar: []
    });

    ActivityPanel.addTab(grid, 30);

    new Axis.DelayedLoader({
        el: grid,
        ds: ds
    });
});