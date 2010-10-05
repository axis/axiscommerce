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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

var CategoryGrid = {
    
    expandedNodeIds: [],
    
    /**
     * @property {Axis.grid.GridTree} el
     */
    el: null,
    
    /**
     * @property {Axis.data.Record} record
     */
    record: null,
    
    collapseAll: function() {
        CategoryGrid.el.store.each(function(r) {
            CategoryGrid.el.store.collapseNode(r);
        })
    },
    
    /**
     * @param {Ext.data.Record} record
     */
    edit: function(record) {
        var category = record || CategoryGrid.el.selModel.getSelected();
        
        if (category.get('lvl') == 0) {
            return false;
        }
        
        Category.load(category.get('id'));
    },
    
    expandAll: function() {
        CategoryGrid.el.store.each(function(r) {
            CategoryGrid.el.store.expandNode(r);
        })
    },
    
    /**
     * Collect expanded nodes
     */
    onBeforeLoad: function() {
        CategoryGrid.expandedNodeIds = [];
        CategoryGrid.el.store.each(function(r) {
            if (CategoryGrid.el.store.isExpandedNode(r)) {
                CategoryGrid.expandedNodeIds.push(r.id);
            }
        });
    },
    
    /**
     * @param {Ext.data.Record} record
     * @param {Ext.data.Record} destination
     * @param {String}          mode [above|below|append]
     */
    onBeforeMove: function(record, destination, mode) {
        // nested set can't insert node below: only moveBefore and moveTo are supported
        if (mode == 'below') {
            var nextRow = CategoryGrid.el.store.getAt(CategoryGrid.el.store.indexOf(destination) + 1);
            
            if (!nextRow || nextRow.get('lvl') < destination.get('lvl')) {
                mode = 'append';
                destination = CategoryGrid.el.store.getNodeParent(destination);
            } else if (nextRow.get('lvl') == destination.get('lvl')
                || (nextRow.get('lvl') > destination.get('lvl')
                    && CategoryGrid.el.store.isExpandedNode(destination))) {
                // if destination has no children => add after it
                // or if destination is expanded => add before first child of destination
                mode = 'above';
                destination = nextRow;
            } else { // if destination is collapsed => add below destination
                var sibling = CategoryGrid.el.store.getNodeNextSibling(destination);
                if (!sibling) {
                    mode = 'append';
                    destination = CategoryGrid.el.store.getNodeParent(destination);
                } else {
                    mode = 'above';
                    destination = sibling;
                }
            }
        }
        
        // moveType mapping
        moveType = [];
        moveType['append'] = 'moveTo';
        moveType['above'] = 'moveBefore';
        
        Ext.Ajax.request({
            url: Axis.getUrl('catalog_category/move/'),
            method: 'post',
            params: {
                catId: record.id,
                newParentId: destination.id,
                moveType: moveType[mode]
            },
            success: CategoryGrid.reload
        });
    },
    
    /**
     * Add uncategorized row
     * Expand nodes that was expanded before reload
     */
    onLoad: function(store, records, options) {
        CategoryGrid.el.store.insert(0, new CategoryGrid.record({
            id: 0,
            lft: 0,
            rgt: 0,
            lvl: 0,
            name: 'Uncategorized'.l(),
            site_id: 0,
            status: 1,
            disable_edit: 1,
            disable_remove: 1
        }));
        for (var i = 0, limit = CategoryGrid.expandedNodeIds.length; i < limit; i++) {
            var recordToExpand = CategoryGrid.el.store.getById(CategoryGrid.expandedNodeIds[i]);
            if (!recordToExpand) { // if record was deleted previously
                continue;
            }
            CategoryGrid.el.store.expandNode(recordToExpand);
        }
    },
    
    /**
     * Reload productGrid
     * 
     * @param {Axis.grid.GridTree} grid
     * @param {int} index
     * @param {Ext.EventObject} e
     */
    onRowClick: function(grid, index, e) {
        var el = Ext.get(Ext.lib.Event.getTarget(e));
        if (el.hasClass('x-grid3-row-checker') 
            || el.hasClass('ux-maximgb-treegrid-elbow-active')
            || el.hasClass('ux-row-action-item')) {
            
            return false;
        }
        
        var record = CategoryGrid.el.store.getAt(index);
        
        ProductGrid.el.store.baseParams.catId = record.get('id');
        ProductGrid.el.store.baseParams.siteId = record.get('site_id');
        
        ProductGrid.el.store.load();
    },
    
    reload: function() {
        CategoryGrid.el.store.reload();
    },
    
    /**
     * @param {Array} record [Ext.data.Record]
     */
    remove: function(records) {
        var selectedItems = records || CategoryGrid.el.selModel.getSelections();
        
        if (!selectedItems.length || !confirm('Are you sure?'.l())) {
            return;
        }
        
        var data = {};
        for (var i = 0; i < selectedItems.length; i++) {
//            if (selectedItems[i].get('lvl') == 0) {
//                alert('Root category cannot be deleted, and will be skipped'.l());
//                continue;
//            }
            data[i] = selectedItems[i].id;
        }
        
        Ext.Ajax.request({
            url: Axis.getUrl('catalog_category/delete/'),
            method: 'post',
            params: { data: Ext.encode(data) },
            callback: function() {
                CategoryGrid.reload();
            }
        });
    }
    
};

Ext.onReady(function() {
    
    CategoryGrid.record = Ext.data.Record.create([
        { name: 'id', type: 'int' },
        { name: 'name' },
        { name: 'lvl', type: 'int' },
        { name: 'lft', type: 'int' },
        { name: 'rgt', type: 'int' },
        { name: 'site_id', type: 'int' },
        { name: 'status' },
        { name: 'disable_remove' },
        { name: 'disable_edit' }
    ]);
    
    var ds = new Axis.data.NestedSetStore({
        autoLoad: true,
        listeners: {
            'beforeload': CategoryGrid.onBeforeLoad,
            'load': CategoryGrid.onLoad
        },
        reader: new Ext.data.JsonReader({
            root: 'data',
            idProperty: 'id'
        }, CategoryGrid.record),
        rootFieldName: 'site_id',
        url: Axis.getUrl('catalog_category/get-flat-tree')
    });
    
    var actions = new Ext.ux.grid.RowActions({
        header:'Actions'.l(),
        actions:[{
            hideIndex: 'disable_edit',
            iconCls: 'icon-folder-edit',
            tooltip: 'Edit'.l()
        }, {
            hideIndex: 'disable_remove',
            iconCls: 'icon-folder-delete',
            tooltip: 'Delete'.l()
        }],
        callbacks: {
            'icon-folder-edit': function(grid, record, action, row, col) {
                CategoryGrid.edit(record);
            },
            'icon-folder-delete': function(grid, record, action, row, col) {
                CategoryGrid.remove([record]);
            }
        }
    });
    
    var cm = new Ext.grid.ColumnModel({
        columns: [{
            dataIndex: 'name',
            header: 'Name'.l(),
            id: 'name',
            renderer: function (value, meta, record) {
                if (record.get('status') != 'enabled') {
                    value = '<span class="disabled">' + value + '</span>';
                }
                
                meta.attr += 'ext:qtip="ID: ' + record.get('id') + '"';
                return value;
            }
        }, actions]
    });
    
    CategoryGrid.el = new Axis.grid.GridTree({
        autoExpandColumn: 'name',
        cm: cm,
        collapseMode: 'mini',
        ds: ds,
        id: 'grid-category-list',
        master_column_id: 'name',
        plugins: [actions],
        region: 'west',
        width: 280,
        listeners: {
            'beforerowmoved': CategoryGrid.onBeforeMove,
            'rowclick': CategoryGrid.onRowClick
        },
        tbar: [/*{
            handler: function() {
                Category.add();
            },
            icon: Axis.skinUrl + '/images/icons/add.png',
            text: 'Add'.l()
        },*/ {
            handler: function() {
                CategoryGrid.remove();
            },
            icon: Axis.skinUrl + '/images/icons/delete.png',
            text: 'Delete'.l()
        }, {
            handler: CategoryGrid.reload,
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            text: 'Reload'.l()
        }, '->', {
            handler: CategoryGrid.expandAll,
            icon: Axis.skinUrl + '/images/icons/expand-all.gif',
            overflowText: 'Expand'.l(),
            tooltip: 'Expand'.l()
        }, '-',  {
            handler: CategoryGrid.collapseAll,
            icon: Axis.skinUrl + '/images/icons/collapse-all.gif',
            overflowText: 'Collapse'.l(),
            tooltip: 'Collapse'.l()
        }]
    });
});
