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

var AttributeGrid = {

    remove: function() {
        var selectedItems = Attribute.grid.getSelectionModel().selections.items;

        if (!selectedItems.length || !confirm('Are you sure?'.l())) {
            return;
        }

        var data = {};
        for (var i = 0; i < selectedItems.length; i++) {
            data[i] = selectedItems[i].id;
        }

        Ext.Ajax.request({
            url: Axis.getUrl('catalog_product-attributes/delete'),
            params: {data: Ext.encode(data)},
            callback: function() {
                Attribute.grid.getStore().reload();
            }
        });
    },

    renderType: function(id) {
        var record = null;
        if ((record = Attribute.inputTypeStore.getById(id))) {
            return record.get('title');
        } else {
            return id;
        }
    }
};

Ext.onReady(function() {

    var ds = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('catalog_product-attributes/list')
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, [
            {name: 'id', type: 'int'},
            {name: 'code'},
            {name: 'name'},
            {name: 'input_type'},
            {name: 'sort_order', type: 'int'}
        ]),
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        },
        remoteSort: true
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: 'Id'.l(),
            dataIndex: 'id',
            width: 90
        }, {
            header: 'Code'.l(),
            id: 'code',
            dataIndex: 'code',
            width: 250,
            filter: {
                operator: 'LIKE'
            }
        }, {
            header: 'Name'.l(),
            id: 'name',
            dataIndex: 'name',
            width: 250,
            table: 'cpot',
            filter: {
                operator: 'LIKE'
            }
        }, {
            header: 'Type'.l(),
            dataIndex: 'input_type',
            renderer: AttributeGrid.renderType,
            filter: {
                store: Attribute.inputTypeStore,
                resetValue: 'reset',
                displayField: 'title',
                valueField: 'id'
            }
        }, {
            header: 'Sort Order'.l(),
            dataIndex: 'sort_order'
        }]
    });

    Attribute.grid = AttributeGrid = new Axis.grid.GridPanel({
        autoExpandColumn: 'name',
        ds: ds,
        cm: cm,
        plugins: [new Axis.grid.Filter()],
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler: Attribute.add
        }, {
            text: 'Edit'.l(),
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            handler: function() {
                var record = Attribute.grid.getSelectionModel().getSelected();
                if (record) {
                    Attribute.load(record.get('id'));
                }
            }
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: AttributeGrid.remove
        }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: function() {
                Attribute.grid.getStore().reload();
            }
        }],
        bbar: new Axis.PagingToolbar({
            store: ds
        })
    });

    Attribute.grid.on('rowdblclick', function(grid, index, e) {
        Attribute.load(grid.getStore().getAt(index).get('id'));
    });

    ds.load({params:{start:0, limit:25}});
});