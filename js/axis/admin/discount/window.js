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

var discountWindow = {

    el: null,

    form: null,

    show: function() {
        discountWindow.el.show();
    },
    hide: function() {
        discountWindow.el.hide();
    },
    save: function() {

    }
};
Ext.onReady(function() {
    var fields = [
        {name: 'discount[id]',          mapping: 'discount.id',          type: 'int'},
        {name: 'discount[name]',        mapping: 'discount.name'},
//        {name: 'discount[description]', mapping: 'discount.description'},
        {name: 'discount[from_date]',   mapping: 'discount.from_date'},
        {name: 'discount[to_date]',     mapping: 'discount.to_date'},
        {name: 'discount[is_active]',   mapping: 'discount.is_active',   type: 'int'},
        {name: 'discount[type]',        mapping: 'discount.type'},
        {name: 'discount[amount]',      mapping: 'discount.amount'},
        {name: 'discount[priority]',    mapping: 'discount.priority'},
        {name: 'discount[is_combined]', mapping: 'discount.is_combined', type: 'int'},
        {name: 'eav[site]',             mapping: 'eav.site'},
        {name: 'eav[group]',            mapping: 'eav.group'},
        {name: 'eav[manufacturer]',     mapping: 'eav.manufacturer'},
        {name: 'eav[category]',         mapping: 'eav.category'},
        {name: 'eav[productId]',        mapping: 'eav.productId'},
        {name: 'eav[price]',            mapping: 'eav.price'}
//        {name: 'eav[date]',             mapping: 'eav.date'}
    ];
    
    discountWindow.form = new Axis.FormPanel({
        bodyStyle   : 'padding: 10px 0 0;',
        method      : 'post',
        reader      : new Ext.data.JsonReader({
            root        : 'data',
            idProperty  : 'discount.id'
        }, fields),
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
            items: [
                discountWindowFormGeneralTab.el,
                discountWindowFormSiteTab.el,
                discountWindowFormGroupTab.el,
                discountWindowFormManufacturerTab.el, 
//                discountWindowFormCategoriesTab.el,
                discountWindowFormProductTab.el,
                discountWindowFormPriceTab.el,
                discountWindowFormDateTab.el
//                discountWindowFormConditionTab.el
            ]
        }]
    });

    discountWindow.el = new Axis.Window({
//        height  : 350,
        title   : 'Discount'.l(),
        items   : discountWindow.form,
        buttons : [{
            icon    : Axis.skinUrl + '/images/icons/database_save.png',
            text    : 'Save'.l(),
            handler : function() {
                discountWindow.save(true);
            }
        }, {
            icon    : Axis.skinUrl + '/images/icons/database_save.png',
            text    : 'Save & Continue Edit'.l(),
            handler : function() {
                discountWindow.save(false);
            }
        }, {
            icon    : Axis.skinUrl + '/images/icons/cancel.png',
            text    : 'Cancel'.l(),
            handler : discountWindow.hide
        }]
    });
});