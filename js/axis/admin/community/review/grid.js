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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

Ext.onReady(function(){

    var ds = new Ext.data.Store({
        autoLoad: true,
        baseParams: {
            limit: 25
        },
        reader: new Ext.data.JsonReader({
            idProperty: 'id',
            root: 'data',
            totalProperty: 'count'
        }, review_object),
        remoteSort: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        },
        url: Axis.getUrl('community_review/get-list')
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true,
            table: 'cr'
        },
        columns: [{
            header: 'Id'.l(),
            dataIndex: 'id',
            width: 90
        }, {
            header: 'Product'.l(),
            dataIndex: 'product_name',
            id: 'product_name',
            table: 'cpd',
            filter: {
                name: 'name',
                operator: 'LIKE'
            },
            width: 300
        }, {
            header: 'Rating'.l(),
            dataIndex: 'rating',
            renderer: function(value) {
                return value ? value.toFixed(2) : '';
            },
            width: 90
        }, {
            header: 'Author'.l(),
            dataIndex: 'author',
            filter: {
                operator: 'LIKE'
            },
            width: 120
        }, {
            header: 'Title'.l(),
            dataIndex: 'title',
            id: 'title',
            filter: {
                operator: 'LIKE'
            }
        }, {
            header: 'Date created'.l(),
            dataIndex: 'date_created',
            renderer: function(v) {
                return Ext.util.Format.date(v);
            },
            width: 160
        }, {
            header: 'Status'.l(),
            dataIndex: 'status',
            renderer: function(value) {
                return value.l();
            },
            filter: {
                store: new Ext.data.SimpleStore({
                    data: [
                        ['pending', 'Pending'.l()],
                        ['approved', 'Approved'.l()],
                        ['disapproved', 'Disapproved'.l()]
                    ],
                    fields: ['id', 'name']
                }),
                resetValue: 'reset'
            },
            width: 100
        }]
    });

    var grid = new Axis.grid.GridPanel({
        autoExpandColumn: 'title',
        cm: cm,
        id: 'grid',
        ds: ds,
        plugins: [new Axis.grid.Filter()],
        tbar: [{
            text: 'Add'.l(),
            handler: function(){
                Ext.getCmp('form').getForm().clear();
                Ext.getCmp('window').show();
            },
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/add.png'
        }, {
            text: 'Edit'.l(),
            handler: edit,
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/page_edit.png'
        }, {
            text: 'Delete'.l(),
            handler: deleteSelected,
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/delete.png'
        },'->', {
            text: 'Reload'.l(),
            handler: reload,
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/refresh.png'
        }],
        bbar: new Axis.PagingToolbar({
            store: ds
        })
    })

    grid.on('rowdblclick', function(grid, rowIndex, e){
        loadEditForm(Ext.getCmp('grid').store.getAt(rowIndex));
    })

    new Axis.Panel({
        items: [
            grid
        ]
    });

    function reload(){
        Ext.getCmp('grid').store.reload();
    }

    function edit(){
        var selected = Ext.getCmp('grid').getSelectionModel().getSelected();

        if (!selected) {
            return;
        }

        loadEditForm(selected);
    }

    var tries = 0;
    function loadEditForm(row) {
        Ext.getCmp('form').getForm().clear();
        Ext.getCmp('product_combo').store.load({
            params: {
                id: row.get('product_id')
            },
            callback: function() {
                tryShowWindow();
            }
        })
        if (typeof row.get('customer_id') == 'number') {
            Ext.getCmp('customer_combo').store.load({
                params: {
                    id: row.get('customer_id')
                },
                callback: function() {
                    tryShowWindow();
                }
            });
        } else {
            tryShowWindow();
        }
        function tryShowWindow() {
            if (++tries == 2) {
                Ext.getCmp('window').setTitle(row.get('product_name')).show();
                fillForm(row);
                tries = 0;
            }
        }
    }

    function deleteSelected(){
        var selections = Ext.getCmp('grid').getSelectionModel().getSelections();

        if (!selections.length || !confirm('Are you sure?'.l())) {
            return;
        }

        var obj = {};
        for (var i = 0, len = selections.length; i < len; i++) {
            obj[i] = selections[i]['id'];
        }
        var jsonData = Ext.encode(obj);
        Ext.Ajax.request({
            url: Axis.getUrl('community_review/delete'),
            method: 'post',
            params: {
                data: jsonData
            },
            success: reload,
            filure: function(){
                alert('An error has been occured'.l());
            }
        })
    }
})