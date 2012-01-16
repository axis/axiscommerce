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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

var ProductGrid = {

    /**
     * @property {Axis.grid.EditorGridPanel} el
     */
    el: null,

    /**
     * @property {integer} pageSize
     */
    pageSize: 25,

    /**
     * @property {Axis.data.Record} record
     */
    record: null,

    add: function() {
        Product.add();
    },

    edit: function(record) {
        var product = record || ProductGrid.el.selModel.getSelected();
        Product.load(product.get('id'));
    },

    getCategoryId: function() {
        return ProductGrid.el.store.baseParams.catId;
    },

    getSiteId: function() {
        return ProductGrid.el.store.baseParams.siteId;
    },

    reload: function() {
        ProductGrid.el.store.reload();
    },

    /**
     * @param {string} mode[category|site|db]
     * @param {Array} records [Ext.data.Record]
     */
    remove: function(mode, records) {
        var selectedItems = records || ProductGrid.el.selModel.getSelections();

        if (!selectedItems.length || !confirm('Are you sure?'.l())) {
            return;
        }

        var data = {};
        for (var i = 0; i < selectedItems.length; i++) {
            data[i] = selectedItems[i].id;
        }

        var url = '';
        var params = {};

        switch (mode) {
            case 'category':
                url = 'remove-product-from-category';
                var category = ProductGrid.getCategoryId();
                if (!category) {
                    return alert('Select parent category or site, on the left panel'.l());
                }
                params = {
                    prodIds: Ext.encode(data),
                    catId: category
                };
            break;
            case 'site':
                url = 'remove-product-from-site';
                var site = ProductGrid.getSiteId();
                if (!site) {
                    return alert('Select parent category or site, on the left panel'.l());
                }
                params = {
                    prodIds: Ext.encode(data),
                    siteId: ProductGrid.getSiteId()
                };
            break;
            case 'db':
                url = 'remove';
                params = {
                    data: Ext.encode(data)
                };
            break;
        }

        Ext.Ajax.request({
            url: Axis.getUrl('catalog/product/') + url,
            params: params,
            callback: function() {
                ProductGrid.reload();
            }
        });
    },

    save: function() {
        var modified = ProductGrid.el.store.getModifiedRecords();
        if (!modified.length) {
            return false;
        }

        var data = {};
        for (var i = 0, n = modified.length; i < n; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }

        var jsonData = Ext.encode(data);
        Ext.Ajax.request({
            url: Axis.getUrl('catalog/product/batch-save'),
            params: {
                data: jsonData,
                siteId: ProductGrid.getSiteId()
            },
            callback: function() {
                ProductGrid.el.store.commitChanges();
                ProductGrid.reload();
            }
        })
    }
};

var ProductBuffer = {

    data: {},

    clear: function() {
        ProductBuffer.data = {};
        Ext.getCmp('menu-buffer-paste').disable();
    },

    cut: function() {
        var selectedItems = ProductGrid.el.selModel.getSelections();
        if (!selectedItems.length) {
            return;
        }
        for (var i = 0, limit = selectedItems.length; i < limit; i++) {
            var action = {};
            if (ProductBuffer.data[selectedItems[i].id] !== undefined) {
                action = ProductBuffer.data[selectedItems[i].id].action;
            }
            action[ProductGrid.getCategoryId()] = 'cut';
            ProductBuffer.data[selectedItems[i].id] = {
                product_id: selectedItems[i].id,
                action: action
            };
        }
        Ext.getCmp('menu-buffer-paste').enable();
    },

    copy: function() {
        var selectedItems = ProductGrid.el.selModel.getSelections();
        if (!selectedItems.length) {
            return;
        }
        for (var i = 0, limit = selectedItems.length; i < limit; i++) {
            var action = {};
            if (ProductBuffer.data[selectedItems[i].id] !== undefined) {
                action = ProductBuffer.data[selectedItems[i].id].action;
            }
            action[ProductGrid.getCategoryId()] = 'copy';
            ProductBuffer.data[selectedItems[i].id] = {
                product_id: selectedItems[i].id,
                action: action
            };
        }
        Ext.getCmp('menu-buffer-paste').enable();
    },

    /**
     *
     * @param {int} destination categoryId
     */
    paste: function(destination) {
        if (!ProductBuffer.data) {
            alert('Buffer is empty'.l());
            return;
        }
        Ext.Ajax.request({
            url: Axis.getUrl('catalog/product/batch-move'),
            params: {
                data: Ext.encode(ProductBuffer.data),
                destination: destination
            },
            callback: function() {
                ProductGrid.reload();
            }
        });
    }

};

Ext.onReady(function() {

    ProductGrid.record = Ext.data.Record.create([
        {name: 'id', type: 'int'},
        {name: 'name'},
        {name: 'sku'},
        {name: 'quantity', type: 'float'},
        {name: 'price', type: 'float'},
        {name: 'is_active', type: 'int'}
    ]);

    var massActions = new Ext.menu.Menu({
        id: 'product-mass-actions',
        items: [{
            text: 'Edit'.l(),
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            handler: function(){
                ProductGrid.edit();
            }
        }, {
            text: 'Buffer'.l(),
            icon: Axis.skinUrl + '/images/icons/note.png',
            id: 'menu-buffer',
            menu: {
                items: [{
                    text: 'Cut'.l(),
                    icon: Axis.skinUrl + '/images/icons/page_edit.png',
                    handler: ProductBuffer.cut
                }, {
                    text: 'Copy'.l(),
                    icon: Axis.skinUrl + '/images/icons/copy_multiple.png',
                    handler: ProductBuffer.copy
                }, {
                    text: 'Paste'.l(),
                    icon: Axis.skinUrl + '/images/icons/move.png',
                    id: 'menu-buffer-paste',
                    disabled: true,
                    handler: function() {
                        ProductBuffer.paste(ProductGrid.getCategoryId());
                    }
                }, {
                    text: 'Clear'.l(),
                    icon: Axis.skinUrl + '/images/icons/page_white.png',
                    handler: ProductBuffer.clear
                }]
            }
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            menu: {
                items: [{
                    text: 'From category'.l(),
                    handler: function() {
                        ProductGrid.remove('category');
                    }
                }, {
                    text: 'From site'.l(),
                    handler: function() {
                        ProductGrid.remove('site');
                    }
                }, {
                    text: 'From DB'.l(),
                    handler: function() {
                        ProductGrid.remove('db');
                    }
                }]
            }
        }]
    });

    var ds = new Ext.data.Store({
        autoLoad: true,
        baseParams: {
            limit: ProductGrid.pageSize
        },
        reader: new Ext.data.JsonReader({
            idProperty: 'id',
            root: 'data',
            totalProperty: 'count'
        }, ProductGrid.record),
        remoteSort: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        },
        url: Axis.getUrl('catalog/product/list')
    });

    var actions = new Ext.ux.grid.RowActions({
        //header:'Actions'.l(),
        actions:[{
            iconCls: 'icon-page-edit',
            tooltip: 'Edit'.l()
        }, {
            iconCls: 'icon-page-delete',
            tooltip: 'Delete'.l()
        }],
        callbacks: {
            'icon-page-edit': function(grid, record, action, row, col) {
                ProductGrid.edit(record);
            },
            'icon-page-delete': function(grid, record, action, row, col) {
                ProductGrid.remove('db', [record]);
            }
        }
    });

    var status = new Axis.grid.CheckColumn({
        dataIndex: 'is_active',
        header: 'Status'.l(),
        width: 100,
        filter: {
            editable: false,
            resetValue: 'reset',
            store: new Ext.data.ArrayStore({
                data: [[0, 'Disabled'], [1, 'Enabled']],
                fields: ['id', 'name']
            })
        }
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true,
            table: 'cp'
        },
        columns: [{
            dataIndex: 'id',
            header: 'Id'.l(),
            width: 100
        }, {
            dataIndex: 'name',
            header: 'Name'.l(),
            id: 'name',
            table: 'cpd',
            filter: {
                operator: 'LIKE'
            }
        }, {
            dataIndex: 'sku',
            editor: new Ext.form.TextField({
                allowBlank: false
            }),
            header: 'SKU'.l(),
            width: 180,
            filter: {
                operator: 'LIKE'
            }
        }, {
            align: 'right',
            dataIndex: 'quantity',
            editor: new Ext.form.NumberField({
                allowBlank: true,
                allowNegative: false
            }),
            header: 'Quantity'.l(),
            width: 100
        }, {
            align: 'right',
            dataIndex: 'price',
            editor: new Ext.form.NumberField({
                allowBlank: true,
                allowNegative: false
            }),
            header: 'Price'.l(),
            renderer: function(value) {
                return value.toFixed(2);
            },
            width: 100
        },
        status,
        actions]
    });

    ProductGrid.el = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'name',
        ds: ds,
        cm: cm,
        id: 'grid-product-list',
        plugins: [actions, status, new Axis.grid.Filter()],
        listeners: {
            'rowdblclick': function(grid, index, e) {
                ProductGrid.edit(ProductGrid.el.store.getAt(index));
            }
        },
        bbar: new Axis.PagingToolbar({
            pageSize: ProductGrid.pageSize,
            store: ds
        }),
        tbar: [{
            handler: Product.add,
            icon: Axis.skinUrl + '/images/icons/add.png',
            text: 'Add Product'.l()
        }, {
            handler: function() {
                ProductGrid.remove('db');
            },
            icon: Axis.skinUrl + '/images/icons/delete.png',
            text: 'Delete'.l()
        }, {
            text: 'Actions'.l(),
            icon: Axis.skinUrl + '/images/icons/menu_action.png',
            menu: massActions
        }, {
            handler: ProductGrid.save,
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            text: 'Save'.l()
        }, '->', {
            handler: ProductGrid.reload,
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            text: 'Reload'.l()
        }]
    });
});
