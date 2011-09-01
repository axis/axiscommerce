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

Ext.onReady(function(){
    
    Ext.QuickTips.init();
    
    var tabs = [{
        title: 'General'.l(),
        layout:'form',
        items: [{
            layout: 'column',
            border: false,
            items: [{
                columnWidth: .5,
                border: false,
                layout: 'form',
                items: [{
                    fieldLabel: 'Name'.l(),
                    xtype: 'textfield',
                    anchor: '95%',
                    name: 'data[name]',
                    allowBlank:false
                }, Ext.getCmp('type-combo').cloneConfig({
                    fieldLabel: 'Field type'.l(),
                    name: 'data[field_type]',
                    value: 'text',
                    hiddenName: 'data[field_type]',
                    editable: false,
                    anchor: '95%'
                }), {
                    fieldLabel: 'Sort Order'.l(),
                    xtype: 'textfield',
                    anchor: '95%',
                    name: 'data[sort_order]'
                }, {
                    fieldLabel: 'Status'.l(),
                    xtype: 'checkbox',
                    anchor: '95%',
                    name: 'data[is_active]'
                }, {
                    fieldLabel: 'Required'.l(),
                    xtype: 'checkbox',
                    anchor: '95%',
                    name: 'data[required]'
                }, {
                    xtype: 'hidden',
                    name: 'data[id]',
                    value: 'new'
                }]
            }, {
                columnWidth: .5,
                border: false,
                layout: 'form',
                items: [
                Ext.getCmp('group-combo').cloneConfig({
                    fieldLabel: 'Group'.l(),
                    name: 'data[customer_field_group_id]',
                    hiddenName: 'data[customer_field_group_id]',
                    editable: false,
                    allowBlank: false,
                    anchor: '95%'
                }), 
                Ext.getCmp('value-set-combo').cloneConfig({
                    fieldLabel: 'Valueset'.l(),
                    name: 'data[customer_valueset_id]',
                    hiddenName: 'data[customer_valueset_id]',
                    mode: 'local',
                    editable: false,
                    anchor: '95%'
                }),
                Ext.getCmp('validator-combo').cloneConfig({
                    fieldLabel: 'Validator'.l(),
                    name: 'data[validator]',
                    hiddenName: 'data[validator]',
                    editable: false,
                    anchor: '95%',
                    value: ''
                })]
            }]
        }, {
            fieldLabel: 'Axis validator'.l(),
            xtype: 'textfield',
            anchor: '95%',
            name: 'data[axis_validator]'
        }]
    }, {
        title: 'Title'.l(),
        layout: 'form',
        items: fieldTitle //see index.phtml
    }]
    
    var fieldForm = new Ext.form.FormPanel({
        id: 'fieldForm',
        labelWidth: 60,
        border: false,
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
    })
    
    var fieldEdit = new Ext.Window({
        title: 'Field'.l(),
        id: 'fieldEditWindow',
        closeAction: 'hide',
        constrainHeader: true,
        autoScroll: true,
        width: 550,
        height: 300,
        bodyStyle: 'background: white; padding-top: 7px',
        items: fieldForm,
        buttons: [{
            text: 'Save'.l(),
            handler: save
        }, {
            text: 'Cancel'.l(),
            handler: function(){
                fieldEdit.hide();
            }
        }]
    })
})

function save(){
    Ext.getCmp('fieldForm').getForm().submit({
        url: Axis.getUrl('account/field/save-field'),
        success: function(){
            Ext.getCmp('fieldEditWindow').hide();
            Ext.getCmp('grid-fields').store.reload();
        },
        failure: function(){
            Ext.Msg.show({
                buttons: Ext.Msg.OK,
                title: 'Error'.l(),
                modal: false
            });
        }
    })
}
