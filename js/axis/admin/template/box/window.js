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
    
    Ext.QuickTips.init();
    
    Ext.form.Field.prototype.msgTarget = 'side';
    
    var tabs = [{
        title: 'General'.l(),
        bodyStyle:'padding:10px',
        layout:'form',
        items: [{
            xtype: 'hidden',
            name: 'data[id]'
        }, {
            fieldLabel: 'Parent block'.l(),
            xtype: 'textfield',
            name: 'data[block]',
            anchor: '95%',
            allowBlank: false
        }, {
            fieldLabel: 'Class'.l(),
            xtype: 'textfield',
            name: 'data[class]',
            anchor: '95%',
            allowBlank: false
        }, {
            fieldLabel: 'Sort Order'.l(),
            xtype: 'textfield',
            name: 'data[order]',
            anchor: '95%',
            allowBlank: false
        }, {
            fieldLabel: 'Status'.l(),
            xtype: 'textfield',
            name: 'data[status]',
            anchor: '95%',
            allowBlank: false
        }, {
            fieldLabel: 'Config'.l(),
            xtype: 'textarea',
            name: 'data[config]',
            anchor: '95%',
            allowBlank: false
        }]
    }, Ext.getCmp('grid-box-exception')]
    
    var form = new Ext.FormPanel({
        id: 'form',
        border: false,
        labelAlign: 'left',
        items: [{
            xtype: 'tabpanel',
            border: false,
            plain: true,
            activeTab: 0,
            autoHeight: true,
            autoScroll: true,
            //deferredRender: true,
            layoutOnTabChange: true,
            defaults: {
                autoHeight: true
            },
            items: tabs
        }]
    })
    
    var window = new Ext.Window({
        id: 'window',
        layout: 'fit',
        constrainHeader: true,
        resizable: true,
        maximizable: true,
        closeAction: 'hide',
        width: 550,
        height: 400,
        title: 'Box'.l(),
        bodyStyle: 'background: white; padding-top: 7px',
        items: form,
        buttons:
        [{
            text: 'Save'.l(),
            handler: function(){
                form.getForm().submit({
                    url: Axis.getUrl('template_box/save'),
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