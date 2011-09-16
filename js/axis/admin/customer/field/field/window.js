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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

var FieldWindow = {

    el: null,

    form: null,

    show: function() {
        FieldWindow.el.show();
    },

    hide: function() {
        FieldWindow.el.hide();
    },

    save: function(closeWindow) {
        FieldWindow.form.getForm().submit({
            url     : Axis.getUrl('account/field/save'),
            success : function(form, action) {
                FieldGrid.reload();
                if (closeWindow) {
                    FieldWindow.hide();
                    FieldWindow.form.getForm().clear();
                } else {
                    var response = Ext.decode(action.response.responseText);
                    Field.load(response.data.id);
                }
            },
            failure: function(form, action) {
                if (action.failureType == 'client') {
                    return;
                }
            }
        });
    }

};

Ext.onReady(function() {

    var fields = [
        {name: 'field[id]', type: 'int', mapping: 'field.id'},
        {name: 'field[name]', mapping: 'field.name'},
        {name: 'field[customer_field_group_id]', type: 'int', mapping: 'field.customer_field_group_id'},
        {name: 'field[field_type]', mapping: 'field.field_type'},
        {name: 'field[required]', mapping: 'field.required'},
        {name: 'field[sort_order]', mapping: 'field.sort_order'},
        {name: 'field[is_active]', mapping: 'field.is_active'},
        {name: 'field[customer_valueset_id]', mapping: 'field.customer_valueset_id'},
        {name: 'field[validator]', mapping: 'field.validator'},
        {name: 'field[axis_validator]', mapping: 'field.axis_validator'}
    ];
    for (var id in Axis.locales) {
        fields.push({
            name    : 'label[' + id + ']',
            mapping : 'label.lang_' + id
        });
    }

    // the next store is updated every time
    // after origin store will be loaded: 'store-fieldgroup' (group/grid.js)
    var fieldgroupStore = new Ext.data.JsonStore({
        idProperty  : 'id',
        fields      : [
            {name: 'id', type: 'int'},
            {name: 'name'},
            {name: 'is_active', type: 'int'},
            {name: 'sort_order', type: 'int'}
        ]
    });
    Ext.StoreMgr.get('store-fieldgroup').on('load', function(store, records, options) {
        fieldgroupStore.add(records);
    });

    var validatorCombo = Ext.getCmp('combobox-validator').cloneConfig({
        fieldLabel  : 'Validator'.l(),
        name        : 'field[validator]',
        hiddenName  : 'field[validator]',
        editable    : false,
        anchor      : '-10'
    });
    validatorCombo.setValue = validatorCombo.setValue.createSequence(function(v) {
        if (null === v) {
            validatorCombo.setValue('');
        }
    });

    // the next store is updated every time
    // after origin store will be loaded: 'store-valueset' (valueset/grid.js)
    var valuesetStore = new Ext.data.JsonStore({
        idProperty  : 'id',
        fields      : ['id', 'name']
    });
    Ext.StoreMgr.get('store-valueset').on('load', function(store, records, options) {
        valuesetStore.loadData([{'id': 0, 'name': 'None'.l()}], false);
        Ext.each(records, function(r) {
            valuesetStore.add(r.copy()); // need to copy to allow to modify them in origin store
        })
    });
    var valuesetCombo = new Ext.form.ComboBox({
        fieldLabel      : 'Valueset'.l(),
        name            : 'field[customer_valueset_id]',
        hiddenName      : 'field[customer_valueset_id]',
        mode            : 'local',
        editable        : false,
        initialValue    : 0,
        anchor          : '100%',
        triggerAction   : 'all',
        displayField    : 'name',
        store           : valuesetStore,
        forceSelection  : false,
        valueField      : 'id',
        mode            : 'local'
    });
    valuesetCombo.setValue = valuesetCombo.setValue.createSequence(function(v) {
        if (null === v) {
            valuesetCombo.setValue(0);
        }
    });

    FieldWindow.form = new Axis.FormPanel({
        bodyStyle   : 'padding: 10px 10px 0;',
        method      : 'post',
        reader      : new Ext.data.JsonReader({
            root        : 'data',
            idProperty  : 'field.id'
        }, fields),
        defaults    : {
            anchor: '-20'
        },
        items: [{
            fieldLabel  : 'Name'.l(),
            xtype       : 'textfield',
            name        : 'field[name]',
            allowBlank  : false
        }, {
            fieldLabel  : 'Title'.l(),
            xtype       : 'langset',
            name        : 'label',
            tpl         : '{self}[{language_id}]',
            allowBlank  : false
        }, {
            layout: 'column',
            border: false,
            defaults: {
                layout      : 'form',
                columnWidth : .5,
                border      : false
            },
            items: [{
                items: [new Ext.form.ComboBox({
                    allowBlank      : false,
                    fieldLabel      : 'Group'.l(),
                    triggerAction   : 'all',
                    displayField    : 'name',
                    store           : fieldgroupStore,
                    forceSelection  : false,
                    lazyRender      : true,
                    editable        : false,
                    valueField      : 'id',
                    mode            : 'local',
                    name            : 'field[customer_field_group_id]',
                    hiddenName      : 'field[customer_field_group_id]',
                    anchor          : '-10'
                })]
            }, {
                items: [{
                    allowBlank  : false,
                    fieldLabel  : 'Sort Order'.l(),
                    xtype       : 'textfield',
                    name        : 'field[sort_order]',
                    initialValue: 20,
                    anchor      : '100%'
                }]
            }]
        }, {
            layout: 'column',
            border: false,
            defaults: {
                layout      : 'form',
                columnWidth : .5,
                border      : false
            },
            items: [{
                items: [{
                    allowBlank  : false,
                    columns     : [100, 100],
                    fieldLabel  : 'Status'.l(),
                    name        : 'field[is_active]',
                    xtype       : 'radiogroup',
                    initialValue: 1,
                    items       : [{
                        boxLabel    : 'Enabled'.l(),
                        checked     : true,
                        name        : 'field[is_active]',
                        inputValue  : 1
                    }, {
                        boxLabel    : 'Disabled'.l(),
                        name        : 'field[is_active]',
                        inputValue  : 0
                    }]
                }]
            }, {
                labelWidth: 110,
                items: [{
                    allowBlank  : false,
                    columns     : [100, 100],
                    fieldLabel  : 'Required'.l(),
                    name        : 'field[required]',
                    xtype       : 'radiogroup',
                    initialValue: 0,
                    items: [{
                        boxLabel    : 'Yes'.l(),
                        checked     : true,
                        name        : 'field[required]',
                        inputValue  : 1
                    }, {
                        boxLabel    : 'No'.l(),
                        name        : 'field[required]',
                        inputValue  : 0
                    }]
                }]
            }]
        }, {
            layout: 'column',
            border: false,
            defaults: {
                layout      : 'form',
                columnWidth : .5,
                border      : false
            },
            items: [{
                items: [Ext.getCmp('combobox-fieldtype').cloneConfig({
                    fieldLabel  : 'Field type'.l(),
                    name        : 'field[field_type]',
                    value       : 'text',
                    hiddenName  : 'field[field_type]',
                    editable    : false,
                    anchor      : '-10',
                    initialValue: 'text'
                })]
            }, {
                items: [valuesetCombo]
            }]
        }, {
            layout: 'column',
            border: false,
            defaults: {
                layout      : 'form',
                columnWidth : .5,
                border      : false
            },
            items: [{
                items: [validatorCombo]
            }, {
                items: [{
                    fieldLabel  : 'Axis validator'.l(),
                    xtype       : 'textfield',
                    name        : 'field[axis_validator]',
                    anchor      : '100%'
                }]
            }]
        }, {
            xtype   : 'hidden',
            name    : 'field[id]'
        }]
    });

    FieldWindow.el = new Axis.Window({
        height  : 350,
        title   : 'Field'.l(),
        items   : FieldWindow.form,
        buttons : [{
            icon    : Axis.skinUrl + '/images/icons/database_save.png',
            text    : 'Save'.l(),
            handler : function() {
                FieldWindow.save(true);
            }
        }, {
            icon    : Axis.skinUrl + '/images/icons/database_save.png',
            text    : 'Save & Continue Edit'.l(),
            handler : function() {
                FieldWindow.save(false);
            }
        }, {
            icon    : Axis.skinUrl + '/images/icons/cancel.png',
            text    : 'Cancel'.l(),
            handler : FieldWindow.hide
        }]
    });
});
