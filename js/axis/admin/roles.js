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
                Ext.getCmp('remove-role-button').disable();
                return;
            }
            Role.id = node.id;
            Ext.getCmp('save-role-button').enable();
            Ext.getCmp('remove-role-button').enable();
            Ext.Ajax.request({
                method: 'post',
                url: Axis.getUrl('roles/edit'),
                params: {
                    id: node.id
                },
                success: Role.init
            });
        },

        template: new Ext.Template.from('parent-role-tpl'),

        init: function(response, options) {
            Role.resetForm();
            var obj = Ext.decode(response.responseText);
            $('#roleId').val(obj.data.id);
            $('#roleName').val(obj.data.name);

            // enable possible checkboxes
            for (var i in obj.data.possibleParents) {
                var role = obj.data.possibleParents[i];
                Role.template.append('parent-roles', {
                    id: role['id'],
                    name: role['role_name']
                });
            }

            // load parent allows if parent roles was changed
            $('#parent-roles input').change(function(){
                Role.loadParentAllows();
            });

            // set checked for current parents
            for (var i = 0, n = obj.data.parents.length;  i < n; i++) {
                $('#role-' + obj.data.parents[i]).attr('checked', 'checked');
            }

            // check rules
            $('#role-rules input').removeAttr('checked');

            // colorize parent rules
            Role.markParentRules(obj.data.parentAllows);

            for (var i = 0, n = obj.data.rules.length; i < n; i++) {
                var rule = obj.data.rules[i];
                var id = rule['resource_id'].replace(/\//g, '-');
                $('#' + rule['permission'] + '-' + id).attr('checked', 'checked');
            }
        },

        markParentRules: function(allows) {
            // clear old
            $('#role-rules .parentAllowed').removeClass('parentAllowed');
            // mark new
            for (var i = 0, n = allows.length; i < n; i++) {
                var id = allows[i].replace(/\//g, '-');
                $('#' + id).addClass('parentAllowed')
                $('#allow-' + id).attr('checked', 'checked');
            }
        },

        save: function() {
            if (Role.id == null) {
                alert('Select role for edit');
                return;
            }
            Ext.Ajax.request({
                url: Axis.getUrl('roles/save/roleId/') + Role.id,
                form: 'form-role'
            });
        },

        add: function() {
            var name = $('#new-role-name');
            Ext.Ajax.request({
                url: Axis.getUrl('roles/add'),
                params: {
                    'roleName': name.val()
                },
                success: function(response) {
                    data = Ext.decode(response.responseText);
                    Ext.getCmp('remove-role-button').disable();

                    tree.getRootNode().appendChild(new Ext.tree.TreeNode({
                        'id': data.id,
                        text: name.val()
                    }));

                    Role.template.append('parent-roles', {'id': data.id, 'name': name.val()});

                    name.val('');
                    //tree.getRootNode().reload();
                    //Role.loadParent();
                }
            });
        },

        remove: function() {
            var node = tree.getSelectionModel().getSelectedNode();

            if (!node || !confirm('Are you sure?'.l())) {
                return;
            }

            Ext.Ajax.request({
                url: Axis.getUrl('roles/delete'),
                params: {
                    'roleId': node.id
                },
                success: function() {
                    Ext.getCmp('remove-role-button').disable();
                    Ext.getCmp('save-role-button').disable();
                    tree.getRootNode().reload();
                    Role.resetForm();
                    Ext.getCmp('save-role-button').disable();
                }
            });
        },

        resetForm: function() {
            $('#parent-roles').empty();
            $('#role-rules .parentAllowed').removeClass('parentAllowed');
        },

        loadParent: function() {
            Ext.Ajax.request({
                url: Axis.getUrl('roles/list'),
                success: function(response, options) {
                    $('#parent-roles').html(response.responseText);
                    $('#parent-roles input').change(function(){
                        Role.loadParentAllows();
                    });
                }
            });
        },

        loadParentAllows: function() {
            var parents = [];
            $('#parent-roles input').each(function(){
                if (this.checked)
                    parents.push(this.value);
            });

            Ext.Ajax.request({
                url: Axis.getUrl('roles/get-parent-allows'),
                params: {
                    'parents': Ext.encode(parents)
                },
                success: function(response, options) {
                    var allows = Ext.decode(response.responseText);
                    Role.markParentRules(allows);
                }
            });
        }
    };
    Axis.Role = Role;

    var treeToolBar = new Ext.Toolbar();
    treeToolBar.addText('Add'.l());
    treeToolBar.addField(new Ext.form.TextField({
        id: 'new-role-name'
    }));
    treeToolBar.addButton({
        icon: Axis.skinUrl + '/images/icons/add.png',
        cls: 'x-btn-icon',
        handler : Role.add
    });
    treeToolBar.addButton({
        id: 'remove-role-button',
        icon: Axis.skinUrl + '/images/icons/delete.png',
        cls: 'x-btn-icon',
        handler : Role.remove,
        tooltip: 'Remove'.l(),
        disabled: true
    });

    var tree = new Ext.tree.TreePanel({
        width: 230,
        useArrows:true,
        autoScroll:true,
        animate: false,
        collapsible: true,
        collapseMode: 'mini',
        header: false,
        region: 'west',
        split: true,
        loader: new Ext.tree.TreeLoader({
            dataUrl: Axis.getUrl('roles/get-nodes')
        }),
        tbar: treeToolBar
    });

    // set the root node
    var rootNode = new Ext.tree.AsyncTreeNode({
        text: 'Roles'.l(),
        draggable:false,
        id: '0'
    });
    tree.setRootNode(rootNode);
    tree.on('click', Role.load);

    rootNode.expand();

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
            rolesPanel
        ]
    });

});