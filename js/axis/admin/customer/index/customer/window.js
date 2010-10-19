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

var CustomerWindow = {

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
        if (!CustomerWindow.tabCollection) {
            CustomerWindow.tabCollection = new Ext.util.MixedCollection();
        }
        CustomerWindow.tabCollection.add(sortOrder, tab);
        CustomerWindow.tabCollection.keySort('ASC', function(a, b) {
            return a - b;
        });
        CustomerWindow.tabs.splice(
            CustomerWindow.tabCollection.indexOf(tab), 0, tab
        );
    },

    show: function() {
        CustomerWindow.el.show();
    },

    hide: function() {
        CustomerWindow.el.hide();
    },

    clearData: function() {
        Ext.each(CustomerWindow.dataObjects, function(item) {
            item.clearData();
        });
        CustomerWindow.data = {};
    },

    loadData: function(data) {
        Ext.each(CustomerWindow.dataObjects, function(item) {
            item.loadData(data);
        });
    },

    getData: function() {
        CustomerWindow.data = {};
        Ext.each(CustomerWindow.dataObjects, function(item) {
            if (!item.getData) {
                return true;
            }
            var data = item.getData();
            for (i in data) {
                CustomerWindow.data[i] = Ext.encode(data[i]);
            }
        });
    },

    save: function(closeWindow) {
        CustomerWindow.getData();
        CustomerWindow.form.getForm().submit({
            url: Axis.getUrl('customer_index/save-customer'),
            params: CustomerWindow.data,
            submitEmptyText: false,
            success: function(form, action) {
                CustomerGrid.reload();
                if (closeWindow) {
                    CustomerWindow.hide();
                    CustomerWindow.form.getForm().clear();
                    CustomerWindow.clearData();
                } else {
                    var response = Ext.decode(action.response.responseText);
                    Customer.load(response.data.customer_id);
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
        CustomerWindow.el.show();
    }
};

Ext.onReady(function() {

    Ext.form.Field.prototype.msgTarget = 'qtip';

    CustomerWindow.form = new Axis.FormPanel({
        id: 'form-customer',
        bodyStyle: {
            padding: '5px 0 0'
        },
        reader: new Ext.data.JsonReader({
                root: 'data',
                idProperty: 'customer.id'
            }, CustomerWindow.formFields
        ),
        items: [{
            activeTab: 0,
            id: 'tab-panel-customer',
            anchor: Ext.isWebKit ? 'undefined 100%' : '100% 100%',
            border: false,
            defaults: {
                autoScroll: true,
                hideMode: 'offsets',
                layout: 'form'
            },
            deferredRender: false, // Ext.form.RadioGroup getErrors() problem
            enableTabScroll: true,
            plain: true,
            xtype: 'tabpanel',
            items: CustomerWindow.tabs
        }]
    });

    CustomerWindow.el = new Axis.Window({
        id: 'window-customer',
        items: [
            CustomerWindow.form
        ],
        maximizable: true,
        width: 750,
        title: 'Customer'.l(),
        buttons: [{
            icon: Axis.skinUrl + '/images/icons/database_save.png',
            text: 'Save'.l(),
            handler: function() {
                CustomerWindow.save(true);
            }
        }, {
            icon: Axis.skinUrl + '/images/icons/database_save.png',
            text: 'Save & Continue Edit'.l(),
            handler: function() {
                CustomerWindow.save(false);
            }
        }, {
            icon: Axis.skinUrl + '/images/icons/cancel.png',
            text: 'Cancel'.l(),
            handler: CustomerWindow.hide
        }]
    });

    CustomerWindow.el.on('hide', function() {
        CustomerWindow.form.getForm().clear();
    });

//    Customer.load(1);
});
