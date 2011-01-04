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
Ext.onReady(function(){

    var history = new Ext.data.Record.create([
        {name: 'comments',             type: 'string'},
        {name: 'created_on',           type: 'date', dateFormat: 'Y-m-d H:i:s'},
        {name: 'id',                   type: 'int'},
        {name: 'notified',             type: 'int'},
        {name: 'order_id',             type: 'int'},
        {name: 'order_status_id',      type: 'int'},
        {name: 'status_name',          type: 'string'}
    ])

    var ds = new Ext.data.Store({
        reader: new Ext.data.JsonReader({
            id: 'id'
        }, history),
        mode: 'local'
    });

    var cm = new Ext.grid.ColumnModel([{
        header: 'Order Status'.l(),
        dataIndex: 'status_name',
        width: 150,
        menuDisabled: true
    }, {
        header: 'Comment'.l(),
        id: 'comments',
        dataIndex: 'comments',
        menuDisabled: true
    }, {
        header: 'Date added'.l(),
        dataIndex: 'created_on',
        width: 100,
        menuDisabled: true,
        renderer: function(v) {
            return Ext.util.Format.date(v);
        }
    }, {
        header: 'Notified'.l(),
        dataIndex: 'notified',
        width: 70,
        menuDisabled: true,
        renderer: function (value, meta, record) {
            var image = '/images/icons/delete.png';
            if (record.get('notified')) {
                 image = '/images/icons/accept.png';
            }

            return '<img alt="icons/accept.png" src="' + Axis.skinUrl + image + '">';
        }
    }])
    cm.defaultSortable = true;

    var gridHistory = new Ext.grid.GridPanel({
        cm: cm,
        ds: ds,
        autoExpandColumn: 'comments',
        id: 'grid-history',
        autoScroll: true,
        border: false,
        autoHeight: true
    });

    var tabStatusHistory = new Ext.Panel({
        id: 'tab-status-history',
        title: 'Status History'.l(),
        layout: 'fit',
        padding: '0',
        border: false,
        items: [gridHistory]
    });

}, this);
