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

Box.Window = {

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

    save: function(closeWindow) {
        Box.Window.getData();
        Box.Window.form.getForm().submit({
            url: Axis.getUrl('core/theme_block/save'),
            params: Box.Window.data,
            success: function(form, action) {
                Box.Grid.reload();
                if (closeWindow) {
                    Box.Window.hide();
                    Box.Window.form.getForm().clear();
                    Box.Window.clearData();
                } else {
                    var response = Ext.decode(action.response.responseText);
                    Box.load(response.data.id);
                }
            }
        });
    },

    show: function() {
        Box.Window.el.show();
    },

    hide: function() {
        Box.Window.el.hide();
    },

    /**
     * @param {Object} tab
     * @param {integer} sortOrder
     */
    addTab: function(tab, sortOrder) {
        if (!Box.Window.tabCollection) {
            Box.Window.tabCollection = new Ext.util.MixedCollection();
        }
        Box.Window.tabCollection.add(sortOrder, tab);
        Box.Window.tabCollection.keySort('ASC', function(a, b) {
            return a - b;
        });
        Box.Window.tabs.splice(
            Box.Window.tabCollection.indexOf(tab), 0, tab
        );
    },

    clearData: function() {
        Ext.each(Box.Window.dataObjects, function(item) {
            item.clearData();
        });
        Box.Window.data = {};
    },

    loadData: function(data) {
        Ext.each(Box.Window.dataObjects, function(item) {
            item.loadData(data);
        });
    },

    getData: function() {
        Box.Window.data = {};
        Ext.each(Box.Window.dataObjects, function(item) {
            var data = item.getData();
            for (i in data) {
                Box.Window.data[i] = Ext.encode(data[i]);
            }
        });
    }
};

Ext.onReady(function() {

    Ext.QuickTips.init();

    Ext.form.Field.prototype.msgTarget = 'msg';

    Box.Window.form = new Axis.FormPanel({
        id: 'form-box',
        bodyStyle: {
            padding: '5px 0 0'
        },
        reader: new Ext.data.JsonReader({
                root: 'data',
                idProperty: 'id'
            }, Box.Window.formFields
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
            deferredRender: false,
            plain: true,
            xtype: 'tabpanel',
            items: [Box.Window.tabs]
        }]
    });

    Box.Window.el = new Axis.Window({
        id: 'window',
        width: 700,
        height: 400,
        title: 'Box'.l(),
        items: Box.Window.form,
        buttons: [{
            icon: Axis.skinUrl + '/images/icons/database_save.png',
            text: 'Save'.l(),
            handler: function() {
                Box.Window.save(true);
            }
        }, {
            icon: Axis.skinUrl + '/images/icons/database_save.png',
            text: 'Save & Continue Edit'.l(),
            handler: function() {
                Box.Window.save(false);
            }
        }, {
            icon: Axis.skinUrl + '/images/icons/cancel.png',
            text: 'Cancel'.l(),
            handler: Box.Window.hide
        }]
    });
});
