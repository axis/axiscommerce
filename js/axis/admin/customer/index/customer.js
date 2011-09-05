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

var Customer = {

    id: null,

    add: function() {
        Customer.id = null;
        CustomerWindow.el.setTitle('New customer'.l());
        CustomerWindow.form.getForm().clear();
        CustomerWindow.clearData();
        CustomerWindow.show();
    },

    /**
     * Load data of recieved customer id, and open customerWindow on success
     *
     * @param {int} id
     */
    load: function(id) {
        Customer.id = id;
        CustomerWindow.form.getForm().load({
            url: Axis.getUrl('account/customer/load/id/' + id),
            method: 'get',
            success: function(form, action) {
                var data = Ext.decode(action.response.responseText).data;
                CustomerWindow.el.setTitle(
                    Axis.escape(data.customer.firstname
                    + ' '
                    + data.customer.lastname)
                    + ': '
                    + data.customer.email
                );
                CustomerWindow.loadData(data);
                CustomerWindow.show();
            },
            failure: function() {
                console.log(arguments);
            }
        });
    }
};
