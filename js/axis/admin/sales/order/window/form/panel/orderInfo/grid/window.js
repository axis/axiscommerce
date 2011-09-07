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

Ext.onReady(function(){

    /**
     * Clone Function
     */
    Ext.ux.clone = function(obj)
    {
       if(obj == null || typeof(obj) != 'object')
          return obj;
       if (Ext.isDate(obj))
          return obj.clone();

       var cloneArray = function(arr)
       {
          var len = arr.length;
          var out = [];
          if (len > 0)
          {
             for (var i = 0; i < len; ++i)
                out[i] = Ext.ux.clone(arr[i]);
          }
          return out;

       };

       var c = new obj.constructor();
       for (var prop in obj)
       {
          var p = obj[prop];
          if (Ext.isArray(p))
             c[prop] = cloneArray(p);
          else if (typeof p == 'object')
             c[prop] = Ext.ux.clone(p);
          else
             c[prop] = p;
       }
       return c;
    };


    additionalProduts = {
        add: function () {
            var selections = additionalProduts._getSelections();

            if (undefined === selections.__count__) {
                selections.__count__ = function() {
                    var count = 0;
                    for (var k in selections) {
                        if (selections.hasOwnProperty(k)) {
                           ++count;
                        }
                    }
                    return count;
                }
                selections.__count__.toString = function() {return this();}
            }

            if (0 === selections.__count__) {
                return;
            }
            Ext.Ajax.request({
                url: Axis.getUrl('sales/order/add-product'),
                method: 'post',
                params: {data: Ext.encode(selections)},
                success: function(response, options) {
                    var data = Ext.decode(response.responseText).data;
                    var store = Ext.getCmp('grid-products').getStore();
                    for (var i in data) {
                        if ('object' !== typeof data[i]) {
                            continue;
                        }
                        var record = new store.recordType(data[i]);
                        record.markDirty();
                        store.modified.push(record);
                        store.add(record);
                    }
                    windowAddProductOrder.hide();
                }
            });
        },
        _getSelections: function () {
            function _prepareModifiers(domElementName, domElementValue, productId)
            {
//                domElementValue = domElementValue || null;
                var properties = domElementName
                    .replace(/\]/g, '')
                    .split(/\[/g)
//                        .reverse()
                    ;
                if ('' === properties[properties.length - 1]) {
                    properties.pop();
                }

                var value = domElementValue, temp;
                for (var i = properties.length - 1; i >= 1; i--) {
                    temp = new Object();
                    temp[properties[i]] = Ext.ux.clone(value);
                    value = temp;

                }
                var varName = properties[0];// + '_' + productId;
                if ('undefined' === typeof window[varName]
                    || null === window[varName]) {

                    window[varName] = new Object();
                }

                if ('undefined' === typeof window[varName][productId]
                    || null === window[varName][productId]) {

                    window[varName][productId] = value;
                } else {
                    window[varName][productId] = jQuery.extend(
                        true, window[varName][productId], value
                    );
                }
                return window[varName][productId];
            }

            var selectionModel  = Ext.getCmp('grid-add-products').getSelectionModel();
            var selections = selectionModel
                .getSelections();
            var selected = {}, record, variationId;
            var form =  Order.form.getForm();
            for (var i = 0, len = selections.length; i < len; i++) {
                variationId = 0;
                record = selections[i].data;
                selected[record.id] = record;

                selected[record.id].orderId = form
                    .findField('order[id]')
                    .getValue();

                selected[record.id].customerGroupId = form
                    .findField('customer[group_id]')
                    .getValue();

                selected[record.id].countryId = form
                    .findField('order[delivery_country]')
                    .getValue();

                selected[record.id].zoneId = form
                    .findField('order[delivery_state]')
                    .getValue();

                var domElements = Ext.query('#product-attributes-form-' + record.id + ' .product-variations select');
                if ('undefined' !== typeof domElements[0]) {
                    variationId = domElements[0].value;
                }
                selected[record.id].variationId = variationId;

                domElements = Ext.query('#product-attributes-form-' + record.id + ' .product-modifiers .modifier');
                modifiers = modifier = null;
                for(var j in domElements) {
                    if ('undefined' == typeof domElements[j].id) {
                        continue;
                    }
                    value = domElements[j].value;
                    if (('radio' === domElements[j].type
                            || 'checkbox' === domElements[j].type)
                        && false === domElements[j].checked ) {

                        continue;
                    }
                    modifiers = _prepareModifiers(
                        domElements[j].name, value, record.id
                    );
                }
                selected[record.id].modifiers = modifiers;
            }
            selectionModel.clearSelections();
            return selected;
//            jQuery.extend(additionalProduts.selected, selected);
//            console.log(additionalProduts.selected);
//            return additionalProduts.selected;
        }

    };

    var storeProducts = new Ext.data.Store({
//        storeId: 'storeProducts',
        reader: new Ext.data.JsonReader({
            idProperty: 'id',
            root: 'data',
            totalProperty: 'count'
        }, [
            {name: 'id',                type: 'int'},
            {name: 'name'},
            {name: 'sku'},
            {name: 'avaible_quantity',  type: 'float', mapping: 'quantity'},
            {name: 'quantity',          type: 'float', mapping: 'quantit1', defaultValue: 1},
            {name: 'price',             type: 'float'},
            {name: 'is_active',         type: 'int'}

        ]),
        remoteSort: true,
        pruneModifiedRecords: true,
        url: Axis.getUrl('catalog/product/list'),
        listeners: {
            beforeload: function(store, options) {
                var siteId = Order.form.getForm().findField('order[site_id]').value;
                if (!siteId) {
                    alert('Set Store');
                    return false;
                }
                options.params.siteId = siteId;
            }
        }
    });
    var selectionModel = new Ext.grid.CheckboxSelectionModel();

//////////////////////////////////////////////////////////////

    AjaxRowExpander = function(config) {
        config = config || {};
        AjaxRowExpander.superclass.constructor.call(this, config);
        this.enableCaching = false;
    }

    Ext.extend(AjaxRowExpander, Ext.grid.RowExpander, {
        getBodyContent: function(record, index){
            var domId = 'product-attributes-form-' + record.id;
            var html = '<div class="product-attributes">' +
                            '<p class="product-attribute">' +
                                '<div id="' + domId + '">' + 'Loading'.l() + '...</div>' +
                            '</p>' +
                        '</div>';

            Ext.Ajax.request({
                url: Axis.getUrl(
                    'catalog/product-option/get-form/productId/' + record.id
                ),
                method: 'post',
                disableCaching: true,
                success: function(response, options) {
                    var data = Ext.decode(response.responseText).data;
                    Ext.getDom(domId).innerHTML = data.form;
                },
                productId: record.id
            });

            return html;
        },
        beforeExpand: function(record, body, rowIndex){
            if(false !== this.fireEvent('beforeexpand', this, record, body, rowIndex)){
                if ('' !== body.innerHTML) {
                    return true;
                }
                body.innerHTML = this.getBodyContent(record, rowIndex);
                return true;
            }
            return false;
        }
    });

    var expander = new AjaxRowExpander();

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [
            selectionModel,
            expander,
        {
            header: 'Name'.l(),
            dataIndex: 'name',
            width: 300,
            menuDisabled: true,
            table: 'cpd'
        }, {
            align: 'right',
            header: 'Price'.l(),
            dataIndex: 'price',
            menuDisabled: true
        }, {
            align: 'right',
            header: 'Quantity'.l(),
            dataIndex: 'quantity',
            menuDisabled: true,
            sortable: false,
            editor: new Ext.form.NumberField({
                allowBlank: false,
                allowNegative: false,
                maxValue: 100000
            }),
            filterable: false
        }, {
            align: 'right',
            header: 'Available'.l(),
            dataIndex: 'avaible_quantity',
            menuDisabled: true,
            sortName: 'quantity',
            filter: {
                name: 'quantity'
            }
        }]
    });

    var pagginator = new Axis.PagingToolbar({
        pageSize: 10,
        store: storeProducts
    });

    var gridAddProducts = new Axis.grid.EditorGridPanel({
        cm: cm,
        sm: selectionModel,
        ds: storeProducts,
        id: 'grid-add-products',
        plugins: [
            expander,
            new Axis.grid.Filter()
        ],
        border: false,
        viewConfig: {
            forceFit: true,
            emptyText: 'No records found'.l()
        },
        bbar: pagginator
    });

    gridAddProducts.override({
        onMouseDown : function(e, t){
            if ('INPUT' === t.tagName) {
                return;
            }
            this.processEvent('mousedown', e);
        }
    });

    selectionModel.on('rowselect', function(sm, rowIndex, record){
        expander.expandRow(rowIndex);
    }, expander);

    pagginator.on('beforechange', function(toolbar, params) {
        var isSelected = Ext.getCmp('grid-add-products')
            .getSelectionModel()
            .getSelections()
            .length;
       if (!isSelected) {
           return true;
       }
       if (confirm('Add checked products')) {
           additionalProduts.add();
           return true;
       }
       return false;
    });

    var windowAddProductOrder = new Axis.Window({
        id: 'windowAddProductOrder',
        title: 'Add Product'.l(),
        width: 700,
        height: 365,
        items: [gridAddProducts],
        buttons: [{
            icon: Axis.skinUrl + '/images/icons/add.png',
            text: 'Add'.l(),
            handler: additionalProduts.add
        }, {
            icon: Axis.skinUrl + '/images/icons/cancel.png',
            text: 'Close'.l(),
            handler: function() {windowAddProductOrder.hide();}
        }]
    });
}, this);