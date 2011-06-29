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

Axis.dd.GridRow = Ext.extend(Ext.util.Observable, {
    
    constructor: function(config) {
        if (config) {
            Ext.apply(this, config);
        }
        this.events = {};
        Axis.dd.GridRow.superclass.constructor.call(this);
    },
    
    init: function(grid) {
        this.grid = grid;
        
        grid.on({
            render: { fn: this.onGridRender, scope: this, single: true }
        });
    },
    
    onGridRender: function (grid) {
        var self = this;
        
        this.target = new Ext.dd.DropTarget(grid.getEl(), {
            
            ddGroup: grid.ddGroup,
            
            grid: grid,
            
            /**
             * @cfg {String} expandDelay
             * The delay in milliseconds to wait before expanding a target tree node while dragging a droppable node
             * over the target (defaults to 1000)
             */
            expandDelay: 1000,
            
            expandNode: function(node) {
                grid.store.expandNode(node);
            },
            
            queueExpand: function(node) {
                this.expandProcId = this.expandNode.defer(this.expandDelay, this, [node]);
            },
            
            cancelExpand: function() {
                if (this.expandProcId) {
                    clearTimeout(this.expandProcId);
                    this.expandProcId = false;
                }
            },
            
            getDropPoint: function(e, data, dd) {
                //var srcData = dd.getDragData(e);
                
                var cursorY = e.getPageY();
                
                var dstEl = Ext.get(Ext.lib.Event.getTarget(e));
                var dstY = dstEl.getY();
                var dstH = dstEl.getHeight(); 
                
                if (cursorY < (dstY + 4)) {
                    return "above";
                } else if (cursorY > (dstY + dstH - 4)) {
                    return "below";
                } else {
                    return "append";
                }
            },
            
            isValidDropPoint: function(source, destination, mode) {
                if (!source || !destination) {
                    return false;
                }
                
                var isNestedSetStore = grid.store.leftFieldName ? true : false;
                if (isNestedSetStore) {
                    var lft     = grid.store.leftFieldName;
                    var rgt     = grid.store.rightFieldName;
                    var lvl     = grid.store.levelFieldName;
                    var rootLvl = grid.store.rootNodeLevel;
                    var root    = grid.store.rootFieldName;
                }
                
                for (var i = 0, limit = source.length; i < limit; i++) {
                    // self move
                    if (source[i].id == destination.id) {
                        return false;
                    }
                    
                    if (isNestedSetStore) {
                        // move between tree's
                        if (source[i].data[root] != destination.get(root)) {
                            return false;
                        }
                        // move to root level
                        if (destination.get(lvl) == rootLvl && mode != "append") {
                            return false;
                        }
                        // parent to child
                        if (source[i].data[lft] < destination.get(lft) &&
                            source[i].data[rgt] > destination.get(rgt)) {
                        
                            return false;
                        }
                    }
                }
                
                return true;
            },
            
            notifyDrop: function(dd, e, data) {
                this.removeDropIndicators(this.destRow);
                
                var sm = grid.getSelectionModel();
                var srcRows = sm.getSelections();
                var destRow = grid.store.getAt(dd.getDragData(e).rowIndex);
                var pt = this.getDropPoint(e);
                
                if (this.expandProcId) {
                    this.cancelExpand();
                }
                
                if (!this.isValidDropPoint(srcRows, destRow, pt)) {
                    return false;
                }
                
                for (var i = 0, limit = srcRows.length; i < limit; i++) {
                    this.moveRow(srcRows[i], destRow, pt);
                }
                
                grid.store.each(function(r) {
                    grid.getView().renderCellTreeUI(r, grid.store);
                })
                sm.selectRecords(srcRows);
                
                return true;
            },

            notifyOver: function(dd, e, data) {
                this.removeDropIndicators(this.destRow);
                this.removeDragIndicators(dd.getDragEl());
                
                var pt = this.getDropPoint(e, data, dd);
                var destIndex = this.grid.getView().findRowIndex(
                    Ext.lib.Event.getTarget(e)
                );
                var destRecord = grid.store.getAt(destIndex);
                var srcRows = grid.getSelectionModel().getSelections();
                this.destRow = this.grid.getView().getRow(destIndex);
                
                if (!this.isValidDropPoint(srcRows, destRecord, pt)) {
                    return this.dropNotAllowed;
                }
                
                if (!this.expandProcId
                    && pt == "append"
                    && grid.store.hasChildNodes(destRecord)
                    && !grid.store.isExpandedNode(destRecord)) {
                    
                    this.queueExpand(destRecord);
                } else if (pt != "append") {
                    this.cancelExpand();
                }
                
                var dragCls;
                var rowCls;
                if (pt == "above") {
                    dragCls = (destIndex == 0) ? 
                        "x-tree-drop-ok-above" : "x-tree-drop-ok-between";
                    rowCls = "x-grid-drag-insert-above";
                } else if (pt == "below") {
                    dragCls = !this.grid.getView().getRow(destIndex + 1) ? 
                        "x-tree-drop-ok-below" : "x-tree-drop-ok-between";
                    rowCls = "x-grid-drag-insert-below";
                } else {
                    dragCls = "x-tree-drop-ok-append";
                    rowCls = "x-grid-drag-append";
                }
                
                if (this.lastInsertClass != rowCls) {
                    Ext.fly(this.destRow).replaceClass(this.lastInsertClass, rowCls);
                    Ext.fly(dd.getDragEl()).addClass(dragCls);
                    this.lastInsertClass = rowCls;
                }
                
                return this.dropAllowed;
            },

            notifyOut: function(dd, e, data) {
                //this.removeDropIndicators(this.destRow);
            },
            
            /**
             * @param {Ext.data.Record} record
             * @param {Ext.data.Record} destination
             * @param {String}          mode [above|below|append]
             */
            moveRow: function(record, destination, mode) {
                this.grid.fireEvent('beforerowmoved', record, destination, mode);
                this.grid.fireEvent('rowmoved', record, destination, mode);
            },
            
            removeDropIndicators: function(dropEl) {
                if (!dropEl) {
                    return;
                }
                Ext.fly(dropEl).removeClass([
                    "x-grid-drag-insert-above",
                    "x-grid-drag-insert-below",
                    "x-grid-drag-append"
                ]);
                this.lastInsertClass = "_noclass";
            },
            
            removeDragIndicators: function(dragEl) {
                Ext.fly(dragEl).removeClass([
                    "x-tree-drop-ok-between",
                    "x-tree-drop-ok-append",
                    "x-tree-drop-ok-below",
                    "x-tree-drop-ok-above"
                ]);
            }
        });
    },

    onBeforeDestroy: function(grid) {
        // if we previously registered with the scroll manager, unregister
        // it (if we don't it will lead to problems in IE)
        Ext.dd.ScrollManager.unregister(grid.getView().getEditorParent());
    }
});