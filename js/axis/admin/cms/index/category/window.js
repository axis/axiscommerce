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

var CategoryWindow = {

    el: null,

    form: null,

    formFields: [],

    save: function(closeWindow) {
        CategoryWindow.form.getForm().submit({
            url: Axis.getUrl('cms_index/save-category'),
            success: function(form, action) {
                CategoryTree.reload();
                if (closeWindow) {
                    CategoryWindow.hide();
                    CategoryWindow.form.getForm().clear();
                } else {
                    var response = Ext.decode(action.response.responseText);
                    Category.load(response.data.id);
                }
            },
            failure: function(form, action) {
                if (action.failureType == 'client') {
                    return;
                }
            }
        });
    },

    show: function() {
        CategoryWindow.el.show();
    },

    hide: function() {
        CategoryWindow.el.hide();
    }

};

Ext.onReady(function() {

    CategoryWindow.formFields = [
        {name: 'id', type: 'int'},
        {name: 'site_id', type: 'int'},
        {name: 'parent_id', type: 'int'},
        {name: 'name'},
        {name: 'is_active'}
    ];
    for (var id in Axis.locales) {
        CategoryWindow.formFields.push({
            name: 'content[' + id + '][title]',
            mapping: 'content.lang_' + id + '.title'
        }, {
            name: 'content[' + id + '][description]',
            mapping: 'content.lang_' + id + '.description'
        }, {
            name: 'content[' + id + '][link]',
            mapping: 'content.lang_' + id + '.link'
        }, {
            name: 'content[' + id + '][meta_title]',
            mapping: 'content.lang_' + id + '.meta_title'
        }, {
            name: 'content[' + id + '][meta_description]',
            mapping: 'content.lang_' + id + '.meta_description'
        }, {
            name: 'content[' + id + '][meta_keyword]',
            mapping: 'content.lang_' + id + '.meta_keyword'
        });
    }

    CategoryWindow.form = new Axis.form.FormPanel({
        bodyStyle: 'padding: 10px 10px 0',
        method: 'post',
        reader: new Ext.data.JsonReader({
            root: 'data'
        }, CategoryWindow.formFields),
        defaults: {
            anchor: '-20'
        },
        items: [{
            layout: 'column',
            border: false,
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
                    initialValue: '1',
                    triggerAction: 'all',
                    editable: false,
                    hiddenName: 'is_active',
                    anchor: '100%'
                }]
           }]
        }, {
            name: 'content[title]',
            fieldLabel: 'Title'.l(),
            xtype: 'langset'
        }, {
            name: 'content[link]',
            fieldLabel: 'Link'.l(),
            xtype: 'langset'
        }, {
            name: 'content[description]',
            defaultType: 'textarea',
            fieldLabel: 'Description'.l(),
            xtype: 'langset'
        }, {
            xtype: 'fieldset',
            title: 'Meta'.l(),
            defaults: {
                anchor: '100%'
            },
            items: [{
                name: 'content[meta_title]',
                fieldLabel: 'Title'.l(),
                xtype: 'langset'
            }, {
                name: 'content[meta_description]',
                defaultType: 'textarea',
                fieldLabel: 'Description'.l(),
                xtype: 'langset'
            }, {
                name: 'content[meta_keyword]',
                defaultType: 'textarea',
                fieldLabel: 'Keywords'.l(),
                xtype: 'langset'
            }]
        }, {
            xtype: 'hidden',
            name: 'id',
            initialValue: 0
        }, {
            xtype: 'hidden',
            name: 'parent_id'
        }, {
            xtype: 'hidden',
            name: 'site_id'
        }]
    });

    CategoryWindow.el = new Axis.Window({
        items: CategoryWindow.form,
        title: 'Category'.l(),
        buttons: [{
            icon: Axis.skinUrl + '/images/icons/database_save.png',
            text: 'Save'.l(),
            handler: function() {
                CategoryWindow.save(true);
            }
        }, {
            icon: Axis.skinUrl + '/images/icons/database_save.png',
            text: 'Save & Continue Edit'.l(),
            handler: function() {
                CategoryWindow.save(false);
            }
        }, {
            icon: Axis.skinUrl + '/images/icons/cancel.png',
            text: 'Cancel'.l(),
            handler: CategoryWindow.hide
        }]
    });
});
