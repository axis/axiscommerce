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

var GroupGrid = {

    el: null,

    remove: function(records) {
        var selectedItems = records || GroupGrid.el.getSelectionModel().selections.items;
        if (!selectedItems.length || !confirm('Are you sure?'.l())) {
            return;
        }

        var data = {};
        for (var i = 0; i < selectedItems.length; i++) {
            data[i] = selectedItems[i].id;
        }

        Ext.Ajax.request({
            url: Axis.getUrl('account/field-group/remove'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                GroupGrid.el.getStore().reload();
            }
        });
    },

    reload: function() {
        GroupGrid.el.getStore().reload();
    }
};

Ext.onReady(function() {

    var ds = new Ext.data.Store({
        autoLoad: true,
        id      : 'store-fieldgroup',
        url     : Axis.getUrl('account/field-group/list'),
        reader  : new Ext.data.JsonReader({
            root            : 'data',
            totalProperty   : 'count',
            idProperty      : 'id'
        }, [
            {name: 'id', type: 'int'},
            {name: 'name'},
            {name: 'is_active', type: 'int'},
            {name: 'sort_order', type: 'int'}
        ]),
        sortInfo: {
            field       : 'sort_order',
            direction   : 'ASC'
        },
        remoteSort: true
    });

    var actions = new Ext.ux.grid.RowActions({
        header:'Actions'.l(),
        actions:[{
            iconCls: 'icon-folder-edit',
            tooltip: 'Edit'.l()
        }, {
            iconCls: 'icon-folder-delete',
            tooltip: 'Delete'.l()
        }],
        callbacks: {
            'icon-folder-edit': function(grid, record, action, row, col) {
                Group.load(record.get('id'));
            },
            'icon-folder-delete': function(grid, record, action, row, col) {
                GroupGrid.remove([record]);
            }
        }
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header      : 'Name'.l(),
            id          : 'name',
            dataIndex   : 'name',
            filter: {
                operator: 'LIKE'
            }
        }, actions]
    });

    GroupGrid.el = new Axis.grid.GridPanel({
        autoExpandColumn: 'name',
        collapseMode    : 'mini',
        region  : 'west',
        width   : 250,
        ds      : ds,
        cm      : cm,
        plugins : [
            actions,
            new Axis.grid.Filter()
        ],
        tbar: [{
            text    : 'Add'.l(),
            icon    : Axis.skinUrl + '/images/icons/add.png',
            handler : Group.add
        }, {
            text    : 'Edit'.l(),
            icon    : Axis.skinUrl + '/images/icons/page_edit.png',
            handler : function() {
                var record = GroupGrid.el.getSelectionModel().getSelected();
                if (record) {
                    Group.load(record.get('id'));
                }
            }
        }, {
            text    : 'Delete'.l(),
            icon    : Axis.skinUrl + '/images/icons/delete.png',
            handler : function() {
                GroupGrid.remove();
            }
        }, '->', {
            icon    : Axis.skinUrl + '/images/icons/refresh.png',
            handler : function() {
                GroupGrid.el.getStore().reload();
            }
        }],
        bbar: new Axis.PagingToolbar({
            store: ds
        })
    });

    GroupGrid.el.on('rowclick', function(grid, index, e) {
        var fieldGridStore  = FieldGrid.el.getStore(),
            baseParams      = fieldGridStore.baseParams,
            fieldgroupId    = grid.getStore().getAt(index).get('id');

        delete baseParams['filter[fieldgroup][field]'];
        delete baseParams['filter[fieldgroup][value]'];
        if (!isNaN(fieldgroupId)) {
            baseParams['filter[fieldgroup][field]'] = 'customer_field_group_id';
            baseParams['filter[fieldgroup][value]'] = fieldgroupId;
        }
        fieldGridStore.load();
    });
});
