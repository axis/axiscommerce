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

var Product = {

    id: null,

    imageRoot: Axis.secureUrl + '/media/product',

    add: function() {
        Product.id = null;
        ProductWindow.el.setTitle('New product'.l());
        ProductWindow.form.getForm().clear();
        ProductWindow.clearData();
        ProductWindow.show();
    },

    /**
     * Load data of recieved product id, and open productWindow on success
     *
     * @param {int} id
     */
    load: function(id) {
        Product.id = id;
        ProductWindow.form.getForm().load({
            url: Axis.getUrl('catalog_index/get-product-data/id/' + id),
            method: 'get',
            success: function(form, action) {
                var data = Ext.decode(action.response.responseText).data;
                ProductWindow.el.setTitle(data.description['lang_' + Axis.language].name);
                ProductWindow.loadData(data);
                ProductWindow.show();
            },
            failure: function() {
                console.log(arguments);
            }
        });
    },

    updatePriceIndex: function(skipConfirm, skipSession) {
        if (!skipConfirm
            && !confirm(("Are you sure want to delete old price indexes " +
            "and create new for all products?\nThis can take a while.").l())) {

            return;
        }
        Ext.Ajax.request({
            url: Axis.getUrl('catalog_index/update-price-index'),
            params: {
                limit       : 50,
                skip_session: skipSession
            },
            success: function(response, options) {
                response = Ext.decode(response.responseText);
                if (false == response.completed) {
                    Product.updatePriceIndex(1, 1);
                }
            }
        });
    }
};
