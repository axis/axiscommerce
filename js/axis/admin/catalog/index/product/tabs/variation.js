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

var VariationGrid = {
    
    el: null,
    
    /**
     * Input: 
     *  [[blue, green], [small, large]]
     * Output:
     *  [[blue, small], [blue, large], [green, small], [green, large]]
     * 
     * @param {Array} options
     */
    generateRecords: function(options) {
        var records = [[]];
        for (var i = options.length - 1; i >= 0; --i) {
            var temp = [];
            for (var j = options[i].length - 1; j >= 0; --j) {
                for (var k = 0, limit = records.length; k < limit; k++) {
                    temp.push(array_merge(records[k], [options[i][j]]));
                }
            }
            records = temp;
        }
        return records;
    },
    
    /**
     * 
     * @param {Array} records
     */
    add: function(records) {
        
        if (!records.length) {
            return;
        }
        
        var variationOptions = [],
            i = 0,
            mapping = {};
        
        Ext.each(records, function(r) {
            if (r.get('input_type') != -1) {
                return;
            }
            if (undefined === mapping[r.get('option_id')]) {
                mapping[r.get('option_id')] = i++;
            }
            if (!variationOptions[mapping[r.get('option_id')]]) {
                variationOptions[mapping[r.get('option_id')]] = [];
            }
            variationOptions[mapping[r.get('option_id')]].push({
                code                    : r.get('code'),
                option_code             : r.get('option_code'),
                option_name             : r.get('option_name'),
                value_name              : r.get('value_name'),
                option_id       : r.get('option_id'),
                option_value_id : r.get('value_id')
            });
        });
        
        var totalCount = 0;
        
        if (variationOptions.length) {
            totalCount = 1;
            Ext.each(variationOptions, function(o) {
                totalCount *= o.length;
            });
        } else {
            return;
        }
        
        if (totalCount > 100 && !confirm(
            ('You are going to generate {count} variations. \n' 
            + 'During this process, browser may not respond for a while. \n'
            + 'Are you sure?').l('core', totalCount)
        )) {
        
            return false;
        }
        
        if (!ProductWindow.attributeDetailsWindow) {
            ProductWindow.attributeDetailsWindow = new Axis.AttributeDetails();
        }
        
        ProductWindow.attributeDetailsWindow.purgeListeners();
        ProductWindow.attributeDetailsWindow.on('okpress', function(defaults) {
            var records = VariationGrid.generateRecords(variationOptions);
            var variations = [];
            Ext.each(records, function(r) {
                var record = new VariationGrid.el.store.recordType({
                    quantity    : defaults.quantity,
                    price_type  : defaults.price_type,
                    weight_type : defaults.weight_type,
                    price       : defaults.price,
                    cost        : defaults.cost,
                    weight      : defaults.weight,
                    remove      : 0
                });
                var sku = defaults.sku_prefix;
                for (var i = 0, limit = r.length; i < limit; i++) {
                    sku += '_'
                        + r[i].option_code.toLowerCase()
                        + '-'
                        + r[i].value_name.toLowerCase();
                }
                record.set('sku', sku);
                record.set('attributes', r);
                record.markDirty();
                VariationGrid.el.store.modified.push(record);
                variations.push(record);
            });
            VariationGrid.el.store.add(variations);
        });
        
        ProductWindow.attributeDetailsWindow
            .setSkuPrefix(ProductWindow.form.getForm().findField('product[sku]').getValue())
            .showField('sku_prefix')
            .showField('quantity')
            .showField('cost')
            .show();
    },
    
    clearData: function() {
        VariationGrid.el.store.loadData([]);
    },
    
    loadData: function(data) {
        VariationGrid.el.store.loadData(data.variations);
    },
    
    getData: function() {
        var modified = VariationGrid.el.store.getModifiedRecords();
        
        if (!modified.length) {
            return;
        }
        
        var data = {};
        for (var i = modified.length - 1; i >= 0; i--) {
            data[modified[i]['id']] = modified[i]['data'];
        }
        
        return {
            'variation': data
        };
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
                {name: 'sku'},
                {name: 'quantity', type: 'float'},
                {name: 'cost', type: 'float'},
                {name: 'attributes'},
                {name: 'price_type'},
                {name: 'price', type: 'float'},
                {name: 'weight_type'},
                {name: 'weight', type: 'float'},
                {name: 'remove', type:'int'}
            ]
        })
    });
    
    var expander = new Ext.grid.RowExpander({
        listeners: {
            beforeexpand: function(expander, record, body, rowIndex) {
                if (!this.tpl) {
                    this.tpl = new Ext.Template();
                }
                
                var html = '<div class="product-attributes">';
                Ext.each(record.get('attributes'), function(attr) {
                    html += String.format(
                        '<p class="product-attribute"><label>{0}</label><span>{1}</span></p>',
                        attr.option_name,
                        attr.value_name
                    );
                }, this);
                html += '</div>';
                this.tpl.set(html);
            }
        }
    });
    
    var remove = new Axis.grid.CheckColumn({
        dataIndex: 'remove',
        header: 'Delete'.l(),
        width: 60
    });
    
    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true,
            menuDisabled: true
        },
        columns: [expander, {
            dataIndex: 'id',
            header: 'Id'.l(),
            width: 40
        }, {
            id: 'sku',
            dataIndex: 'sku',
            header: 'SKU'.l(),
            editor: new Ext.form.TextField({
                allowBlank: true,
                maxLength: 255
            }),
            renderer: function (value, meta, record) {
                meta.attr += 'ext:qtip="' + value + '"';
                return value;
            }
        }, {
            align: 'right',
            dataIndex: 'quantity',
            header: 'Q-ty'.l(),
            editor: new Ext.form.NumberField({
                allowBlank: false,
                allowNegative: false
            }),
            width: 70
        }, {
            align: 'right',
            dataIndex: 'cost',
            header: 'Cost'.l(),
            editor: new Ext.form.NumberField({
                allowBlank: false,
                allowNegative: false
            }),
            width: 70
        }, {
            header: 'Price modifier'.l(),
            dataIndex: 'price_type',
            editor: ProductWindow.modifierType.cloneConfig(),
            renderer: ProductWindow.modifierRenderer,
            width: 130
        }, {
            align: 'right',
            header: 'Price'.l(),
            dataIndex: 'price',
            editor: new Ext.form.NumberField(),
            width: 70
        }, {
            header: 'Weight modifier'.l(),
            dataIndex: 'weight_type',
            editor: ProductWindow.modifierType.cloneConfig(),
            renderer: ProductWindow.modifierRenderer,
            width: 130
        }, {
            align: 'right',
            header: 'Weight'.l(),
            dataIndex: 'weight',
            editor: new Ext.form.NumberField(),
            width: 70
        }, remove]
    });
    
    VariationGrid.el = ProductWindow.variationGrid = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'sku',
        border: false,
        cm: cm,
        ds: ds,
        id: 'grid-product-window-variation-list',
        massAction: false,
        plugins: [
            remove,
            expander
        ],
        sm: new Ext.grid.RowSelectionModel(),
        title: 'Variations'.l(),
        trackMouseOver: false,
        tbar: [{
            icon: Axis.skinUrl + '/images/icons/add.png',
            text: 'Add'.l(),
            handler: function() {
                if (!ProductWindow.attributeWindow) {
                    ProductWindow.attributeWindow = new Axis.AttributeWindow();
                }
                ProductWindow.attributeWindow.purgeListeners();
                ProductWindow.attributeWindow.on('okpress', VariationGrid.add);
                ProductWindow.attributeWindow
                    .setFilter(function(r) {
                        return r.get('input_type') == -1
                            || (r.get('input_type') != 1 && !r.get('leaf'))
                    })
                    .setSingleSelect(false)
                    .show();
            }
        }]
    });
    
    ProductWindow.addTab(VariationGrid.el, 90);
    ProductWindow.dataObjects.push(VariationGrid);
    
});

function array_merge () {
    // http://kevin.vanzonneveld.net
    // +   original by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Nate
    // +   input by: josh
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: arr1 = {"color": "red", 0: 2, 1: 4}
    // *     example 1: arr2 = {0: "a", 1: "b", "color": "green", "shape": "trapezoid", 2: 4}
    // *     example 1: array_merge(arr1, arr2)
    // *     returns 1: {"color": "green", 0: 2, 1: 4, 2: "a", 3: "b", "shape": "trapezoid", 4: 4}
    // *     example 2: arr1 = []
    // *     example 2: arr2 = {1: "data"}
    // *     example 2: array_merge(arr1, arr2)
    // *     returns 2: {0: "data"}
    
    var args = Array.prototype.slice.call(arguments),
                            retObj = {}, k, j = 0, i = 0, retArr = true;
    
    for (i=0; i < args.length; i++) {
        if (!(args[i] instanceof Array)) {
            retArr=false;
            break;
        }
    }
    
    if (retArr) {
        retArr = [];
        for (i=0; i < args.length; i++) {
            retArr = retArr.concat(args[i]);
        }
        return retArr;
    }
    var ct = 0;
    
    for (i=0, ct=0; i < args.length; i++) {
        if (args[i] instanceof Array) {
            for (j=0; j < args[i].length; j++) {
                retObj[ct++] = args[i][j];
            }
        } else {
            for (k in args[i]) {
                if (args[i].hasOwnProperty(k)) {
                    if (parseInt(k, 10)+'' === k) {
                        retObj[ct++] = args[i][k];
                    } else {
                        retObj[k] = args[i][k];
                    }
                }
            }
        }
    }
    return retObj;
}
