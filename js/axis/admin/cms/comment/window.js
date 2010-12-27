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

var CommentWindow = {

    el: null,

    form: null,

    show: function() {
        CommentWindow.el.show();
    },

    hide: function() {
        CommentWindow.el.hide();
    },

    save: function(closeWindow) {
        CommentWindow.form.getForm().submit({
            url: Axis.getUrl('cms_comment/save-comment'),
            success: function(form, action) {
                CommentGrid.el.getStore().reload();
                if (closeWindow) {
                    CommentWindow.hide();
                    CommentWindow.form.getForm().clear();
                }
            }
        });
    }
};

Ext.onReady(function() {

    Ext.form.Field.prototype.msgTarget = 'qtip';

    CommentWindow.form = new Axis.form.FormPanel({
        labelAlign: 'top',
        bodyStyle: 'padding: 5px 5px 0px 5px',
        reader: new Ext.data.JsonReader({
            root: 'comment'
        }, [
            'author',
            'email',
            'status',
            'content'
        ]),
        items: [{
            layout: 'column',
            border: false,
            anchor: '100%',
            items: [{
                columnWidth: .3,
                layout: 'form',
                border: false,
                items: [{
                    xtype: 'textfield',
                    fieldLabel: 'Author'.l(),
                    name: 'author',
                    allowBlank: false,
                    maxLength: 45,
                    anchor: '-5'
                }]
            }, {
                columnWidth: .4,
                layout: 'form',
                border: false,
                items: [{
                    fieldLabel: 'Email'.l(),
                    xtype: 'textfield',
                    name: 'email',
                    vtype: 'email',
                    anchor: '-5',
                    maxLength: 45
                }]
            }, {
                columnWidth: .3,
                layout: 'form',
                border: false,
                items: [{
                    fieldLabel: 'Status'.l(),
                    xtype: 'combo',
                    name: 'status',
                    emptyText: 'Select status'.l(),
                    hiddenName: 'status',
                    store: new Ext.data.ArrayStore({
                        fields: ['id', 'value'],
                        data: status
                    }),
                    displayField: 'value',
                    valueField: 'id',
                    mode: 'local',
                    editable: false,
                    triggerAction: 'all',
                    anchor: '100%',
                    maxLength: 45
                }]
            }]
        }, {
            name: 'content',
            fieldLabel: 'Content'.l(),
            anchor: '100%',
            height: 300,
            xtype: 'textarea'
        }, {
            name: 'id',
            initialValue: 0,
            xtype: 'hidden'
        }, {
            name: 'cms_page_id',
            xtype: 'hidden'
        }]
    });

    CommentWindow.el = new Axis.Window({
        maximizable: true,
        title: 'Comment'.l(),
        items: CommentWindow.form,
        buttons: [{
            icon: Axis.skinUrl + '/images/icons/database_save.png',
            text: 'Save'.l(),
            handler: function() {
                CommentWindow.save(true);
            }
        }, {
            icon: Axis.skinUrl + '/images/icons/database_save.png',
            text: 'Save & Continue Edit'.l(),
            handler: function() {
                CommentWindow.save(false);
            }
        }, {
            icon: Axis.skinUrl + '/images/icons/cancel.png',
            text: 'Cancel'.l(),
            handler: CommentWindow.hide
        }]
    });
});
