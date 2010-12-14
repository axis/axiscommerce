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
        }, [
            {name: 'id', type: 'int'},
            {name: 'site_id', type: 'int'},
            {name: 'date_purchased_on', type: 'date', dateFormat: 'Y-m-d H:i:s'},
            {name: 'billing_firstname'},
            {name: 'billing_lastname'},
            {name: 'delivery_name'},
            {name: 'customer_name'},
            {name: 'customer_email'},
            {name: 'customer_id', type: 'int'},
            {name: 'order_status_id', type: 'int'},
            {name: 'order_total_base'}
        ]),
        remoteSort: true,
        sortInfo: {
            field: 'date_purchased_on',
            direction: 'DESC'
        },
        url: Axis.getUrl('sales_order/list')
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: false,
            menuDisabled: true
        },
        columns: [{
            header: "Payer".l(),
            id: 'payer',
            dataIndex: 'billing_firstname',
            renderer: function (value, meta, record) {
                return String.format(
                    '<a href="{1}" target="_blank">{0}</a>',
                    value + ' ' + record.get('billing_lastname'),
                    Axis.getUrl('sales_order/index/orderId/' + record.get('id'))
                );
            },
        }, {
            header: "Order Total".l(),
            dataIndex: 'order_total_base',
            sortName: 'order_total',
            width: 130
        }, {
            header: "Purchased On".l(),
            dataIndex: 'date_purchased_on',
            renderer: function(value) {
                return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, 'H:i:s');
            },
            width: 130
        }]
    });

    var grid = new Axis.grid.GridPanel({
        autoExpandColumn: 'payer',
        title: 'Orders'.l(),
        ds: ds,
        cm: cm,
        border: false,
        massAction: false,
        bbar: []
    });

    ActivityPanel.addTab(grid, 10);

    new Axis.DelayedLoader({
        el: grid,
        ds: ds
    });
});