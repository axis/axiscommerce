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

Ext.namespace('Axis', 'Axis.Role');
Ext.onReady(function(){
    
    var Rule = {
        load: function() {
            
            storeRules.load({
                params: {
                    'role_id': Role.id
                }, 
                callback : function(records, options, success) {
                    if (!success) {
                        return;
                    }
                    var store = Ext.StoreMgr.lookup('store-resource');
                    var data = [];
                    store.each(function(resource, i) {
                        data[i] = resource.data;
                        data[i]['allow'] = 0;
                        data[i]['deny'] = 0;
                        Ext.each(records, function(rule) {
                            if (resource.get('id') == rule.get('resource_id')) {
                                if (rule.get('permission') == 'allow') {
                                    data[i]['allow'] = 1;
                                } else {
                                    data[i]['deny'] = 1;
                                } 
//                                delete(records[j]);
                            }
                        });
                    });
                    store.loadData(data, false);
                }
            });
        }, 
        save : function (){
            if (null == Role.id) {
                alert(Role.id); 
                return;
            }
            var store = Ext.StoreMgr.lookup('store-resource');
            var modified = store.getModifiedRecords();

            if (!modified.length) {
                return;
            }

            var data = {};
            
            for (var i = 0; i < modified.length; i++) {
                var row = modified[i]['data'];
                var permission = null;
                if (1 == row['allow']) {
                    permission = 'allow';
                }
                if (1 == row['deny']) {
                    permission = 'deny';
                }
                data[modified[i]['id']] = {
                    'role_id'     : Role.id,
                    'resource_id' : row['id'],
                    'permission'  : permission
                    
                };
            }
            Ext.Ajax.request({
                url: Axis.getUrl('acl-rule/batch-save'),
                params: {
                    dataset: Ext.encode(data)
                },
                success: Rule.load
            });
            
        }  
    };
    
    var Role = {

        id: null,
        load: function(node, e) {            
            if (node.id == '0') {
                return;
            }
            Role.id = node.id;
            Rule.load();
        },
        add: function() {
            form.getForm().clear();
            windowRole.show();
        },
        edit: function() {
            form.getForm().clear();
            form.getForm().load({
                url:   Axis.getUrl('acl-role/load'),
                params: {id: Role.id},
                method: 'post'
            });
            windowRole.show();
        },

        save: function() {
            form.getForm().submit({
                method: 'post',
                success: function(form, response) {
                    rootNode.reload();
                    windowRole.hide();
                }
            });
        },

        remove: function() {
            if (!confirm('Are you sure?'.l())) {
                return;
            }

            Ext.Ajax.request({
                url: Axis.getUrl('acl-role/remove'),
                params: {'id': Role.id},
                method: 'post',
                success: function() {
                    rootNode.reload();
                }
            });
        }
    };
    Axis.Role = Role;

    var rootNode = new Ext.tree.AsyncTreeNode({
        text: 'Roles'.l(),
        draggable:false,
        id: '0'
    });
    
    var tree = new Ext.tree.TreePanel({
        collapseMode: 'mini',
        collapsible: true,
        header: false,
        split: true,
        region: 'west',
        width: 230,
//        height: 550,
        useArrows:true,
        autoScroll:true,
        root: rootNode,
        rootVisible: false,
        animate: false,
        containerScroll: true,
        loader: new Ext.tree.TreeLoader({
            dataUrl: Axis.getUrl('acl-role/list')
        }),
        tbar: {
            enableOverflow: true,
            items: [{
                text: 'Add'.l(),
                icon: Axis.skinUrl + '/images/icons/add.png',
                handler: Role.add
            }, {
                text: 'Edit'.l(),
                icon: Axis.skinUrl + '/images/icons/page_edit.png',
                handler: Role.edit
            }, {
                text: 'Delete'.l(),
                icon: Axis.skinUrl + '/images/icons/delete.png',
                handler: Role.remove
            }, '->', {
                icon: Axis.skinUrl + '/images/icons/refresh.png',
                handler: function(){
                    tree.getLoader().load(tree.getRootNode(), function(){
                        tree.getRootNode().expand();
                    });
                }
            }]
        }
    });
    tree.on('click', Role.load);
    rootNode.expand();
    
    var fields = [
        {name: 'role[id]',         type: 'int',  mapping: 'role.id'},
        {name: 'role[role_name]',                mapping: 'role.role_name'},
        {name: 'role[sort_order]', type: 'int',  mapping: 'role.sort_order'}
    ];
    
    var form = new Axis.FormPanel({
        url: Axis.getUrl('acl-role/save'),
        defaults: {
            anchor: '100%'
        },
        border: false,
        bodyStyle: 'padding: 10px 5px 0',
        defaultType: 'textfield',
        reader      : new Ext.data.JsonReader({
            root        : 'data',
            idProperty  : 'role.id'
        }, fields),
        items: [{
            fieldLabel: 'Name'.l(),
            name: 'role[role_name]',
            allowBlank:false
        },{
            fieldLabel: 'Sort Order'.l(),
            name: 'role[sort_order]',
            allowBlank:false
        }, {
            xtype: 'hidden',
            name: 'role[id]'
        }]
    });

    var windowRole = new Axis.Window({
        width: 250,
        height: 160,
        title: 'Role'.l(),
        buttons: [{
            text: 'Save'.l(),
            handler: Role.save
        }, {
            text: 'Cancel'.l(),
            handler: function(){
                windowRole.hide();
            }
        }],
        items: form
    });
    
    var storeResource = new Ext.ux.maximgb.treegrid.AdjacencyListStore({
        storeId: 'store-resource',
        autoLoad: true,
        mode: 'local',
        reader: new Ext.data.JsonReader({
            idProperty: 'id'
        }, [
            {name: 'id'}, // this is not integer
            {name: 'text'}, 
            {name: 'leaf'},
            {name: 'deny'},
            {name: 'allow'},
            {name: 'parent'}
        ]),
        paramNames: {
            active_node: 'node'
        },
        leaf_field_name: 'leaf',
        parent_id_field_name: 'parent',
        url: Axis.getUrl('acl-resource/list')
    });

    var denyColumn = new Axis.grid.CheckColumn({
        dataIndex: 'deny',
        header: 'Deny'.l(),
        width: 100,
        onMouseDown: function(e, t) {
            var index = this.grid.getView().findRowIndex(t);
            
            Axis.grid.CheckColumn.prototype.onMouseDown.call(this, e, t);
            
            if(t.className && t.className.indexOf('x-grid3-cc-'+this.id) != -1){
                var record = this.grid.store.getAt(index);
                if (record.data[this.dataIndex] == this.fields.enabled) {
                    record.set('allow', this.fields.disabled);
                }
            }
            
        }
    });
    
    var allowColumn = new Axis.grid.CheckColumn({
        dataIndex: 'allow',
        header: 'Allow'.l(),
        width: 100,
        onMouseDown: function(e, t) {
            var index = this.grid.getView().findRowIndex(t);
            
            Axis.grid.CheckColumn.prototype.onMouseDown.call(this, e, t);
            
            if(t.className && t.className.indexOf('x-grid3-cc-'+this.id) != -1){
                var record = this.grid.store.getAt(index);
                if (record.data[this.dataIndex] == this.fields.enabled) {
                    record.set('deny', this.fields.disabled);
                }
            }
        }
    });
    
    var cm = new Ext.grid.ColumnModel({
        columns: [{
            dataIndex: 'text',
            header: 'Resource'.l(),
            id: 'text'
        }, denyColumn, allowColumn]
    });
    
    var gridResources = new Axis.grid.GridTree({
        autoExpandColumn: 'text',
        cm: cm,
        ds: storeResource,
        enableDragDrop: false,
        master_column_id: 'text',
        massAction: false,
        plugins: [denyColumn, allowColumn
//                new Ext.ux.grid.Search({
//                    mode: 'local',
////                    align: 'left',
//                    iconCls: false,
//                    position: 'top',
//                    width: 200,
//                    minLength: 0
//                })
        ],
        tbar: [{
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler : Rule.save
        }, '->', {
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-icon',
            handler: function() {
                gridResources.getStore().reload();
            }
        }
        ]
    }); 
    
    var storeRules = new Ext.data.JsonStore({
        storeId: 'storeRules',
        url: Axis.getUrl('acl-rule/list'),
        root: 'data',
        id: 'role_id',
        fields: [
            {name: 'role_id', type: 'int'}, 
            {name: 'resource_id'},
            {name: 'permission'}
        ]
    });

    new Axis.Panel({
        items: [
            tree, 
            gridResources
        ]
    });

});