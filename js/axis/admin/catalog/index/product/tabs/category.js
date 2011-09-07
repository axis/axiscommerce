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

var ProductCategoryGrid = {

    el: null,

    clearData: function() {
        Ext.Ajax.request({
            url: Axis.getUrl('catalog/category/list'),
            success: function(response) {
                var categories = Ext.decode(response.responseText).data;
                var belongs_to = [];
                var selected = null;
                if ((selected = CategoryGrid.el.selModel.getSelected())) {
                    belongs_to.push(selected.id);
                    Ext.each(categories, function(item) {
                        if (selected.id == item.id) {
                            item.belongs_to = 1;
                            return false;
                        }
                    });
                }
                ProductCategoryGrid.loadData({
                    categories: categories,
                    belongs_to: belongs_to
                });
            }
        });
    },

    loadData: function(data) {
        // fill categories grid, and expand belongs_to nodes
        ProductCategoryGrid.el.store.loadData(data.categories);
        for (var i = 0, limit = data.belongs_to.length; i < limit; i++) {
            if (!(r = ProductCategoryGrid.el.store.getById(
                        data.belongs_to[i]))) {

                continue;
            }

            do {
                ProductCategoryGrid.el.store.expandNode(r);
            } while ((r = ProductCategoryGrid.el.store.getNodeParent(r)));
        }
    },

    getData: function() {
        var records = ProductCategoryGrid.el.store.data.items;
        var data = {
            ids: {},
            site_ids: {}
        };

        for (var i = records.length - 1; i >= 0; i--) {
            if (!records[i]['data'].belongs_to) {
                continue;
            }
            data['ids'][records[i].data['id']] = records[i]['data'].id;
            data['site_ids'][records[i].data['site_id']] = records[i]['data'].site_id;
        }

        return {
            'category': data
        };
    }

};

Ext.onReady(function() {

    var categoryStore = new Axis.data.NestedSetStore({
        mode: 'local',
        reader: new Ext.data.JsonReader({
            idProperty: 'id',
            fields: [
                {name: 'id', type: 'int'},
                {name: 'name'},
                {name: 'lvl', type: 'int'},
                {name: 'lft', type: 'int'},
                {name: 'rgt', type: 'int'},
                {name: 'site_id', type: 'int'},
                {name: 'status'},
                {name: 'disable_remove'},
                {name: 'disable_edit'},
                {name: 'belongs_to', type: 'int'}
            ]
        }),
        rootFieldName: 'site_id'
    });

    var categoryBelongsTo = new Axis.grid.CheckColumn({
        dataIndex: 'belongs_to',
        header: 'Belongs to'.l(),
        width: 100
    });

    var categoryCols = new Ext.grid.ColumnModel({
        defaults: {
            sortable: false,
            menuDisabled: true
        },
        columns: [{
            dataIndex: 'name',
            header: 'Name'.l(),
            id: 'name',
            renderer: function (value, meta, record) {
                if (record.get('status') != 'enabled') {
                    value = '<span class="disabled">' + value + '</span>';
                }

                meta.attr = 'ext:qtip="ID: ' + record.get('id') + '"';
                return value;
            }
        }, categoryBelongsTo]
    });

    ProductCategoryGrid.el = ProductWindow.categoryGrid = new Axis.grid.GridTree({
        autoExpandColumn: 'name',
        border: false,
        cm: categoryCols,
        ds: categoryStore,
        enableDragDrop: false,
        sm: new Ext.grid.RowSelectionModel({
            singleSelect:true
        }),
        viewConfig: {
            emptyText: 'No records found'.l(),
            getRowClass: function(record, rowIndex, rowParams, ds) {
                if (record.get('belongs_to')) {
                    return 'x-grid3-row-active';
                }
            }
        },
        title: 'Categories'.l(),
        id: 'grid-product-window-category-list',
        massAction: false,
        master_column_id: 'name',
        plugins: [categoryBelongsTo]
    });

    ProductWindow.addTab(ProductCategoryGrid.el, 60);
    ProductWindow.dataObjects.push(ProductCategoryGrid);

});
