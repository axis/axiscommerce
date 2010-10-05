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
    
    var form = new Ext.FormPanel({
        id: 'form',
        border: false,
        labelAlign: 'left',
        autoHeight: true,
        bodyStyle: 'padding: 10px 10px 5px 10px; display: block;',
        defaults: {
            anchor: '95%',
            msgTarget: 'side'
        },
        items:
        [Ext.getCmp('product_combo'),
         Ext.getCmp('status_combo'),
         Ext.getCmp('customer_combo'),
         Ext.getCmp('rating_fieldset'), {
            fieldLabel: 'Author'.l(),
            maxLength: 55,
            xtype: 'textfield',
            allowBlank: false,
            name: 'author'
        }, {
            fieldLabel: 'Title'.l(),
            maxLength: 55,
            xtype: 'textfield',
            allowBlank: false,
            name: 'title'
        }, {
            fieldLabel: 'Pros'.l(),
            maxLength: 250,
            xtype: 'textarea',
            name: 'pros',
            allowBlank: false
        }, {
            fieldLabel: 'Cons'.l(),
            maxLength: 250,
            xtype: 'textarea',
            name: 'cons',
            allowBlank: false
        }, Ext.getCmp('resizable_area').cloneConfig({
            name: 'summary',
            height: 120,
            allowBlank: true,
            fieldLabel: 'Summary'.l()
        }), {
            fieldLabel: 'id',
            xtype: 'hidden',
            name: 'id',
            allowBlank: true
        }]
    })
    
    var window = new Ext.Window({
        id: 'window',
        layout: 'fit',
        constrainHeader: true,
        resizable: true,
        maximizable: true,
        closeAction: 'hide',
        width: '650',
        title: 'Review'.l(),
        bodyStyle: 'background-color: white;',
        items: form,
        buttons:
        [{
            text: 'Save'.l(),
            handler: function(){
                form.getForm().submit({
                    url: Axis.getUrl('community_review/save'),
                    method: 'post',
                    success: function(){
                        window.hide();
                        Ext.getCmp('grid').store.reload();
                    }
                });
            }
        }, {
            text: 'Cancel'.l(),
            handler: function(){
                window.hide();
            }
        }]
    })
})