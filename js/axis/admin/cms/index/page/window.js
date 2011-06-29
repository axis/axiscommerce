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

var PageWindow = {

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

    categoryTree: null,

    /**
     * @param {Object} tab
     * @param {integer} sortOrder
     */
    addTab: function(tab, sortOrder) {
        if (!PageWindow.tabCollection) {
            PageWindow.tabCollection = new Ext.util.MixedCollection();
        }
        PageWindow.tabCollection.add(sortOrder, tab);
        PageWindow.tabCollection.keySort('ASC', function(a, b) {
            return a - b;
        });
        PageWindow.tabs.splice(
            PageWindow.tabCollection.indexOf(tab), 0, tab
        );
    },

    save: function(closeWindow) {
        PageWindow.getData();
        PageWindow.form.getForm().submit({
            url: Axis.getUrl('cms_index/save-page'),
            params: PageWindow.data,
            success: function(form, action) {
                PageGrid.el.getStore().reload();
                if (closeWindow) {
                    PageWindow.hide();
                    PageWindow.form.getForm().clear();
                    PageWindow.clearData();
                } else {
                    var response = Ext.decode(action.response.responseText);
                    Page.load(response.data.id);
                }
            }
        });
    },

    hide: function() {
        PageWindow.el.hide();
    },

    show: function() {
        PageWindow.el.show();
    },

    clearData: function() {
        Ext.each(PageWindow.dataObjects, function(item) {
            item.clearData();
        });
        PageWindow.data = {};
    },

    loadData: function(data) {
        Ext.each(PageWindow.dataObjects, function(item) {
            item.loadData(data);
        });
    },

    getData: function() {
        PageWindow.data = {};
        Ext.each(PageWindow.dataObjects, function(item) {
            var data = item.getData();
            for (i in data) {
                PageWindow.data[i] = Ext.encode(data[i]);
            }
        });
    }

};

Ext.onReady(function() {

    PageWindow.form = new Axis.form.FormPanel({
        bodyStyle: {
            padding: '5px 0 0'
        },
        method: 'post',
        reader: new Ext.data.JsonReader({
            root: 'data'
        }, PageWindow.formFields),
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
            id: 'tab-panel-page',
            plain: true,
            xtype: 'tabpanel',
            items: PageWindow.tabs
        }]
    });

    PageWindow.el = new Axis.Window({
        items: [
            PageWindow.form
        ],
        maximizable: true,
        title: 'New Page'.l(),
        buttons: [{
            icon: Axis.skinUrl + '/images/icons/database_save.png',
            text: 'Save'.l(),
            handler: function() {
                PageWindow.save(true);
            }
        }, {
            icon: Axis.skinUrl + '/images/icons/database_save.png',
            text: 'Save & Continue Edit'.l(),
            handler: function() {
                PageWindow.save(false);
            }
        }, {
            icon: Axis.skinUrl + '/images/icons/cancel.png',
            text: 'Cancel'.l(),
            handler: PageWindow.hide
        }]
    });
});
