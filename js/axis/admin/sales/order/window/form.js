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
    
    Order.form = new Ext.form.FormPanel({
        items: [Ext.getCmp('panel-form-order')],
        border: false,
        bodyStyle: 'padding: 5px 0 0;',
        reader: new Ext.data.JsonReader({
            idProperty: 'order.id',
            root: 'data'
        }, Ext.data.Record.create([
            {name: 'order[id]',                         type: 'int',     mapping: 'order.id'},
            {name: 'order[number]',                     type: 'string',  mapping: 'order.number'},
            {name: 'order[site_id]',                    type: 'int',     mapping: 'order.site_id'},
            {name: 'order[date_purchased_on]',          type: 'date',    mapping: 'order.date_purchased_on', dateFormat: 'Y-m-d H:i:s'},
            {name: 'order[status_name]',                type: 'string',  mapping: 'order.status_name'},
            {name: 'order[order_status_id]',            type: 'int',     mapping: 'order.order_status_id'},
            {name: 'order[currency]',                   type: 'string',  mapping: 'order.currency'},
            {name: 'order[ip_address]',                 type: 'string',  mapping: 'order.ip_address'},
            {name: 'order[customer_id]',                type: 'string',  mapping: 'order.customer_id',
                convert: function (v, record) {
                    //if guest
                    if ('0' === v) {
                        v = -1;//  'Checkout as Guest';
                    }
                    return v;
                }
            },
            {name: 'customer[firstname]',               type: 'string',  mapping: 'customer.firstname'},
            {name: 'customer[lastname]',                type: 'string',  mapping: 'customer.lastname'},
            {name: 'order[customer_email]',             type: 'string',  mapping: 'order.customer_email'},
            {name: 'customer[group_id]',                type: 'string',  mapping: 'customer.group_id'},
            {name: 'order[ip_address]',                 type: 'string',  mapping: 'order.ip_address'},
            {name: 'order[billing_firstname]',          type: 'string',  mapping: 'address.billing.firstname'},
            {name: 'order[billing_lastname]',           type: 'string',  mapping: 'address.billing.lastname'},
            {name: 'order[billing_company]',            type: 'string',  mapping: 'address.billing.company'},
            {name: 'order[billing_street_address]',     type: 'string',  mapping: 'address.billing.street_address'},
            {name: 'order[billing_suburb]',             type: 'string',  mapping: 'address.billing.suburb'},
            {name: 'order[billing_city]',               type: 'string',  mapping: 'address.billing.city'},
            {name: 'order[billing_country]',            type: 'int',     mapping: 'address.billing.country_id',
                convert: function(v, record) {
                    if (record.address.billing.country_id) {
                        return record.address.billing.country_id;
                    }
                    return null;
                }
            },
            {name: 'order[billing_postcode]',           type: 'string',  mapping: 'address.billing.postcode'},
            {name: 'order[billing_state]',              type: 'int',     mapping: 'address.billing.zone_id',
                convert: function (v, record) {
                    if (undefined !== record.address.billing.zone_id) {
                        return record.address.billing.zone_id;
                    }
                    return null;
                }
            },
            {name: 'order[billing_phone]',               type: 'string',  mapping: 'address.billing.phone'},
            {name: 'order[billing_fax]',                 type: 'string',  mapping: 'address.billing.fax'},

            {name: 'order[delivery_firstname]',          type: 'string', mapping: 'address.delivery.firstname'},
            {name: 'order[delivery_lastname]',           type: 'string', mapping: 'address.delivery.lastname'},
            {name: 'order[delivery_company]',            type: 'string', mapping: 'address.delivery.company'},
            {name: 'order[delivery_street_address]',     type: 'string', mapping: 'address.delivery.street_address'},
            {name: 'order[delivery_suburb]',             type: 'string', mapping: 'address.delivery.suburb'},
            {name: 'order[delivery_city]',               type: 'string', mapping: 'address.delivery.city'},
            {name: 'order[delivery_country]',            type: 'int',    mapping: 'address.delivery.country_id',
                convert: function(v, record) {
                    if (record.address.delivery.country_id) {
                        return record.address.delivery.country_id;
                    }
                    return null;
                }
            },
            {name: 'order[delivery_postcode]',           type: 'string', mapping: 'address.delivery.postcode'},
            {name: 'order[delivery_state]',              type: 'int',    mapping: 'address.delivery.zone_id',
                convert: function (v, record) {
                    if (undefined !== record.address.delivery.zone_id) {
                        return record.address.delivery.zone_id;
                    }
                    return null;
                }
            },
            {name: 'order[delivery_phone]',               type: 'string', mapping: 'address.delivery.phone'},
            {name: 'order[delivery_fax]',                 type: 'string', mapping: 'address.delivery.fax'},

            {name: 'order[payment_method_code]',          type: 'string', mapping: 'payment.code'},
            {name: 'order[payment_method]',               type: 'string', mapping: 'payment.name'},
            {name: 'order[shipping_method_code]',         type: 'string', mapping: 'shipping.code'},
            {name: 'order[shipping_method]',              type: 'string', mapping: 'shipping.name'},

            {name: 'totals[subtotal]',                    type: 'float',  mapping: 'totals.subtotal.value'},
            {name: 'totals[shipping]',                    type: 'float', /*this is stupid bug fix*/ mapping: 'totals.subtotal.value'
                ,convert: function (v, record) {
                    if (undefined == record.totals.shipping
                        || undefined == record.totals.shipping.value) {
                        
                        return 0;
                    }
                    return record.totals.shipping.value;
                }
            },
            {name: 'totals[tax]',                         type: 'float', /*this is stupid bug fix*/ mapping: 'totals.subtotal.value'
                ,convert: function (v, record) {
                    if (undefined == record.totals.tax
                        || undefined == record.totals.tax.value) {
                        
                        return 0;
                    }
                    return record.totals.tax.value;
                }
            },
            {name: 'totals[shipping_tax]',                type: 'float', /*this is stupid bug fix*/ mapping: 'totals.subtotal.value'
                ,convert: function (v, record) {
                    if (undefined == record.totals.shipping_tax
                        || undefined == record.totals.shipping_tax.value) {
                        
                        return 0;
                    }
                    return record.totals.shipping_tax.value;
                }
            },
            {name: 'order[order_total]',                  type: 'float',  mapping: 'order.order_total'}

        ]))
    });
    Order.form._config = {};

    Order.form.config = function() {
        return Order.form._config;
    };
    Order.form.setConfig = function(config) {
        Order.form._config = config;
    }
}, this);