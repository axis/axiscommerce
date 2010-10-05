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
        {name: 'product[price]', mapping: 'product.price'},
        {name: 'product[cost]', mapping: 'product.cost'},
        {name: 'product[tax_class_id]', mapping: 'product.tax_class_id'},
        {name: 'special[price]', mapping: 'special.price'},
        {name: 'special[from_date_exp]', mapping: 'special.from_date_exp', type: 'date', dateFormat: 'Y-m-d'},
        {name: 'special[to_date_exp]', mapping: 'special.to_date_exp', type: 'date', dateFormat: 'Y-m-d'}
    );
    
    ProductWindow.addTab({
        title: 'Price'.l(),
        bodyStyle: 'padding: 10px',
        defaults: {
            anchor: '-20',
            border: false
        },
        items: [{
            anchor: '100%',
            layout: 'column',
            defaults: {
                border: false,
                columnWidth: '.5',
                layout: 'form'
            },
            items: [{
                items: [{
                    allowBlank: false,
                    allowNegative: false,
                    anchor: '-5',
                    fieldLabel: 'Price'.l(),
                    name: 'product[price]',
                    xtype: 'numberfield'
                }]
            }, {
                items: [{
                    allowNegative: false,
                    anchor: '100%',
                    fieldLabel: 'Cost'.l(),
                    initialValue: 0,
                    name: 'product[cost]',
                    xtype: 'numberfield'
                }]
            }]
        }, {
            anchor: '100%',
            layout: 'column',
            defaults: {
                border: false,
                columnWidth: '.5',
                layout: 'form'
            },
            items: [{
                items: [{
                    allowBlank: false,
                    anchor: '-5',
                    fieldLabel: 'Tax Class'.l(),
                    displayField: 'name',
                    mode: 'local',
                    name: 'product[tax_class_id]',
                    hiddenName: 'product[tax_class_id]',
                    store: new Ext.data.JsonStore({
                        data: taxClasses,
                        fields: [
                            {name: 'id'},
                            {name: 'name'}
                        ]
                    }),
                    triggerAction: 'all',
                    valueField: 'id',
                    value: null,
                    initialValue: null,
                    xtype: 'combo'
                }]
            }]
        }, {
            anchor: '100%',
            layout: 'column',
            defaults: {
                border: false,
                columnWidth: '.5',
                layout: 'form'
            },
            items: [{
                items: [{
                    anchor: '-5',
                    fieldLabel: 'Special Price'.l(),
                    name: 'special[price]',
                    xtype: 'numberfield'
                }]
            }]
        }, {
            anchor: '100%',
            layout: 'column',
            defaults: {
                border: false,
                columnWidth: '.5',
                layout: 'form'
            },
            items: [{
                items: [{
                    anchor: '-5',
                    fieldLabel: 'Special Price From'.l(),
                    name: 'special[from_date_exp]',
                    xtype: 'datefield'
                }]
            }, {
                items: [{
                    anchor: '100%',
                    fieldLabel: 'Special Price To'.l(),
                    name: 'special[to_date_exp]',
                    xtype: 'datefield'
                }]
            }]
        }]
    }, 40);
    
});
