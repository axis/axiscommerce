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

/**
 * Modified version of Ext.ux.maximgb.treegrid.GridPanel
 * GridPanel for standard Axis 2-columns layout
 */
Axis.grid.GridTree = Ext.extend(Ext.grid.GridPanel, {
    
    collapsible: true,
    
    ddGroup: 'grid-tree-dd-group',
    
    enableDragDrop: true,
    
    header: false,
    
    massAction: true,
    
    plugins: [],
    
    region: 'center',
    
    split: true,
    
    stripeRows: true,
    
    viewConfig: {
        emptyText: 'No records found'.l()
    },
    
    width: 220,
    
    getView: function() {
        if (!this.view) {
            this.view = new Axis.grid.NestedView(this.viewConfig);
        }
        return this.view;
    },
    
    initComponent: function() {
        if (this.massAction && !this.sm) {
            this.sm = new Ext.grid.CheckboxSelectionModel();
            this.cm.columns.splice(0, 0, this.sm);
        }
        this.plugins.push(new Axis.dd.GridRow());
        Axis.grid.GridTree.superclass.initComponent.call(this);
    },
    
    onClick: function(e) {
        var target = e.getTarget();
        var doDefault = true;
        
        if (Ext.fly(target).hasClass('ux-maximgb-treegrid-elbow-active')) {
            var index = this.getView().findRowIndex(target);
            var record = this.store.getAt(index);
            
            if (this.store.isExpandedNode(record)) {
                this.store.collapseNode(record);
            } else {
                this.store.expandNode(record);
            }
            
            doDefault = false;
        }
        
        if (doDefault) {
            Axis.grid.GridTree.superclass.onClick.call(this, e);
        }
    },
    
    onMouseDown: function(e) {
        var target = e.getTarget();

        if (!Ext.fly(target).hasClass('ux-maximgb-treegrid-elbow-active')) {
            Axis.grid.GridTree.superclass.onMouseDown.call(this, e);
        }
    },
    
    onDblClick: function(e) {
        var target = e.getTarget();
        var doDefault = true;
        
        if (!Ext.fly(target).hasClass('x-grid3-row-checker')) {
            var index = this.getView().findRowIndex(target);
            var record = this.store.getAt(index);
            
            if (record) {
                if (this.store.isExpandedNode(record)) {
                    this.store.collapseNode(record);
                } else {
                    this.store.expandNode(record);
                }
                this.selModel.deselectRow(index);
                doDefault = false;
            }
        }
        
        if (doDefault) {
            Axis.grid.GridTree.superclass.onDblClick.call(this, e);
        }
    }
    
});

Ext.grid.CheckboxSelectionModel.override({
    // FIX: added this function to check if the click occured on the checkbox.
    //      If so, then this handler should do nothing...
    handleDDRowClick: function(grid, rowIndex, e) {
        var t = Ext.lib.Event.getTarget(e);
        if (t.className != "x-grid3-row-checker") {
            Ext.grid.CheckboxSelectionModel.superclass.handleDDRowClick.apply(this, arguments);
        }
    }
});
Ext.grid.GridDragZone.override({
    getDragData: function (e) {
        var target = Ext.lib.Event.getTarget(e);
        var rowIndex = this.view.findRowIndex(target);
        if (rowIndex !== false) {
            var sm = this.grid.selModel;
            // FIX: Added additional check 
            //   !Ext.fly(target).hasClass("x-grid3-row-checker") 
            //   && !Ext.fly(target).hasClass("ux-maximgb-treegrid-elbow-active")
            if (!Ext.fly(target).hasClass("x-grid3-row-checker") 
                && !Ext.fly(target).hasClass("ux-maximgb-treegrid-elbow-active")
                && (!sm.isSelected(rowIndex) || e.hasModifier())) {
                
                sm.handleMouseDown(this.grid, rowIndex, e);
            }
            return {grid: this.grid, ddel: this.ddel, rowIndex: rowIndex, selections:sm.getSelections()};
        }

        return false;
    }
});