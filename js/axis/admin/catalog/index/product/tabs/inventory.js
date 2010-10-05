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
        {name: 'product[sku]', mapping: 'product.sku'},
        {name: 'product[date_available]', mapping: 'product.date_available', type: 'date', dateFormat: 'Y-m-d'},
        {name: 'product[quantity]', mapping: 'product.quantity', type: 'int'},
        {name: 'product[weight]', mapping: 'product.weight', type: 'float'},
        {name: 'stock[manage]', mapping: 'stock.manage', type: 'int'},
        {name: 'stock[backorder]', mapping: 'stock.backorder', type: 'int'},
        {name: 'stock[in_stock]', mapping: 'stock.in_stock', type: 'int'},
        {name: 'stock[decimal]', mapping: 'stock.decimal', type: 'int'},
        {name: 'stock[notify_qty]', mapping: 'stock.notify_qty', type: 'float'},
        {name: 'stock[min_qty]', mapping: 'stock.min_qty', type: 'float'},
        {name: 'stock[min_qty_allowed]', mapping: 'stock.min_qty_allowed', type: 'float'},
        {name: 'stock[max_qty_allowed]', mapping: 'stock.max_qty_allowed', type: 'float'}
    );

    ProductWindow.addTab({
        title: 'Inventory'.l(),
        bodyStyle: 'padding: 10px',
        defaults: {
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
                defaults: {
                    anchor: '-5'
                },
                items: [{
                    allowBlank: false,
                    fieldLabel: 'SKU'.l(),
                    name: 'product[sku]',
                    xtype: 'textfield'
                }]
            }, {
                defaults: {
                    anchor: '100%'
                },
                items: [{
                    allowBlank: false,
                    fieldLabel: 'Manage stock'.l(),
                    name: 'stock[manage]',
                    xtype: 'radiogroup',
                    initialValue: 1,
                    items: [{
                        boxLabel: 'Yes'.l(),
                        checked: true,
                        name: 'stock[manage]',
                        inputValue: 1
                    }, {
                        boxLabel: 'No'.l(),
                        name: 'stock[manage]',
                        inputValue: 0
                    }]
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
                defaults: {
                    anchor: '-5'
                },
                items: [{
                    fieldLabel: 'Backorders'.l(),
                    name: 'stock[backorder]',
                    hiddenName: 'stock[backorder]',
                    xtype: 'combo',
                    displayField: 'title',
                    editable: false,
                    mode: 'local',
                    triggerAction: 'all',
                    value: 0,
                    initialValue: 0,
                    valueField: 'value',
                    store: new Ext.data.ArrayStore({
                        data: [
                            [0, 'Disallowed'.l()],
                            [1, 'Allowed'.l()],
                            [2, 'Allowed (notify customer)'.l()]//,
                            //[3, 'Preorder'.l()]
                        ],
                        fields: ['value', 'title'],
                        idIndex: 0
                    })
                }]
            }, {
                defaults: {
                    anchor: '100%'
                },
                items: [{
                    allowBlank: false,
                    fieldLabel: 'Stock availability'.l(),
                    name: 'stock[in_stock]',
                    xtype: 'radiogroup',
                    initialValue: 1,
                    items: [{
                        boxLabel: 'In stock'.l(),
                        checked: true,
                        name: 'stock[in_stock]',
                        inputValue: 1
                    }, {
                        boxLabel: 'Out of stock'.l(),
                        name: 'stock[in_stock]',
                        inputValue: 0
                    }]
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
                defaults: {
                    anchor: '-5'
                },
                items: [{
                    allowBlank: false,
                    allowNegative: false,
                    fieldLabel: 'Weight'.l(),
                    name: 'product[weight]',
                    value: 0,
                    initialValue: 0,
                    xtype: 'numberfield'
                }]
            }, {
                defaults: {
                    anchor: '100%'
                },
                items: [{
                    allowBlank: false,
                    fieldLabel: 'Q-ty uses decimal'.l(),
                    name: 'stock[decimal]',
                    xtype: 'radiogroup',
                    initialValue: 0,
                    items: [{
                        boxLabel: 'Yes'.l(),
                        name: 'stock[decimal]',
                        inputValue: 1
                    }, {
                        boxLabel: 'No'.l(),
                        checked: true,
                        name: 'stock[decimal]',
                        inputValue: 0
                    }]
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
                defaults: {
                    anchor: '-5'
                },
                items: [{
                    fieldLabel: 'Date available'.l(),
                    name: 'product[date_available]',
                    xtype: 'datefield'
                }]
            }/*, {
                defaults: {
                    anchor: '100%'
                },
                items: [{
                    allowBlank: false,
                    fieldLabel: 'Preorder'.l(),
                    name: 'stock[preorder]',
                    xtype: 'radiogroup',
                    items: [{
                        boxLabel: 'Allowed'.l(),
                        name: 'stock[preorder]',
                        inputValue: 1
                    }, {
                        boxLabel: 'Disallowed'.l(),
                        checked: true,
                        name: 'stock[preorder]',
                        inputValue: 0
                    }]
                }]
            }*/]
        }, {
            anchor: '100%',
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
                items: [{
                    allowBlank: false,
                    allowNegative: false,
                    fieldLabel: 'Quantity'.l(),
                    name: 'product[quantity]',
                    initialValue: '',
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
                defaults: {
                    anchor: '-5'
                },
                items: [{
                    fieldLabel: 'Notify for q-ty below'.l(),
                    name: 'stock[notify_qty]',
                    value: 1,
                    initialValue: 1,
                    xtype: 'numberfield'
                }]
            }, {
                defaults: {
                    anchor: '100%'
                },
                items: [{
                    fieldLabel: 'Minimum q-ty to be out of stock'.l(),
                    name: 'stock[min_qty]',
                    value: 0,
                    initialValue: 0,
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
                defaults: {
                    anchor: '-5'
                },
                items: [{
                    fieldLabel: 'Minimum q-ty allowed to be in cart'.l(),
                    name: 'stock[min_qty_allowed]',
                    value: 1,
                    initialValue: 1,
                    xtype: 'numberfield'
                }]
            }, {
                defaults: {
                    anchor: '100%'
                },
                items: [{
                    fieldLabel: 'Maximum q-ty allowed to be in cart'.l(),
                    name: 'stock[max_qty_allowed]',
                    xtype: 'numberfield'
                }]
            }]
        }]
    }, 30);

});
