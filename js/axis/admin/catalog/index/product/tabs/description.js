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

Ext.onReady(function() {

    ProductWindow.formFields.push(
        {name: 'product_id', mapping: 'product.id', type:'int'},
        {name: 'product[is_active]', mapping: 'product.is_active', type: 'int'},
        {name: 'product[new_from]', mapping: 'product.new_from', type: 'date', dateFormat: 'Y-m-d h:i:s'},
        {name: 'product[new_to]', mapping: 'product.new_to', type: 'date', dateFormat: 'Y-m-d h:i:s'},
        {name: 'product[featured_from]', mapping: 'product.featured_from', type: 'date', dateFormat: 'Y-m-d h:i:s'},
        {name: 'product[featured_to]', mapping: 'product.featured_to', type: 'date', dateFormat: 'Y-m-d h:i:s'},
        {name: 'product[manufacturer_id]', mapping: 'product.manufacturer_id', type: 'int'}
    );

    for (var id in Axis.languages) {
        ProductWindow.formFields.push({
            name: 'description[' + id + '][name]',
            mapping: 'description.lang_' + id + '.name'
        }, {
            name: 'description[' + id + '][description]',
            mapping: 'description.lang_' + id + '.description'
        }, {
            name: 'description[' + id + '][short_description]',
            mapping: 'description.lang_' + id + '.short_description'
        });
    }

    ProductWindow.addTab({
        title: 'Description'.l(),
        bodyStyle: 'padding: 10px',
        defaults: {
            anchor: '-20',
            border: false
        },
        items: [{
            allowBlank: false,
            anchor: '100%',
            fieldLabel: 'Name'.l(),
            tpl: '{self_plain}[{language_id}][{self_nested}]',
            name: 'description[name]',
            xtype: 'langset'
        }, {
            defaultType: 'textarea',//'htmleditor',
            fieldLabel: 'Description'.l(),
            height: 150,
            name: 'description[description]',
            xtype: 'langset'
        }, {
            defaultType: 'textarea',//'htmleditor',
            fieldLabel: 'Short Description'.l(),
            height: 70,
            name: 'description[short_description]',
            xtype: 'langset'
        }, {
            fieldLabel: 'Manufacturer'.l(),
            displayField: 'name',
            mode: 'local',
            name: 'product[manufacturer_id]',
            hiddenName: 'product[manufacturer_id]',
            value: null,
            initialValue: null,
            store: new Ext.data.JsonStore({
                data: manufacturers,
                fields: [
                    {name: 'id'},
                    {name: 'name'}
                ]
            }),
            triggerAction: 'all',
            valueField: 'id',
            xtype: 'combo'
        }, {
            allowBlank: false,
            columns: [100, 100],
            fieldLabel: 'Status'.l(),
            name: 'product[is_active]',
            xtype: 'radiogroup',
            value: 1,
            initialValue: 1,
            items: [{
                boxLabel: 'Enabled'.l(),
                checked: true,
                name: 'product[is_active]',
                inputValue: 1
            }, {
                boxLabel: 'Disabled'.l(),
                name: 'product[is_active]',
                inputValue: 0
            }]
        }, {
            layout: 'column',
            defaults: {
                border: false,
                columnWidth: '.5',
                layout: 'form'
            },
            items: [{
                defaults: {
                    anchor: '-5'
                },
                labelWidth: 120,
                items: [{
                    fieldLabel: 'Set As New From'.l(),
                    name: 'product[new_from]',
                    xtype: 'datefield'
                }, {
                    fieldLabel: 'Set As New To'.l(),
                    name: 'product[new_to]',
                    xtype: 'datefield'
                }]
            }, {
                defaults: {
                    anchor: '100%'
                },
                labelWidth: 120,
                items: [{
                    fieldLabel: 'Set As Featured From'.l(),
                    name: 'product[featured_from]',
                    xtype: 'datefield'
                }, {
                    fieldLabel: 'Set As Featured To'.l(),
                    name: 'product[featured_to]',
                    xtype: 'datefield'
                }]
            }]
        }, {
            initialValue: 0,
            name: 'product_id',
            xtype: 'hidden'
        }]
    }, 10);
});
