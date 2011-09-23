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

//Ext.onReady(function() {
    var ds = new Axis.data.NestedSetStore({
        url: Axis.getUrl('catalog/category/list'),
        autoLoad: true,
        reader: new Ext.data.JsonReader({
            root: 'data',
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
    
    var checkColumn = new Axis.grid.CheckColumn({
        dataIndex: 'belongs_to',
        header: 'Belongs to'.l(),
        width: 100
    });
    
    var cm = new Ext.grid.ColumnModel({
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
        }, checkColumn]
    });
    
    var grid = new Axis.grid.GridTree({
        autoExpandColumn: 'name',
        border: false,
        cm: cm,
        ds: ds,
        enableDragDrop: false,
        forceLayout: true,
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
        id: 'grid-window-category-list',
        massAction: false,
        master_column_id: 'name',
        plugins: [checkColumn]
    });
    
    discountWindowFormCategoriesTab = {
        el: grid,
        onLoad: function(data) {
            
            for (var i = 0, limit = data.length; i < limit; i++) {
                if (!(r = this.el.store.getById(data[i]))) {

                    continue;
                }
//                console.log(r);
//                r.data.belongs_to = 1;
                r.set('belongs_to', 1);
//                r.markDirty(false);
                while ((r = this.el.store.getNodeParent(r))) {
                    this.el.store.expandNode(r);
                }
            }
        } 
    }
//});