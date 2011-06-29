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

Ext.onReady(function() {

    var defaults = {
        plugins: inlineField,
        anchor: '-10',
//        submitValue: false,
        labelStyle: 'text-align: right'
    };
    var cmpTotalSubtotal = new Ext.form.TextField(Ext.applyIf({
        fieldLabel: 'Subtotal'.l(),
        readOnly: true,
        name: 'totals[subtotal]'

    }, defaults));
//    var config = totalsConfig;
    var cmpTotalShipping = new Ext.form.TextField(Ext.applyIf({
        fieldLabel: 'Shipping'.l(),
//        readOnly: !config.shipping,
        name: 'totals[shipping]'
    }, defaults));
    var cmpTotalTax = new Ext.form.TextField(Ext.applyIf({
        fieldLabel: 'Tax'.l(),
//        readOnly: !config.tax,
        name: 'totals[tax]'
    }, defaults));
    var cmpTotalShippingTax = new Ext.form.TextField(Ext.applyIf({
        fieldLabel: 'Shipping Tax'.l(),
//        readOnly: !config.shippingTax,
        name: 'totals[shipping_tax]'
    }, defaults));

    var cmpTotal = new Ext.form.TextField(Ext.applyIf({
        fieldLabel: 'Total'.l(),
        readOnly: true,
        name: 'order[order_total]'
    }, defaults));

    Order.form.boxSubtotalInfo = {
        id: 'box-subtotal-info',
        border: false,
        labelWidth: 205,
        items: [
            cmpTotalSubtotal,
            cmpTotalShipping, 
            cmpTotalTax,
            cmpTotalShippingTax, 
            cmpTotal
        ],

        recalculateTotal: function() {
            var subtotal    = cmpTotalSubtotal.getValue()    || 0;
            var shipping    = cmpTotalShipping.getValue()    || 0;
            var tax         = cmpTotalTax.getValue()         || 0;
            var shippingTax = cmpTotalShippingTax.getValue() || 0;

            cmpTotal.setValue(
                parseFloat(
                    parseFloat(subtotal)
                    + parseFloat(shipping)
                    + parseFloat(tax)
                    + parseFloat(shippingTax)
                ).toFixed(2)
            );
        }
    };

    cmpTotalSubtotal.on('change', Order.form.boxSubtotalInfo.recalculateTotal);
    cmpTotalSubtotal.setValue = cmpTotalSubtotal.setValue.createSequence(
        Order.form.boxSubtotalInfo.recalculateTotal
    );
        
    cmpTotalShipping.on('change', Order.form.boxSubtotalInfo.recalculateTotal);
    cmpTotalShipping.setValue = cmpTotalShipping.setValue.createSequence(
        Order.form.boxSubtotalInfo.recalculateTotal
    );
    
    cmpTotalTax.on('change', Order.form.boxSubtotalInfo.recalculateTotal);
    cmpTotalTax.setValue = cmpTotalTax.setValue.createSequence(
        Order.form.boxSubtotalInfo.recalculateTotal
    );
        
    cmpTotalShippingTax.on('change', Order.form.boxSubtotalInfo.recalculateTotal);
    cmpTotalShippingTax.setValue = cmpTotalShippingTax.setValue.createSequence(
        Order.form.boxSubtotalInfo.recalculateTotal
    );
    
}, this);