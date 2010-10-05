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

Ext.onReady(function() {

     categoryForm = new Ext.form.FormPanel({
        labelWidth: 80,
        autoScroll: true,
        border: false,
        reader: new Ext.data.JsonReader({
            root: 'category'
            },
            categoryReader
        ),
        defaults: {
            anchor: '-20'
        },
        items: [{
            layout: 'column',
            labelAlign: 'top',
            border: false,
            bodyStyle: 'padding: 10px 10px 0',
            items: [{
                columnWidth: 0.5,
                layout: 'form',
                border: false,
                items: [{
                    fieldLabel: 'Name'.l(),
                    xtype: 'textfield',
                    allowBlank: false,
                    name: 'name',
                    anchor: '-20'
                }]
            }, {
                columnWidth: 0.5,
                layout: 'form',
                border: false,
                items: [{
                    fieldLabel: 'Status'.l(),
                    xtype: 'combo',
                    name: 'is_active',
                    store: new Ext.data.SimpleStore({
                        fields: ['id', 'value'],
                        data: [['0', 'Disabled'.l()], ['1', 'Enabled'.l()]]
                    }),
                    displayField: 'value',
                    valueField: 'id',
                    mode: 'local',
                    value: '1',
                    emptyText: 'Select status'.l(),
                    triggerAction: 'all',
                    editable: false,
                    hiddenName: 'is_active',
                    anchor: '100%'
                }]
           }]
        }, {
            xtype: 'tabpanel',
            layoutOnTabChange: true,
            deferredRender: false,
            border: false,
            activeTab: 0,
            plain:true,
            defaults: {
                autoHeight:true,
                bodyStyle:'padding:10px'
            },
            items: categoryTabs
        }]
    });

    categoryWindow = new Ext.Window({
        closeAction: 'hide',
        plain: true,
        width: 520,
        height: 530,
        layout: 'fit',
        items: categoryForm,
        title: 'Category',
        buttons:
        [{
            text: 'Save'.l(),
            handler: saveCategory
        }, {
            text: 'Cancel'.l(),
            handler: function(){
                categoryWindow.hide();
            }
        }]
    });
})

function saveCategory() {
    categoryForm.getForm().submit({
        url: Axis.getUrl('cms_index/save-category'),
        params: {catId: category, parentId: parentId, siteId: site},
        clientValidation: true,
        success: function(form, response){
            var obj = Ext.decode(response.response.responseText);
            var parentNodeId = (parentId ? parentId : '_' + site);
            siteTree.getLoader().load(siteTree.root, function() {
                var path = siteTree.getNodeById(obj.data.catId).getPath();
                siteTree.selectPath(path)
            });
            categoryWindow.hide();
        }
    })
}
