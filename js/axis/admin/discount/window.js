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

    create: function(data) {
        this.form.getForm().clear();
        siteTab.clear();
        groupTab.clear();
        manufacturerTab.clear();
        categoryTab.clear();
        productTab.clear();
        attributeTab.clear();
        
        this.el.setTitle('New'.l());
        this.el.show();


    },
    hide: function() {
        this.el.hide();
    },
    load: function(id) {
        var self = this;
        
        this.form.getForm().load({
            url     : Axis.getUrl('discount/load'),
            params  : {id : id},  
            method  : 'get',
            success : function(form, action) {
                var data = Ext.decode(action.response.responseText).data;
                self.el.setTitle(data.discount.name);
                self.el.show();

                siteTab.setData(data.rule.site);
                groupTab.setData(data.rule.group);
                manufacturerTab.setData(data.rule.manufacture);
                categoryTab.setData(data.rule.category);
                productTab.setData(data.rule.productId);
                attributeTab.setData(data.rule);
            },
            failure: function() {
                console.log(arguments);
            }
        });
        
        
    },
    remove: function(data, callback) {
        if (!confirm('Are you sure?'.l())) {
            return;
        }
        Ext.Ajax.request({
            url: Axis.getUrl('discount/remove'),
            params: {data: Ext.encode(data)},
            callback: callback
        });
    },
    save: function() {
        
        var form = this.form.getForm();
        
        var params = {
            site         : siteTab.getSelected(),
            group        : groupTab.getSelected(),
            manufacture  : manufacturerTab.getSelected(),
            category     : categoryTab.getSelected(),
            productId    : productTab.getSelected()
        };
        var price = form.findField('rule[price_greate]').getValue();
        if ('' != price) {
            params.price_greate = [price];
        }
        
        price = form.findField('rule[price_less]').getValue();
        if ('' != price) {
            params.price_less = [price];
        }
        
        params = attributeTab.getSelected(params);
        var params = {
            rule: Ext.encode(params)
        };
        form.submit({
            url     : Axis.getUrl('discount/save'),
            params  : params,  
            method  : 'post',
            success : function() {
                Ext.getCmp('gridDiscount').getStore().reload();
                discountWindow.el.hide();
            }
        });
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
        {name: 'rule[site]',            mapping: 'rule.site'},
        {name: 'rule[group]',           mapping: 'rule.group'},
        {name: 'rule[manufacturer]',    mapping: 'rule.manufacturer'},
        {name: 'rule[category]',        mapping: 'rule.category'},
        {name: 'rule[productId]',       mapping: 'rule.productId'},
        {name: 'rule[price_greate]',    mapping: 'rule.price_greate', convert:function(v, record){
                if (v.length) {
                    return v[0];
                }
                return v;
        }},
        {name: 'rule[price_less]',      mapping: 'rule.price_less', convert:function(v, record){
                if (v.length) {
                    return v[0];
                }
                return v;
        }}
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
                generalTab.el,
                siteTab.el,
                groupTab.el,
                manufacturerTab.el, 
                categoryTab.el,
                productTab.el,
                attributeTab.el
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
                discountWindow.save();
            }
        }, {
            icon    : Axis.skinUrl + '/images/icons/cancel.png',
            text    : 'Cancel'.l(),
            handler : function() {discountWindow.hide();}
        }]
    });
});