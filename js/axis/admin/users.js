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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

Ext.onReady(function(){
    Ext.QuickTips.init();

    var status = new Axis.grid.CheckColumn({
        header: 'Status'.l(),
        width: 90,
        dataIndex: 'is_active',
        filter: {
            editable: false,
            resetValue: 'reset',
            store: new Ext.data.ArrayStore({
                data: [[0, 'Disabled'.l()], [1, 'Enabled'.l()]],
                fields: ['id', 'name']
            })
        }
    });

    var User = Ext.data.Record.create([
        {name: 'id', type: 'int'},
        {name: 'firstname'},
        {name: 'lastname'},
        {name: 'email'},
        {name: 'username'},
        {name: 'password'},
        {name: 'role_id', type: 'int'},
        {name: 'is_active', type: 'int'}
    ]);

    var ds = new Ext.data.Store({
        autoLoad: true,
        baseParams: {
            limit: 25
        },
        url: Axis.getUrl('user/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, User),
        remoteSort: true,
        pruneModifiedRecords: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        }
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
            header: "Firstname".l(),
            dataIndex: 'firstname',
            width: 160,
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }, {
            header: "Lastname".l(),
            dataIndex: 'lastname',
            width: 160,
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }, {
            header: "Email".l(),
            dataIndex: 'email',
            id: 'email',
            width: 200,
            editor: new Ext.form.TextField({
               allowBlank: false,
               vtype: 'email'
            })
        }, {
            header: "Username".l(),
            dataIndex: 'username',
            width: 120,
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }, {
            header: "Password".l(),
            dataIndex: 'password',
            width: 120,
            editor: new Ext.form.TextField({
               allowBlank: true
            }),
            filterable: false
        }, {
            header: "Role".l(),
            dataIndex: 'role_id',
            width: 150,
            editor: new Ext.form.ComboBox({
                typeAhead: true,
                triggerAction: 'all',
                lazyRender: true,
                store: new Ext.data.ArrayStore({
                    data: roles,
                    fields: ['id', 'name']
                }),
                mode: 'local',
                displayField: 'name',
                valueField: 'id'
            }),
            renderer: function(v) {
                var i = 0;
                while (roles[i]) {
                    if (v == roles[i][0]) {
                        return roles[i][1];
                    }
                    i++;
                }
                return 'None'.l();
            },
            filter: {
                editable: false,
                store: new Ext.data.ArrayStore({
                    data: roles,
                    fields: ['id', 'name']
                })
            }
        }, status]
    });

    var grid = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'email',
        ds: ds,
        cm: cm,
        plugins: [
            status,
            new Axis.grid.Filter()
        ],
        bbar: new Axis.PagingToolbar({
            store: ds
        }),
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
            handler : function(){
                var u = new User({
                    firstname   : '',
                    lastname    : '',
                    email       : '',
                    username    : '',
                    password    : '',
                    role_id     : 0,
                    is_active   : true
                });
                grid.stopEditing();
                grid.getStore().insert(0, u);
                grid.startEditing(0, 2);
            }
        },{
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler : function(){
                var data = {};
                var modified = ds.getModifiedRecords();
                for (var i = 0; i < modified.length; i++) {
                    data[modified[i]['id']] = modified[i]['data'];
                }
                var jsonData = Ext.encode(data);
                Ext.Ajax.request({
                    url: Axis.getUrl('user/batch-save'),
                    params: {data: jsonData},
                    callback: function() {
                        ds.commitChanges();
                        ds.reload();
                    }
                });
            }
        },{
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler : function(){
                var selectedItems = grid.getSelectionModel().selections.items;

                if (!selectedItems.length || !confirm('Are you sure?'.l())) {
                    return;
                }

                var data = {};

                for (var i = 0; i < selectedItems.length; i++) {
                    data[i] = selectedItems[i].id;
                }
                var jsonData = Ext.encode(data);
                Ext.Ajax.request({
                    url: Axis.getUrl('user/remove'),
                    params: {data: jsonData},
                    callback: function() {
                        ds.reload();
                    }
                });
            }
        }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler : function(){
                grid.getStore().reload();
            }
        }]
    });

    new Axis.Panel({
        items: [
            grid
        ]
    });
});
