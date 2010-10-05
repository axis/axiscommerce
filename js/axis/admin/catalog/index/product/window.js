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

var ProductWindow = {

    el: null,

    form: null,

    formFields: [],

    tabs: [],

    /**
     * Objects that will store additional information about product
     * e.g. AttributeGrid, VariationGrid etc.
     * These objects should have getData, loadData and clearData methods
     */
    dataObjects: [],

    data: {},

    add: function() {

    },

    /**
     * @param {Object} tab
     * @param {integer} sortOrder
     */
    addTab: function(tab, sortOrder) {
        if (!ProductWindow.tabCollection) {
            ProductWindow.tabCollection = new Ext.util.MixedCollection();
        }
        ProductWindow.tabCollection.add(sortOrder, tab);
        ProductWindow.tabCollection.keySort('ASC', function(a, b) {
            return a - b;
        });
        ProductWindow.tabs.splice(
            ProductWindow.tabCollection.indexOf(tab), 0, tab
        );
    },

    close: function() {
        ProductWindow.hide();
    },

    hide: function() {
        ProductWindow.el.hide();
    },

    clearData: function() {
        Ext.each(ProductWindow.dataObjects, function(item) {
            item.clearData();
        });
        ProductWindow.data = {};
    },

    loadData: function(data) {
        Ext.each(ProductWindow.dataObjects, function(item) {
            item.loadData(data);
        });
    },

    getData: function() {
        ProductWindow.data = {};
        Ext.each(ProductWindow.dataObjects, function(item) {
            var data = item.getData();
            for (i in data) {
                ProductWindow.data[i] = Ext.encode(data[i]);
            }
        });
    },

    save: function(closeWindow) {
        ProductWindow.getData();
        ProductWindow.form.getForm().submit({
            url: Axis.getUrl('catalog_index/save-product'),
            params: ProductWindow.data,
            success: function(form, action) {
                ProductGrid.reload();
                if (closeWindow) {
                    ProductWindow.hide();
                    ProductWindow.form.getForm().clear();
                    ProductWindow.clearData();
                } else {
                    var response = Ext.decode(action.response.responseText);
                    Product.load(response.data.product_id);
                }
            },
            failure: function(form, action) {
                if (action.failureType == 'client') {
                    return;
                }
            }
        });
    },

    show: function() {
        ProductWindow.el.show();
    }
};

Ext.onReady(function() {

    Ext.form.Field.prototype.msgTarget = 'qtip';

    ProductWindow.form = new Axis.FormPanel({
        id: 'form-product',
        bodyStyle: {
            padding: '5px 0 0'
        },
        reader: new Ext.data.JsonReader({
                root: 'data',
                idProperty: 'product.id'
            }, ProductWindow.formFields
        ),
        items: [{
            activeTab: 0,
            anchor: Ext.isWebKit ? 'undefined 100%' : '100% 100%',
            border: false,
            defaults: {
                autoScroll: true,
                hideMode: 'offsets',
                layout: 'form'
            },
            deferredRender: false, // Ext.form.RadioGroup getErrors() problem
            enableTabScroll: true,
            id: 'tab-panel-product',
            plain: true,
            xtype: 'tabpanel',
            items: ProductWindow.tabs
        }]
    });

    ProductWindow.el = new Axis.Window({
        id: 'window-product',
        items: [
            ProductWindow.form
        ],
        maximizable: true,
        width: 750,
        title: 'Product'.l(),
        buttons: [{
            icon: Axis.skinUrl + '/images/icons/database_save.png',
            text: 'Save'.l(),
            handler: function() {
                ProductWindow.save(true);
            }
        }, {
            icon: Axis.skinUrl + '/images/icons/database_save.png',
            text: 'Save & Continue Edit'.l(),
            handler: function() {
                ProductWindow.save(false);
            }
        }, {
            icon: Axis.skinUrl + '/images/icons/cancel.png',
            text: 'Cancel'.l(),
            handler: ProductWindow.hide
        }]
    });

    ProductWindow.el.on('hide', function() {
        ProductWindow.form.getForm().reset();
    });
});
