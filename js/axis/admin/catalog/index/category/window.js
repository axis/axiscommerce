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
 */

var CategoryWindow = {

    el: null,

    form: null,

    close: function() {
        CategoryWindow.hide();
    },

    hide: function() {
        CategoryWindow.el.hide();
    },

    save: function(closeWindow) {
        CategoryWindow.form.getForm().submit({
            url: Axis.getUrl('catalog_category/save'),
            method: 'post',
            success: function(form, response) {
                CategoryGrid.reload();
                if (closeWindow) {
                    CategoryWindow.hide();
                    CategoryWindow.form.getForm().clear();
                } else {
                    var response = Ext.decode(response.response.responseText);
                    Category.load(response.data.category_id);
                }
            },
            failure: function(form, response) {
                if (response.failureType == 'client') {
                    return;
                }
            }
        });
    },

    show: function() {
        CategoryWindow.el.show();
    }

};

Ext.onReady(function() {

    Ext.form.Field.prototype.msgTarget = 'qtip';

    var reader = [
        {name: 'status'},
        {name: 'key_word'},
        {name: 'id', type: 'int'},
        {name: 'site_id', type: 'int'},
        {name: 'parent_id', type: 'int'},
        {name: 'image[base][src]', mapping: 'image_base'},
        {name: 'image[listing][src]', mapping: 'image_listing'}
    ];
    for (var id in Axis.languages) {
        reader.push(
            {name: 'name[' + id + ']',                  mapping: 'name_' + id},
            {name: 'description[' + id + ']',           mapping: 'description_' + id},
            {name: 'meta_title[' + id + ']',            mapping: 'meta_title_' + id},
            {name: 'meta_keyword[' + id + ']',          mapping: 'meta_keyword_' + id},
            {name: 'meta_description[' + id + ']',      mapping: 'meta_description_' + id},
            {name: 'image[base][title][' + id + ']',    mapping: 'image_base_title_' + id},
            {name: 'image[listing][title][' + id + ']', mapping: 'image_listing_title_' + id}
        );
    }

    CategoryWindow.form = new Axis.FormPanel({
        id: 'form-category',
        bodyStyle: {
            padding: '5px 0 0'
        },
        reader: new Ext.data.JsonReader({
                root: 'data'
            }, reader
        ),
        items: [{
            activeTab: 0,
            anchor: Ext.isWebKit ? 'undefined 100%' : '100% 100%',
            border: false,
            defaults: {
                autoScroll: true,
                bodyStyle: 'padding: 10px',
                hideMode: 'offsets',
                layout: 'form'
            },
            deferredRender: false,
            plain: true,
            xtype: 'tabpanel',
            items: [{
                title: 'Description'.l(),
                defaults: {
                    anchor: '-20',
                    border: false
                },
                items: [{
                    layout: 'column',
                    defaults: {
                        border: false,
                        columnWidth: '.5',
                        layout: 'form'
                    },
                    items: [{
                        items: [{
                            allowBlank: false,
                            anchor: '100%',
                            fieldLabel: 'Name'.l(),
                            name: 'name',
                            tpl: '{self}[{language_id}]',
                            xtype: 'langset'
                        }]
                    }, {
                        items: [{
                            allowBlank: false,
                            anchor: '100%',
                            fieldLabel: 'SEO url'.l(),
                            name: 'key_word',
                            xtype: 'textfield'
                        }]
                    }]
                }, {
                    defaultType: 'ckeditor',
                    fieldLabel: 'Description'.l(),
                    height: 150,
                    name: 'description',
                    tpl: '{self}[{language_id}]',
                    xtype: 'langset'
                }, {
                    allowBlank: false,
                    columns: [100, 100],
                    fieldLabel: 'Status'.l(),
                    initialValue: 'enabled',
                    name: 'status',
                    xtype: 'radiogroup',
                    items: [{
                        boxLabel: 'Enabled'.l(),
                        checked: true,
                        name: 'status',
                        inputValue: 'enabled'
                    }, {
                        boxLabel: 'Disabled'.l(),
                        name: 'status',
                        inputValue: 'disabled'
                    }]
                }, {
                    fieldLabel: 'Page title'.l(),
                    name: 'meta_title',
                    tpl: '{self}[{language_id}]',
                    xtype: 'langset'
                }, {
                    defaultType: 'textarea',
                    fieldLabel: 'Meta description'.l(),
                    name: 'meta_description',
                    tpl: '{self}[{language_id}]',
                    xtype: 'langset'
                }, {
                    fieldLabel: 'Meta keywords'.l(),
                    name: 'meta_keyword',
                    tpl: '{self}[{language_id}]',
                    xtype: 'langset'
                }, {
                    fieldLabel: 'Id'.l(),
                    name: 'id',
                    value: 0,
                    initialValue: 0,
                    xtype: 'hidden'
                }, {
                    fieldLabel: 'Parent id'.l(),
                    name: 'parent_id',
                    value: 0,
                    xtype: 'hidden'
                }, {
                    fieldLabel: 'Site id'.l(),
                    name: 'site_id',
                    value: 0,
                    xtype: 'hidden'
                }]
            }, {
                title: 'Images'.l(),
                items: [{
                    border: false,
                    layout: 'column',
                    defaults: {
                        border: false,
                        columnWidth: '0.5',
                        layout: 'form'
                    },
                    items: [{
                        items: [{
                            anchor: '-5',
                            title: 'Base Image'.l(),
                            xtype: 'fieldset',
                            items: [{
                                fieldLabel: 'Image'.l(),
                                url: Axis.getUrl('catalog_category/save-image'),
                                name: 'image[base][src]',
                                rootPath: 'media/category',
                                rootText: 'category',
                                xtype: 'imageuploadfield'
                            }, {
                                fieldLabel: 'Delete'.l(),
                                name: 'image[base][delete]',
                                xtype: 'checkbox'
                            }, {
                                anchor: '-20',
                                fieldLabel: 'Title'.l(),
                                name: 'image[base][title]',
                                tpl: '{self}[{language_id}]',
                                xtype: 'langset'
                            }]
                        }]
                    }, {
                        items: [{
                            title: 'Listing Image'.l(),
                            xtype: 'fieldset',
                            items: [{
                                fieldLabel: 'Image'.l(),
                                url: Axis.getUrl('catalog_category/save-image'),
                                name: 'image[listing][src]',
                                rootPath: 'media/category',
                                rootText: 'category',
                                xtype: 'imageuploadfield'
                            }, {
                                fieldLabel: 'Delete'.l(),
                                name: 'image[listing][delete]',
                                xtype: 'checkbox'
                            }, {
                                anchor: '-20',
                                fieldLabel: 'Title'.l(),
                                name: 'image[listing][title]',
                                tpl: '{self}[{language_id}]',
                                xtype: 'langset'
                            }]
                        }]
                    }]
                }]
            }]
        }]
    });

    CategoryWindow.el = new Axis.Window({
        id: 'window-category',
        items: [CategoryWindow.form],
        maximizable: true,
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
