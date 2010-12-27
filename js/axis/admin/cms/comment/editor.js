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

    commentForm = new Axis.form.FormPanel({
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
                    store: new Ext.data.SimpleStore({
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
        }]
    });

    commentWindow = new Ext.Window({
        closeAction: 'hide',
        plain: false,
        width: 640,
        height: 490,
        maximizable: true,
        layout: 'fit',
        title: 'Comment',
        items: commentForm,
        buttons: [{
            text: 'Save'.l(),
            handler: submitComment
        }, {
            text: 'Cancel'.l(),
            handler: function() {
                commentWindow.hide();
            }
        }]
    });
})

function submitComment() {
     commentForm.getForm().submit({
          url: Axis.getUrl('cms_comment/save-comment'),
          params: {commentId: comment, pageId: page},
        success: function(){
            commentWindow.hide();
            commentGrid.getStore().reload();
        }
     });
}
