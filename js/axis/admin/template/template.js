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

Ext.namespace('Axis', 'Axis.Template', 'Axis.Template.Template', 'Axis.Template.Box');

Ext.onReady(function() {
    
    var Template = {
        load: function(node, e) {
            if (node.id == '0')
                return;
            Template.id = node.id;
            Axis.Template.Box.loadGrid(node.id);
            Axis.Template.Layout.loadGrid(node.id);
        },
        create: function() {
            templateForm.getForm().reset();
            templateWin.show();
        },
        edit: function() {
            templateForm.getForm().reset();
            if (tree.getSelectionModel().getSelectedNode() && tree.getSelectionModel().getSelectedNode().isSelected()) {
                var templateId = tree.getSelectionModel().getSelectedNode().id;
                templateForm.getForm().load({
                    url:   Axis.getUrl('template_index/get-info/'),
                    params: {templateId: tree.getSelectionModel().getSelectedNode().id},
                    method: 'post'
                });
                templateWin.show();
            }
        },
        remove: function() {
            var selectedItem = tree.getSelectionModel().getSelectedNode();
            
            if (!selectedItem)
                return;
            
            if (!confirm('Are you sure?'))
                return;
                
            Ext.Ajax.request({
                url: Axis.getUrl('template_index/delete'),
                params: {templateId: selectedItem.id},
                method: 'post',
                callback: function(request, success, response) {
                    
                    var res = Ext.decode(response.responseText)
                    if (!res.success){
                        alert('Error ' + res.data.error);
                    }
                    rootNode.reload();
                }
            });
        },
        save : function() {
            templateForm.getForm().submit({
                method: 'post',
                success: function(form, response) {
                    rootNode.reload();
                    templateWin.hide();
                } 
            });
        },
        startImport: function() {
            importForm.getForm().reset();
            importWin.show();
        },
        importT: function() {
            importForm.getForm().submit({
                method: 'get',
                success: function() {
                    rootNode.reload();
                    importWin.hide();
                },
                failure: function(form, response) {
                    var data = Ext.decode(response.response.responseText);
                    if (data.errorCode == 'template_exists') {
                        Ext.Msg.show({
                            title:'Are you sure?'.l(),
                            buttons: Ext.Msg.YESNO,
                            modal: false,
                            msg: 'Template with the same name already exist. Do you want to import data to existing template?'.l(),
                            icon: Ext.MessageBox.QUESTION,
                            fn: function(response) {
                                if (response == 'yes') {
                                    importForm.getForm().submit({
                                        url: importForm.url + '/overwrite_existing/1',
                                        method: 'post',
                                        success: function() {
                                            rootNode.reload();
                                            importWin.hide();
                                        }
                                    });
                                }
                            }
                        })
                    }
                }
            });
        },
        exportT: function() {
            var template = tree.getSelectionModel().getSelectedNode();
            if (!template)
                return;
                
            if (!confirm('Export this template?'))
                return;
            
            Ext.Ajax.request({
                url: Axis.getUrl('template_index/export'),
                params: {templateId: template.id},
                method: 'post',
                callback: function(request, success, response) {
                    rootNode.reload();
                }
            });
        }
    }
    
    Axis.Template.Template = Template;
    
    var rootNode = new Ext.tree.AsyncTreeNode({
        text: 'Templates'.l(),
        draggable:false,
        id: '0'
    });
    
    var tree = new Ext.tree.TreePanel({
        collapseMode: 'mini',
        collapsible: true,
        header: false,
        split: true,
        region: 'west',
        width: 250,
        height: 550,
        useArrows:true,
        autoScroll:true,
        root: rootNode,
        animate: false,
        containerScroll: true, 
        loader: new Ext.tree.TreeLoader({
            dataUrl: Axis.getUrl('template_index/get-nodes')
        }),
        tbar: [{
            text: 'Edit'.l(),
            tooltip: {text:'Edit template'},
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            cls: 'x-btn-text-icon',
            handler: Template.edit
        }, {
            text: 'Export'.l(),
            tooltip: {text:'Export template'},
            icon: Axis.skinUrl + '/images/icons/brick_go.png',
            cls: 'x-btn-text-icon',
            handler: Template.exportT
        }, {
            text: 'Delete'.l(),
            tooltip: {text:'Uninstall template'},
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler: Template.remove
        }, '->', {
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-icon',
            handler: function(){
                tree.getLoader().load(tree.getRootNode(), function(){
                    tree.getRootNode().expand();
                });
            }
        }]
    });
    
    tree.on('click', Template.load);
    rootNode.expand();
    
    var tabPanel = new Ext.TabPanel({
        split: true,
        region: 'center',
        plain: true,
        autoScroll: true,
        height: 550,
        activeTab: 0,
        items: [
            Axis.Template.Box.grid,
            Axis.Template.Layout.grid
        ]
    });
    
    var templateStore = new Ext.data.JsonStore({
        url: Axis.getUrl('template_index/list-xml-templates'),
        root: 'data',
        id: 'template',
        fields: ['template'/*, 'file'*/]
    });
    
    var importForm = new Ext.FormPanel({
        url: Axis.getUrl('template_index/import'),
        defaults: {width: 130},
        border: false,
        bodyStyle: 'padding: 10px 5px 0',
        items: [new Ext.form.ComboBox({
            triggerAction: 'all',
            fieldLabel: 'Template'.l(),
            displayField: 'template',
            typeAhead: true,
            name: 'templateName',
            allowBlank: false,
            editable: false,
            mode: 'local',
            forceSelection: false,
            store: templateStore,
            lazyRender: true,
            anchor: '98%',
            valueField: 'template'
        })]
    }); 
    
    var templateForm = new Ext.FormPanel({
        url: Axis.getUrl('template_index/save'),
        defaults: {width: 130},
        border: false,
        bodyStyle: 'padding: 10px 5px 0',
        defaultType: 'textfield',
        items: [{
            fieldLabel: 'Template Name'.l(),
            name: 'name',
            anchor: '98%',
            allowBlank:false
        }, Ext.getCmp('layoutCombo').cloneConfig({
            fieldLabel: 'Default layout'.l(),
            anchor: '98%',
            allowBlank: false,
            name: 'default_layout'
        }), {
            fieldLabel: 'Active'.l(),
            xtype: 'checkbox',
            checked: true,
            name: 'is_active'
        }, {
            fieldLabel: 'Assignments'.l(),
            readOnly: true,
            anchor: '98%',
            name: 'assignments'
        }, {
            xtype: 'hidden',
            name: 'id'
        }]
    })
    
    var templateWin =  new Ext.Window({
        layout: 'fit',
        width: 400,
        height: 220,
        plain: false, 
        title: 'Template',
        closeAction: 'hide',
        buttons: [{
            text: 'Save'.l(),
            handler: Template.save
        }, {
            text: 'Cancel'.l(),
            handler: function(){
                templateWin.hide();
            }
        }],
        items: templateForm
    })
    
    var importWin =  new Ext.Window({
        layout: 'fit',
        width: 300,
        height: 110,
        plain: false, 
        title: 'Template',
        closeAction: 'hide',
        buttons: [{
            text: 'Ok'.l(),
            handler: Template.importT
        }, {
            text: 'Cancel'.l(),
            handler: function(){
                importWin.hide();
            }
        }],
        items: importForm
    })
    
    tree.getRootNode().on('load', function(node){
        templateStore.load();
    });
    
    new Axis.Panel({
        items: [
            tree,
            tabPanel
        ]
    });
});