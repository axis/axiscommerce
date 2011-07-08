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

Ext.onReady(function(){
    ////////////////////////////////////////////////////////////////////////////
    ///// tab
    ////////////////////////////////////////////////////////////////////////////
    new Ext.Panel({
        id: 'tab-order-info',
        title: 'Order Info'.l(),
        defaults: {
            //border: false
        },
        border: false,
        items: [
            myRow(Order.form.boxGeneral, Order.form.boxCustomer),
            myRow(Order.form.boxBilling, Order.form.boxDelivery),
            Ext.getCmp('grid-products'),
            myRow(Order.form.boxPaymentMethod, Order.form.boxShippingMethod),
            myRow(Order.form.boxStatusInfo, Order.form.boxSubtotalInfo),
            myRow({
                fieldLabel: 'Customer comment'.l(),
                name: 'order[customer_comment]',
                xtype: 'textarea',
                anchor: '100%'
            }, {
                fieldLabel: 'Admin comment'.l(),
                name: 'order[admin_comment]',
                xtype: 'textarea',
                anchor: '100%'
            }), {
                xtype: 'hidden',
                name: 'order[id]'
            }
        ]
    });

}, this);