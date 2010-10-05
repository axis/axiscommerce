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

var OrderGrid = {

    /**
     * @param {Axis.grid.EditorGridPanel} el
     */
    el: null,

    clearData: function() {
        OrderGrid.el.store.loadData([]);
    },

    loadData: function(data) {
        OrderGrid.el.store.loadData(data.order);
    }
};

Ext.onReady(function() {

    var ds = new Ext.data.Store({
        mode: 'local',
        pruneModifiedRecords: true,
        reader: new Ext.data.JsonReader({
            idProperty: 'id',
            fields: [
                {name: 'id', type: 'int'},
                {name: 'status'},
                {name: 'product'},
                {name: 'number'},
                {name: 'customer_email'},
                {name: 'delivery_firstname'},
                {name: 'delivery_lastname'},
                {name: 'delivery_phone'},
                {name: 'delivery_fax'},
                {name: 'delivery_company'},
                {name: 'delivery_street_address'},
                {name: 'delivery_suburb'},
                {name: 'delivery_city'},
                {name: 'delivery_postcode'},
                {name: 'delivery_state'},
                {name: 'delivery_country'},
                {name: 'billing_firstname'},
                {name: 'billing_lastname'},
                {name: 'billing_phone'},
                {name: 'billing_fax'},
                {name: 'billing_company'},
                {name: 'billing_street_address'},
                {name: 'billing_suburb'},
                {name: 'billing_city'},
                {name: 'billing_postcode'},
                {name: 'billing_state'},
                {name: 'billing_country'},
                {name: 'payment_method'},
                {name: 'shipping_method'},
                {name: 'coupon_code'},
                {name: 'date_modified_on',  type: 'date', dateFormat: 'Y-m-d h:i:s'},
                {name: 'date_purchased_on', type: 'date', dateFormat: 'Y-m-d h:i:s'},
                {name: 'date_finished_on',  type: 'date', dateFormat: 'Y-m-d h:i:s'},
                {name: 'order_status_id'},
                {name: 'currency'},
                {name: 'currency_rate'},
                {name: 'order_total'},
                {name: 'txn_id'},
                {name: 'ip_address'}
            ]
        })
    });

    var expander = new Ext.grid.RowExpander({
        listeners: {
            beforeexpand: function(expander, record, body, rowIndex) {
                if (!this.tpl) {
                    this.tpl = new Ext.Template();
                }

                var products = '<table class="order-product">'
                    + '<col class="id"/>'
                    + '<col class="name"/>'
                    + '<col class="attributes"/>'
                    + '<col class="price"/>'
                    + '<col class="qty"/>'
                    + '<col class="final-price"/>'
                    + '<tr>'
                    + '<th>' + 'Id'.l() + '</th>'
                    + '<th>' + 'Name'.l() + '</th>'
                    + '<th>' + 'Attributes'.l() + '</th>'
                    + '<th>' + 'Price'.l() + '</th>'
                    + '<th>' + 'Q-ty'.l() + '</th>'
                    + '<th>' + 'Total'.l() + '</th></tr>';

                Ext.each(record.get('product'), function(row) {
                    var attributes = '';
                    Ext.each(row.attributes, function(attribute) {
                        attributes += String.format(
                            '<p><label>{0}:</label> <span>{1}</span></p>',
                            attribute.product_option,
                            attribute.product_option_value
                        );
                    });
                    products += String.format(
                        '<tr><td>{0}</td><td>{1}</td><td>{2}</td><td>{3}</td><td>{4}</td><td>{5}</td></tr>',
                        row.product_id,
                        row.name,
                        attributes,
                        row.final_price + ' ' + record.get('currency'),
                        row.quantity,
                        row.product_subtotal + ' ' + record.get('currency')
                    );
                }, this);
                products += '</table>';

                var paymentData = [
                    {title: 'Payment method'.l(), dataIndex: 'payment_method'},
                    {title: 'Firstname'.l(),    dataIndex: 'billing_firstname'},
                    {title: 'Lastname'.l(),     dataIndex: 'billing_lastname'},
                    {title: 'Phone'.l(),        dataIndex: 'billing_phone'},
                    {title: 'Fax'.l(),          dataIndex: 'billing_fax'},
                    {title: 'Street'.l(),       dataIndex: 'billing_street'},
                    {title: 'Company'.l(),      dataIndex: 'billing_company'},
                    {title: 'Suburb'.l(),       dataIndex: 'billing_suburb'},
                    {title: 'City'.l(),         dataIndex: 'billing_city'},
                    {title: 'Zip'.l(),          dataIndex: 'billing_postcode'},
                    {title: 'State'.l(),        dataIndex: 'billing_state'},
                    {title: 'Country'.l(),      dataIndex: 'billing_country'}
                ];
                var paymentMethod = '<div class="payment">';
                Ext.each(paymentData, function(row) {
                    paymentMethod += String.format(
                        '<p class="payment-method-item"><label>{0}:</label><span>{1}</span></p>',
                        row.title,
                        (value = record.get(row.dataIndex)) ? value : ''
                    );
                }, this);
                paymentMethod += '</div>';

                var shippingData = [
                    {title: 'Shipping method'.l(), dataIndex: 'shipping_method'},
                    {title: 'Firstname'.l(),    dataIndex: 'delivery_firstname'},
                    {title: 'Lastname'.l(),     dataIndex: 'delivery_lastname'},
                    {title: 'Phone'.l(),        dataIndex: 'delivery_phone'},
                    {title: 'Fax'.l(),          dataIndex: 'delivery_fax'},
                    {title: 'Street'.l(),       dataIndex: 'delivery_street'},
                    {title: 'Company'.l(),      dataIndex: 'delivery_company'},
                    {title: 'Suburb'.l(),       dataIndex: 'delivery_suburb'},
                    {title: 'City'.l(),         dataIndex: 'delivery_city'},
                    {title: 'Zip'.l(),          dataIndex: 'delivery_postcode'},
                    {title: 'State'.l(),        dataIndex: 'delivery_state'},
                    {title: 'Country'.l(),      dataIndex: 'delivery_country'}
                ];
                var shippingMethod = '<div class="shipping">';
                Ext.each(shippingData, function(row) {
                    shippingMethod += String.format(
                        '<p class="shipping-method-item"><label>{0}:</label><span>{1}</span></p>',
                        row.title,
                        (value = record.get(row.dataIndex)) ? value : ''
                    );
                }, this);
                shippingMethod += '</div>';

                var html = '<div class="account-order">';
                html += products;
                html += paymentMethod;
                html += shippingMethod;
                html += '</div>';
                this.tpl.set(html);
            }
        }
    });

    var actions = new Ext.ux.grid.RowActions({
        header:'Actions'.l(),
        actions:[{
            iconCls: 'icon-page-edit',
            tooltip: 'Edit'.l()
        }],
        callbacks: {
            'icon-page-edit': function(grid, record, action, row, col) {
                window.open(Axis.getUrl('sales_order/index/orderId/' + record.get('id')));
            }
        }
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true,
            menuDisabled: true
        },
        columns: [expander, {
            dataIndex: 'number',
            header: 'Number'.l(),
            width: 100
        }, {
            dataIndex: 'ip_address',
            header: 'Ip'.l(),
            width: 100
        }, {
            dataIndex: 'order_total',
            header: 'Total Base'.l(),
            width: 120
        }, {
            dataIndex: 'order_total',
            header: 'Total Purchased'.l(),
            renderer: function(v, meta, r) {
                return v * r.get('currency_rate') + ' ' + r.get('currency');
            },
            width: 120
        }, {
            dataIndex: 'date_purchased_on',
            header: 'Date created'.l(),
            renderer: function(v) {
                return Ext.util.Format.date(v);
            },
            width: 120
        }, {
            dataIndex: 'status',
            header: 'Status'.l(),
            width: 100
        }, actions]
    });

    OrderGrid.el = new Axis.grid.GridPanel({
        border: false,
        cm: cm,
        ds: ds,
        massAction: false,
        plugins: [
            expander,
            actions
        ],
        viewConfig: {
            emptyText: 'No records found'.l(),
            forceFit: true
        },
        sm: new Ext.grid.RowSelectionModel(),
        title: 'Orders'.l()
    });

    CustomerWindow.addTab(OrderGrid.el, 40);
    CustomerWindow.dataObjects.push(OrderGrid);

});
