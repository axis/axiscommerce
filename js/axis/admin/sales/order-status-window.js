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
    
    var orderStatus = new Ext.data.Store({
        url:  Axis.getUrl('sales_order-status/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        },
        ['id', 'name']
        )
       // autoLoad: true
    });
    orderStatus.load();
    
    orderStatusChild = new Ext.data.Store({
        url:  Axis.getUrl('sales_order-status/get-childs'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        },
        ['id', 'name']
        )
        //autoLoad: true
    });
    
    var tabs = [{
        title: 'General',
        layout:'form',
        items: [{
            layout: 'form',
            border: false,
            items: [new Ext.form.ComboBox({
                triggerAction: 'all',
                id: 'add-order-status-from',
                displayField: 'name',
                typeAhead: true,
                mode: 'local',
                valueField: 'id',
                fieldLabel: 'From Status'.l(),
                name: 'from',
                hiddenName: 'from',
                store: orderStatus,
                lazyRender: true,
                anchor: '98%'
            }),
            {
                fieldLabel: 'Name'.l(),
                name: 'name',
                xtype: 'textfield',
                allowBlank: false,
                anchor: '98%'
            }, {
                xtype             : 'multiselect',
                name              :  'to',
                id                :  'multiselect',
                fieldLabel        :  'To statuses'.l(),
                dataFields        :  ['id', 'name'], 
                store             :  orderStatusChild,
                valueField        :  'id',
                displayField      :  'name',
                width             :  200,
                height            :  100,
                allowBlank        :  true
        
            }, new Ext.form.Hidden({
                name              :  'statusId',
                id                :  'statusId'
            })
            ]
        }]
    }, {
        title: 'Title',
        layout: 'form',
        items: fieldTitle //see index.phtml
    }]
    var formOrder = new Ext.form.FormPanel({
        labelWidth: 80,
        name : 'formOrder',
        id : 'formOrder',
        autoScroll: true,
        //bodyStyle: 'padding: 5px',
        //defaults: {width: 200},
        border: false,
        autoHeight: true,
        /*reader: new Ext.data.JsonReader({
                root: 'data' 
            },
            ['name']     
        ),*/
        items: [{
            xtype: 'tabpanel',
            border: false,
            plain: true,
            activeTab: 0,
            deferredRender: false,
            layoutOnTabChange: true,
            defaults:{autoHeight:true, bodyStyle:'padding:10px'}, 
            items: tabs
        }]
    });
    
    
    
    var windowOrder = new Ext.Window({
        closeAction: 'hide',
        title: 'Order Status'.l(),
        width: 350,
        height: 350,
        constrainHeader: true,
        id: 'windowOrder',
        name : 'order',
        autoScroll: true,
        bodyStyle: 'background: white; padding-top: 7px',
        border: true,
        items: formOrder,  
        buttons: [{
            text: 'Save'.l(),
            handler: function () {
             Ext.getCmp('formOrder').getForm().submit({
                 url:  Axis.getUrl('sales_order-status/save'),
                 method : 'POST',
                 success : function(form, response) {
                    Ext.getCmp('grid-status').getStore().reload();
                    windowOrder.hide();
                    orderStatus.load();
                 }
             });
                
            }
        },{
            text: 'Cancel'.l(),
            handler: function(){
                windowOrder.hide();
            }
        } ]
    });

    windowOrder.on('hide', function(){
        Ext.getCmp('formOrder').getForm().clear();
    });
    
    Ext.getCmp('add-order-status-from').on('change', function(evt, elem, o) {
        //alert(elem);
        orderStatusChild.baseParams = {
            'parentId': elem
        };
        orderStatusChild.load();
        Ext.getCmp('multiselect').render();
    });
    
}, this);