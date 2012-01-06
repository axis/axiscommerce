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
                {name: 'referrer'},
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

    var referrer = new Ext.grid.RowExpander({
        listeners: {
            beforeexpand: function(expander, record, body, rowIndex) {
                if (!this.tpl) {
                    this.tpl = new Ext.Template();
                }

                var html = '<ul style="padding: 5px 0 5px 50px;">';
                Ext.each(record.get('referrer').split(' '), function(url) {
                    html += String.format(
                        '<li><a href="{0}" title="{0}" onclick="window.open(this.href); return false;">{0}</a><li>',
                        url
                    );
                }, this);
                html += '</ul>';

                this.tpl.set(html);
            }
        }
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [referrer, {
            dataIndex: 'url',
            header: 'Url'.l(),
            id: 'url',
            filter: {
                operator: 'LIKE'
            }
        }, {
            dataIndex: 'referrer',
            header: 'Referrer'.l(),
            id: 'referrer',
            width: 300,
            renderer: function(value, meta, record) {
                // trim the value
                value = value.replace(/^\s*/, '').replace(/\s*$/, '');

                var values = value.split(' '),
                    suffix = '';

                if (values.length > 1) {
                    meta.attr += 'ext:qtip="' + values.join('<br/>') + '"';
                    suffix     = ' (+' + (values.length - 1) + ')';
                }
                return values[0] + suffix;
            },
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
        plugins: [
            referrer,
            new Axis.grid.Filter()
        ],
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

    var panel = new Axis.Panel({
        items: [
            grid
        ]
    });
});