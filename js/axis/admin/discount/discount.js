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

Ext.onReady(function () {

    var ds = new Ext.data.Store({
        baseParams: {
            limit: 25
        },
        reader: new Ext.data.JsonReader({
                idProperty: 'id',
                root : 'data',
                totalProperty: 'count'
            }, [
                {name: 'id', type: 'int'},
                {name: 'name'},
                {name: 'from_date', type: 'date', dateFormat: 'Y-m-d'},
                {name: 'to_date', type: 'date', dateFormat: 'Y-m-d'},
                {name: 'is_active', type: 'int'},
                {name: 'priority', type: 'int'},
                {name: 'is_combined', type: 'int'}
            ]
        ),
        remoteSort: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        },
        url: Axis.getUrl('discount_index/list')
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: "Id".l(),
            width: 90,
            dataIndex: 'id'
        }, {
            header: "Discount Name".l(),
            id:'name',
            dataIndex: 'name'
        }, {
            header: "Priority".l(),
            width: 80,
            dataIndex: 'priority'
        }, {
            header: "Combined".l(),
            width: 110,
            dataIndex: 'is_combined',
            renderer: function(value) {
                if (value) {
                    return 'Combined'.l();
                }
                return 'Not Combined'.l();
            },
            filter: {
                editable: false,
                resetValue: 'reset',
                store: new Ext.data.ArrayStore({
                    data: [[0, 'Not Combined'.l()], [1, 'Combined'.l()]],
                    fields: ['id', 'name']
                })
            }
        }, {
            header: "Start Date".l(),
            width: 130,
            renderer: function(value) {
                return Ext.util.Format.date(value);
            },
            dataIndex: 'from_date'
        }, {
            header: "End Date".l(),
            width: 130,
            renderer: function(value) {
                return Ext.util.Format.date(value);
            },
            dataIndex: 'to_date'
        }, {
            header: "Status".l(),
            width: 90,
            dataIndex: 'is_active',
            renderer: function(value) {
                if (value) {
                    return 'Enabled'.l();
                }
                return 'Disabled'.l();
            },
            filter: {
                editable: false,
                resetValue: 'reset',
                store: new Ext.data.ArrayStore({
                    data: [[0, 'Disabled'.l()], [1, 'Enabled'.l()]],
                    fields: ['id', 'name']
                })
            }
        }]
    });

    var grid = new Axis.grid.EditorGridPanel({
        id: 'gridDiscount',
        autoExpandColumn: 'name',
        ds: ds,
        cm: cm,
        plugins: [new Axis.grid.Filter()],
        tbar: [{
                text: 'Add'.l(),
                icon: Axis.skinUrl + '/images/icons/add.png',
                cls: 'x-btn-text-icon',
                handler : function() {
                    window.location = Axis.getUrl('discount_index/create');
                }
            },{
                text: 'Delete'.l(),
                icon: Axis.skinUrl + '/images/icons/delete.png',
                cls: 'x-btn-text-icon',
                handler : function () {
                    if (!confirm('Are you sure?'.l()))
                        return;
                    var data = {};
                    var selectedItems = grid.getSelectionModel().selections.items;
                    var len = selectedItems.length;
                    for (var i = len; i--;) {
                        if (!selectedItems[i]['data']['id']) {
                            continue;
                        }
                        data[i] = selectedItems[i]['data']['id'];
                    }

                    Ext.Ajax.request({
                        url: Axis.getUrl('discount_index/delete'),
                        params: {data: Ext.encode(data)},
                        callback: function() {
                            grid.getStore().reload();
                        }
                    });
                }
            }, {
                text: 'Edit'.l(),
                cls: 'x-btn-text-icon',
                icon: Axis.skinUrl + '/images/icons/save_multiple.png',
                handler: function () {
                    var selectedItems = grid.getSelectionModel().selections.items;
                    if (!selectedItems.length) {
                        alert('Select discount'.l());
                        return;
                    }
                    window.location = Axis.getUrl(
                        'discount_index/edit/id/' + selectedItems[0]['data']['id']
                    );
                }
            },
            new Ext.Toolbar.Separator(),
            new Ext.Toolbar.TextItem('Display mode  '.l()),
            new Ext.Toolbar.Item('tbar-display-mode') , '->',
            {
                text: 'Reload'.l(),
                icon: Axis.skinUrl + '/images/icons/refresh.png',
                cls: 'x-btn-text-icon',
                handler: function() {
                    grid.getStore().reload();
                }
            }
        ],
        bbar: new Axis.PagingToolbar({
            store: ds
        })
    });

    new Axis.Panel({
        items: [
            grid
        ]
    });

    Ext.getCmp('gridDiscount').getStore().load({params:{
        start:0, limit:25, displayMode: 'without-special'
    }});
    Ext.getCmp('gridDiscount').on('rowdblclick', function(grid, rowIndex, e){
        var row = Ext.getCmp('gridDiscount').getStore().getAt(rowIndex);
        window.location = Axis.getUrl('discount_index/edit/id/')
            + row.data.id;
    });

    Ext.get('tbar-display-mode').on('change', function(event, element) {
        Ext.getCmp('gridDiscount').getStore().baseParams['displayMode'] =
            element.options[element.selectedIndex].value;
        Ext.getCmp('gridDiscount').getStore().load();
    })
});
