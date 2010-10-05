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
    Ext.QuickTips.init();
    
    var status = new Axis.grid.CheckColumn({
        header: 'Status'.l(),
        width: 60,
        dataIndex: 'is_active'
    });
    
    function renderRole(value){
        var roles = rolesList;
        return roles[value] ? roles[value] : 'None';  
    };
    
    var fm = Ext.form, Ed = Ext.grid.GridEditor;
    var dsRole = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            method: 'get',
            url: Axis.getUrl('users/get-roles')
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, [
            {name: 'id'}, 
            {name: 'role_name'}
        ])
    });
    dsRole.load();
    
    var User = Ext.data.Record.create([
        {name: 'firstname', type: 'string'},
        {name: 'lastname', type: 'string'},
        {name: 'email', type: 'string'},
        {name: 'username', type: 'string'},
        {name: 'password', type: 'string'},
        {name: 'role_id'},
        {name: 'is_active', type: 'bool'}
    ]);
    
    var ds = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('users/get-list')
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, User),
        remoteSort: true,
        pruneModifiedRecords: true
    });
    
    var cm = new Ext.grid.ColumnModel([
        {
            header: "Firstname".l(),
            dataIndex: 'firstname',
            width: 160,
            editor: new Ed(new fm.TextField({
               allowBlank: false
            }))
        }, {
            header: "Lastname".l(),
            dataIndex: 'lastname',
            width: 160,
            editor: new Ed(new fm.TextField({
               allowBlank: false
            }))
        }, {
            header: "Email".l(),
            dataIndex: 'email',
            id: 'email',
            width: 200,
            editor: new Ed(new fm.TextField({
               allowBlank: false,
               vtype: 'email'
            }))
        }, {
            header: "Username".l(),
            dataIndex: 'username',
            width: 120,
            editor: new Ed(new fm.TextField({
               allowBlank: false
            }))
        }, {
            header: "Password".l(),
            dataIndex: 'password',
            width: 120,
            editor: new Ed(new fm.TextField({
               allowBlank: true
            }))
        }, {
            header: "Role".l(),
            dataIndex: 'role_id',
            width: 150,
            editor: new Ed(new fm.ComboBox({
                typeAhead: true,
                triggerAction: 'all',
                lazyRender: true,
                store: dsRole,
                mode: 'local',
                displayField: 'role_name',
                valueField: 'id'
            })),
            renderer: function(value) {
                var item = dsRole.getById(value);
                if (typeof(item) == 'undefined')
                    return 'None';
                else
                    return item.get('role_name');
                if (value == '0') {
                    return "None";
                } else {
                    return dsRole.getById(value).get('role_name');
                }
            }
        }, status
    ]);
    
    var grid = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'email',
        ds: ds,
        cm: cm,
        plugins: status,
        bbar: new Axis.PagingToolbar({
            store: ds
        }),
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
            handler : function(){
                var u = new User({
                    firstname: '',
                    lastname: '',
                    email: '',
                    username: '',
                    password: '',
                    role_id: '0',
                    is_active: true
                });
                grid.stopEditing();
                grid.getStore().insert(0, u);
                grid.startEditing(0, 1);
            }
        },{
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            cls: 'x-btn-text-icon',
            handler : function(){
                var data = {};
                var modified = ds.getModifiedRecords();
                for (var i = 0; i < modified.length; i++) {
                    data[modified[i]['id']] = modified[i]['data'];
                }
                var jsonData = Ext.encode(data);
                Ext.Ajax.request({
                    url: Axis.getUrl('users/save'),
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
            cls: 'x-btn-text-icon',
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
                    url: Axis.getUrl('users/delete'),
                    params: {data: jsonData},
                    callback: function() {
                        ds.reload();
                    }
                });
            }
        }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-text-icon',
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
    
    ds.load({params:{start:0, limit:25}});
}, this);