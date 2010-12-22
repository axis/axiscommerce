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

    pageForm = new Ext.form.FormPanel({
        labelWidth: 80,
        autoScroll: true,
        border: true,
        labelAlign: 'top',
        region: 'center',
        bodyStyle: 'padding: 5px',
        reader: new Ext.data.JsonReader({
            root: 'page'
        }, pageReader),
        collpseMode: 'mini',
        header: false,
        split: true,
        items: [{
            layout: 'column',
            border: false,
            anchor: '-20',
            bodyStyle: 'padding: 5px 0px 0px',
            items: [{
                columnWidth: 0.5,
                layout: 'form',
                border: false,
                items: [{
                    fieldLabel: 'Name'.l(),
                    xtype: 'textfield',
                    name: 'name',
                    allowBlank: false,
                    anchor: '-10'
                }, {
                    fieldLabel: 'Status'.l(),
                    xtype: 'combo',
                    name: 'is_active',
                    hiddenName: 'is_active',
                    store: new Ext.data.SimpleStore({
                        fields: ['id', 'value'],
                        data: [['0', 'Disabled'.l()], ['1', 'Enabled'.l()]]
                    }),
                    displayField: 'value',
                    valueField: 'id',
                    mode: 'local',
                    editable: false,
                    value: '1',
                    emptyText: 'Select status'.l(),
                    triggerAction: 'all',
                    anchor: '-10'
                }]
            }, {
                columnWidth: 0.5,
                layout: 'form',
                border: false,
                items: [{
                    fieldLabel: 'Layout'.l(),
                    xtype: 'combo',
                    name: 'layout',
                    hiddenName: 'layout',
                    store: layoutStore,
                    displayField: 'name',
                    valueField: 'name',
                    mode: 'local',
                    editable: false,
                    emptyText: 'Select layout'.l(),
                    triggerAction: 'all',
                    anchor: '100%'
                }, {
                    fieldLabel: 'Comments'.l(),
                    xtype: 'combo',
                    name: 'comment',
                    editable: false,
                    hiddenName: 'comment',
                    store: new Ext.data.SimpleStore({
                        fields: ['id', 'value'],
                        data: [['0', 'Disabled'.l()], ['1', 'Enabled'.l()]]
                    }),
                    displayField: 'value',
                    valueField: 'id',
                    mode: 'local',
                    value: '1',
                    emptyText: 'Comments to page'.l(),
                    triggerAction: 'all',
                    anchor: '100%'
                }]
            }]
        }, {
            xtype: 'tabpanel',
            layoutOnTabChange: true,
            deferredRender: false,
            activeTab: 0,
            plain: true,
            autoHeight: true,
            border: true,
            defaults: {bodyStyle: 'padding: 10px 10px 5px', autoHeight: true},
            anchor: '-20',
            items: pageTabs
        }]
    });

    root = new Ext.tree.AsyncTreeNode({
        text: 'Axis root node'.l(),
        id: 'rootNode'
    })

    categoryBlock = new Ext.tree.TreePanel({
        border: true,
        lines: false,
        animate: false,
        enableDD: false,
        selModel: new Ext.tree.MultiSelectionModel(),
        containerScroll: true,
        root: root,
        layout: 'fit',
        rootVisible: false,
        region: 'east',
        autoScroll: true,
        collapsible: true,
        collpseMode: 'mini',
        header: false,
        split: true,
        width: 150,
        loader: new Ext.tree.TreeLoader({
            url: Axis.getUrl('cms_index/get-site-tree'),
            baseAttrs: {
                checked: false,
                expanded: false
            }
        }),
        bbar: [{
            text: 'Toggle check'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/tick.png',
            handler: function() {
                categoryBlock.root.cascade(function(){
                      this.ui.toggleCheck();
                })
            }
        }],
        listeners: {
            checkchange: function(node, checked) {
                if (isNaN(node.id)) return;
                if (checked) {
                    this.getSelectionModel().select(node, null, true);
                }
                else {
                    node.unselect();
                }
            },
            click: function(node, e){
                if (isNaN(node.id)) return;
                if (node.isSelected()) {
                    node.unselect();
                    node.checked = false;
                    node.attributes.checked = false;
                    node.ui.toggleCheck(false);
                } else {
                    this.getSelectionModel().select(node, e, true);
                    node.checked = true;
                    node.attributes.checked = true;
                    node.ui.toggleCheck(true);
                }
                return false;
            },
            dblclick: function(node, e) {
                node.ui.toggleCheck(!node.ui.isChecked());
            }
        }
    });

    pageWindow = new Axis.Window({
        width: 800,
        height: 500,
        maximizable: true,
        layout: 'border',
        border: false,
        items: [
            pageForm,
            categoryBlock
        ],
        title: 'Page',
        buttons: [{
            text: 'Save'.l(),
            handler: submitPageForm
        }, {
            text: 'Cancel'.l(),
            handler: function(){
                pageWindow.hide();
            }
        }]
    });

    pageForm.on('resize', function(){
        pageWindow.doLayout();
    });

})

function submitPageForm(){
    pageForm.getForm().submit({
        url: Axis.getUrl('cms_index/save-page'),
        params: {
            pageId: page,
            category: Ext.encode(categoryBlock.getChecked('id'))
        },
        success: function(){
            pageWindow.hide();
            pageGrid.getStore().load();
        }
    })
}
