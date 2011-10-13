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
    var Role = {

        id: null,
        load: function(node, e) {            
            if (node.id == '0') {
                return;
            }
            Role.id = node.id;
            console.log('load role' + Role.id);
        },
        add: function() {
            form.getForm().clear();
            windowRole.show();
        },
        edit: function() {
            form.getForm().clear();
            form.getForm().load({
                url:   Axis.getUrl('role/load'),
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
            if (!node || !confirm('Are you sure?'.l())) {
                return;
            }

            Ext.Ajax.request({
                url: Axis.getUrl('role/remove'),
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
            dataUrl: Axis.getUrl('role/list')
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
        url: Axis.getUrl('role/save'),
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
    
    var ds = new Ext.ux.maximgb.treegrid.AdjacencyListStore({
        autoLoad: true,
        reader: new Ext.data.JsonReader({
            idProperty: 'id'
        }, [
            {name: 'id'}, // this is not integer
            {name: 'leaf'},
            {name: 'text'},
            {name: 'code'},
            {name: 'option_code'},
            {name: 'option_name'},
            {name: 'value_name'},
            {name: 'input_type', type: 'int'},
            {name: 'languagable', type: 'int'},
            {name: 'option_id', type: 'int'},
            {name: 'value_id', type: 'int'},
            {name: 'parent'}
        ]),
        paramNames: {
            active_node: 'node'
        },
        leaf_field_name: 'leaf',
        parent_id_field_name: 'parent',
        url: Axis.getUrl('catalog/product-option/nlist'),
        listeners: {
            load: {
                scope: this,
                fn: this.onLoad
            }
        }
    });
    
    var rolesPanel = new Ext.Panel({
        autoScroll: true,
        maskDisabled: true,
        contentEl: 'form-role',
        collapsible: true,
        header: false,
        region: 'center',
        split: true,
        tbar: [{
            id: 'save-role-button',
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/accept.png',
            cls: 'x-btn-text-icon',
            handler: Role.save,
            disabled: true
        }]
    });
    
    

    new Axis.Panel({
        items: [
            tree,
//            rolesPanel,
//            treeResources
        ]
    });

});