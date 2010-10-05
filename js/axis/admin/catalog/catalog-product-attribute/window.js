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
    
    Ext.QuickTips.init();
    
    Ext.form.Field.prototype.msgTarget = 'qtip';
    
    var record = [
        {name: 'option[id]',            mapping: 'id',          type: 'int'},
        {name: 'option[code]',          mapping: 'code'},
        {name: 'option[input_type]',    mapping: 'input_type',  type: 'int'},
        {name: 'option[sort_order]',    mapping: 'sort_order',  type: 'int'},
        {name: 'option[searchable]',    mapping: 'searchable',  type: 'int'},
        {name: 'option[comparable]',    mapping: 'comparable',  type: 'int'},
        {name: 'option[languagable]',   mapping: 'languagable', type: 'int'},
        {name: 'option[filterable]',    mapping: 'filterable',  type: 'int'},
        {name: 'option[visible]',       mapping: 'visible',     type: 'int'},
        {name: 'option[valueset_id]',   mapping: 'valueset_id', type: 'int'}
    ];
    
    for (var id in Axis.locales) {
        record.push(
            {name: 'option[text][' + id + '][name]', mapping: 'text.lang_' + id + '.name'},
            {name: 'option[text][' + id + '][description]', mapping: 'text.lang_' + id + '.description'}
        );
    }
    
    Attribute.inputTypeStore = new Ext.data.Store({
        data: [
            [0, 'Select'],
            [1, 'String'],
            [2, 'Radio'],
            [3, 'Checkbox'],
            [4, 'Textarea'],
            [5, 'File']
        ],
        reader: new Ext.data.ArrayReader({
            idIndex: 0
        }, [
            {name: 'id', type: 'int'}, 
            {name: 'title'}
        ])
    });
    
    var inputTypeCombo = new Ext.form.ComboBox({
        fieldLabel: 'Type'.l(),
        hiddenName: 'option[input_type]',
        name: 'option[input_type]',
        displayField: 'title',
        valueField: 'id',
        mode: 'local',
        triggerAction: 'all',
        editable: false,
        hiddenValue: 0,
        store: Attribute.inputTypeStore
    });
    
    Ext.intercept(inputTypeCombo, 'setValue', function(value) {
        var form = Attribute.form.getForm();
        if (value === 1 || value === 4 || value === 5) { //text and file attribute
            form.findField('option[valueset_id]').disable();
            form.findField('option[languagable]').enable();
            form.findField('option[filterable]').disable();
        } else {
            form.findField('option[valueset_id]').enable();
            form.findField('option[languagable]').disable();
            form.findField('option[filterable]').enable();
        }
    });
    
    Attribute.form = new Axis.FormPanel({
        bodyStyle: 'padding: 10px;',
        defaults: {
            anchor: '-20',
            initialValue: '',
            xtype: 'textfield'
        },
        reader: new Ext.data.JsonReader({
                root: 'data',
                idProperty: 'id'
            }, record
        ),
        items: [{
            allowBlank: false,
            fieldLabel: 'Code'.l(),
            maxLength: 32,
            name: 'option[code]'
        }, {
            allowBlank: false,
            fieldLabel: 'Name'.l(),
            name: 'option[text][name]',
            tplName: 'option[text][{language_id}][name]',
            xtype: 'langset'
        }, {
            fieldLabel: 'Description'.l(),
            defaultType: 'textarea',
            height: 50,
            name: 'option[text][description]',
            tplName: 'option[text][{language_id}][description]',
            xtype: 'langset'
        }, 
        inputTypeCombo, {
            fieldLabel: 'Valueset'.l(),
            name: 'option[valueset_id]',
            hiddenName: 'option[valueset_id]',
            displayField: 'name',
            valueField: 'id',
            triggerAction: 'all',
            editable: false,
            mode: 'local',
            emptyText: 'None'.l(),
            store: new Ext.data.Store({
                autoLoad: true,
                url: Axis.getUrl('catalog_product-option-valueset/list-sets'),
                reader: new Ext.data.JsonReader({
                    root: 'data',
                    idProperty: 'id'
                }, [
                    {name: 'id'},
                    {name: 'name'}
                ])
            }),
            xtype: 'combo'
        }, {
            allowNegative: false,
            fieldLabel: 'Sort Order'.l(),
            initialValue: 10,
            name: 'option[sort_order]',
            xtype: 'numberfield',
            maxValue: 254
        }, {
            anchor: '100%',
            border: false,
            layout: 'column',
            xtype: 'panel',
            defaults: {
                border: false,
                columnWidth: '.5',
                layout: 'form'
            },
            items: [{
                defaults: {
                    anchor: '-5'
                },
                labelWidth: 210,
                items: [/*{
                    fieldLabel: 'Use in Search Indexes'.l(),
                    name: 'option[searchable]',
                    xtype: 'checkbox'
                },*/ {
                    fieldLabel: 'Use in Catalog Filters'.l(),
                    checked: true,
                    initialValue: 1,
                    name: 'option[filterable]',
                    xtype: 'checkbox'
                }, {
                    fieldLabel: 'Visible on Frontend'.l(),
                    checked: true,
                    initialValue: 1,
                    name: 'option[visible]',
                    xtype: 'checkbox'
                }]
            }, {
                defaults: {
                    anchor: '100%'
                },
                labelWidth: 210,
                items: [{
                    fieldLabel: 'Use in Product Comparation'.l(),
                    name: 'option[comparable]',
                    initialValue: 0,
                    xtype: 'checkbox'
                }, {
                    fieldLabel: 'Value of this attribute depends on language'.l(),
                    name: 'option[languagable]',
                    initialValue: 0,
                    xtype: 'checkbox'
                }]
            }]
        }, {
            name: 'option[id]',
            xtype: 'hidden',
            initialValue: 0
        }]
    });
    
    Attribute.window = new Axis.Window({
        width: 550,
        height: 450,
        items: [
            Attribute.form
        ],
        buttons: [{
            icon: Axis.skinUrl + '/images/icons/database_save.png',
            text: 'Save'.l(),
            handler: function() {
                Attribute.form.getForm().submit({
                    url: Axis.getUrl('catalog_product-attributes/save'),
                    success: function(form, action) {
                        Attribute.window.hide();
                        Attribute.grid.getStore().reload();
                    }
                });
            }
        }, {
            icon: Axis.skinUrl + '/images/icons/cancel.png',
            text: 'Cancel'.l(),
            handler: function(){
                Attribute.window.hide();
            }
        }]
    });
});