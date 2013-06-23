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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

Ext.onReady(function(){

    var product = new Ext.ux.data.CalcRecord.create([
        {name: 'attributes'},
        {name: 'backorder',            type: 'int'},
        {name: 'final_price',          type: 'float'},
        {name: 'id',                   type: 'int'},
        {name: 'order_id',             type: 'int'},
        {name: 'product_id',           type: 'int'},
        {name: 'name',                 type: 'string'},
        {name: 'price',                type: 'float'},
        {name: 'final_weight',         type: 'float'},
        {name: 'quantity',             type: 'float'},
        {name: 'sku',                  type: 'string'},
        {name: 'tax_rate',             type: 'float'},
        {
            name: 'product_subtotal',
            type: 'float',
            dependencies: ['final_price', 'quantity'],
            notDirty: true,
            calc: function(record) {
                return parseFloat(parseFloat(
                    record.get('final_price') * record.get('quantity')
                ).toFixed(2));
            }
        }, {
            name: 'product_subweight',
            type: 'float',
            dependencies: ['final_weight', 'quantity'],
            notDirty: true,
            calc: function(record) {
                return parseFloat(parseFloat(
                    record.get('final_weight') * record.get('quantity')
                ).toFixed(2));
            }
        }, {
            name: 'product_subtax',
            type: 'float',
            dependencies: ['tax_rate', 'quantity', 'final_price'],
            notDirty: true,
            calc: function(record) {
                return parseFloat(parseFloat(
                    record.get('tax_rate')
                        * record.get('quantity')
                        * record.get('final_price') / 100
                    ).toFixed(2));
            }
        },
        {name: 'variation_id', type: 'int'},
        {name: 'remove', type: 'int'}
    ]);

    var ds = new Ext.data.Store({
        storeId: 'storeGridProducts',
        reader: new Ext.data.JsonReader({
            id: 'id'
        }, product),
        mode: 'local',
        getData: function() {
            var items = this.data.items;

            if (!items.length) {
                return;
            }

            var data = {};
            for (var i = items.length - 1; i >= 0; i--) {
                data[items[i]['id']] = items[i]['data'];
            }

            return Ext.encode(data);
        }
    });
    ds.reloadDepend = function(store) {
//        store = store || this;
        var form = Order.form.getForm();
        form.findField('totals[subtotal]').setValue(
            store.sum('product_subtotal').toFixed(2)
        );
        if (true === totalsConfig.tax) {
            form.findField('totals[tax]').setValue(
                store.sum('product_subtax').toFixed(2)
            );
        }
        Ext.StoreMgr.lookup('storeShippingMethod').reloadList();
        Ext.StoreMgr.lookup('storePaymentMethod').reloadList();
    };

    var expander = new Ext.grid.RowExpander({
        listeners: {
            beforeexpand: function(expander, record, body, rowIndex) {
                if (!this.tpl) {
                    this.tpl = new Ext.Template();
                }
                var attributes = record.get('attributes');
                if (typeof attributes !== 'object') {
                    this.tpl.set('');
                    return;
                }

                var html = '<div class="product-attributes">';
                Ext.each(attributes, function(attribute) {
                    html += String.format(
                        '<p class="product-attribute"><label>{0}</label><span>{1}</span></p>',
                        attribute.product_option,
                        attribute.product_option_value
                    );
                }, this);
                html += '</div>';
                this.tpl.set(html);
            }
        }
    });

    var cm = new Ext.grid.ColumnModel([
        expander, {
            header: 'Id'.l(),
            dataIndex: 'id',
            menuDisabled: true
        }, {
            header: 'Name'.l(),
            dataIndex: 'name',
            id: 'name',
            width: 300,
            menuDisabled: true
        }, {
            header: 'SKU'.l(),
            dataIndex: 'sku',
            menuDisabled: true
        }, {
            align: 'right',
            header: 'Price'.l(),
            dataIndex: 'final_price',
            width: 90,
            menuDisabled: true,
            editor: new Ext.form.NumberField({
                allowBlank: false,
                allowNegative: false,
                maxValue: 100000
            })
        }, {
            align: 'right',
            header: 'Quantity'.l(),
            dataIndex: 'quantity',
            menuDisabled: true,
            width: 60,
            editor: new Ext.form.NumberField({
                allowBlank: false,
                allowNegative: false,
                maxValue: 100000
                // @todo qty validator (ajax request)
//                ,validator: function(value) {
//                    return true;
//                }
            })
        }, {
            align: 'right',
            header: 'Tax'.l(),
            dataIndex: 'product_subtax',
            menuDisabled: true,
            width: 60,
            hidden: !totalsConfig.tax,
            editor: new Ext.form.NumberField({
                allowBlank: false,
                allowNegative: false,
                maxValue: 100000
            })
        }, {
            align: 'right',
            header: 'Subtotal'.l(),
            width: 100,
            dataIndex: 'product_subtotal',
            menuDisabled: true
        }
    ])
    cm.defaultSortable = true;

    var grid = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'name',
        width: 'auto',
        cm: cm,
        ds: ds,
        id: 'grid-products',
        autoHeight: true,
        plugins: [expander],
        bodyStyle: 'margin-bottom: 7px',
        border: true,
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler: function() {
                Ext.getCmp('grid-add-products').getStore().reload();
                Ext.getCmp('windowAddProductOrder').show();
            }
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: function() {
                var selectedItems = grid.getSelectionModel().getSelections();
                if (!selectedItems.length || !confirm('Are you sure?'.l())) {
                    return;
                }
                for (var i = 0; i < selectedItems.length; i++) {
                    grid.getStore().remove(selectedItems[i]);
                }

                ds.reloadDepend(ds);
            }
        }]
    });

    ds.on('add', ds.reloadDepend);
    ds.on('load', function(store, records, options) {
        Ext.StoreMgr.lookup('storeShippingMethod').reloadList();
        Ext.StoreMgr.lookup('storePaymentMethod').reloadList();
    });
    ds.on('update', ds.reloadDepend);

}, this);
