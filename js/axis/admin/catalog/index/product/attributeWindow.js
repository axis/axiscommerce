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

Axis.AttributeWindow = Ext.extend(Ext.util.Observable, {
    
    hideInputable: false,
    
    singleSelect: false,
    
    /**
     * Array of callbacks to filter attribute grid
     * 
     * @param {Array} filters
     */
    filters: [],
    
    constructor: function(config) {
        Ext.apply(this, config);
        
        this.events = {
            /**
             * @event cancel
             * Fires when cancel button was pressed
             */
            'cancelpress': true,
            /**
             * @event ok
             * Fires when ok button was pressed
             * @param {Array} Selected records
             */
            'okpress': true
        };
        Axis.AttributeWindow.superclass.constructor.call(this);
        
        var ds = new Ext.ux.maximgb.treegrid.AdjacencyListStore({
            autoLoad: true,
            reader: new Ext.data.JsonReader({
                idProperty: 'id'
            }, [
                {name: 'id'}, // this is not integer
                {name: 'leaf'},
                {name: 'text'},
                {name: 'code'},
                {name: 'option_code'},
                {name: 'option_name'},
                {name: 'value_name'},
                {name: 'input_type', type: 'int'},
                {name: 'languagable', type: 'int'},
                {name: 'option_id', type: 'int'},
                {name: 'value_id', type: 'int'},
                {name: 'parent'}
            ]),
            paramNames: {
                active_node: 'node'
            },
            leaf_field_name: 'leaf',
            parent_id_field_name: 'parent',
            url: Axis.getUrl('catalog_index/get-options'),
            listeners: {
                load: {
                    scope: this,
                    fn: this.onLoad
                }
            }
        });
        
        var sm = new Ext.grid.CheckboxSelectionModel({
            listeners: {
                rowselect: {
                    scope: this,
                    fn: this.onRowSelect
                },
                rowdeselect: {
                    scope: this,
                    fn: this.onRowDeselect
                }
            }
        });
        
        var cm = new Ext.grid.ColumnModel({
            columns: [sm, {
                dataIndex: 'text',
                header: 'Name'.l(),
                id: 'text'
            }]
        });
        
        this.attributeGrid = new Axis.grid.GridTree({
            autoExpandColumn: 'text',
            cm: cm,
            ds: ds,
            enableDragDrop: false,
            sm: sm,
            master_column_id: 'text',
            region: 'center',
            plugins: [
//                new Ext.ux.grid.Search({
//                    mode: 'local',
////                    align: 'left',
//                    iconCls: false,
//                    position: 'top',
//                    width: 200,
//                    minLength: 0
//                })
            ],
            tbar: []
        });
        
        this.attributeGrid.getTopToolbar().addFill();
        this.attributeGrid.getTopToolbar().add({
            handler: function() {
                this.attributeGrid.store.load();
            },
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            scope: this,
            text: 'Reload'.l()
        });
        
        this.window = new Axis.Window({
            border: false,
            layout: 'border',
            maximizable: true,
            split: true,
            title: 'Select Attributes to Add'.l(),
            width: 350,
            items: [
                this.attributeGrid
            ],
            buttons: [{
                icon: Axis.skinUrl + '/images/icons/accept.png',
                text: 'Ok'.l(),
                scope: this,
                handler: this.okPress
            }, {
                icon: Axis.skinUrl + '/images/icons/cancel.png',
                text: 'Cancel'.l(),
                scope: this,
                handler: this.cancelPress
            }],
            listeners: {
                hide: {
                    scope: this,
                    fn: this.hide
                }
            }
        });
    },
    
    destroy: function() {
        if (this.attributeGrid) {
            this.attributeGrid.destroy();
        }
        this.purgeListeners();
    },
    
    /**
     * If true, you'll be able to select only one value per option
     * Used true with product properties
     * 
     * @param {Boolean} flag
     */
    setSingleSelect: function(flag) {
        this.singleSelect = flag;
        return this;
    },
    
    /**
     * Add filter function to filters array
     * This function accepts record and must to return boolean
     * 
     * @param {Object} filter Callback function
     */
    setFilter: function(filter) {
        this.filters = [filter];
        this.applyFilter();
        return this;
    },
    
    clearFilter: function() {
        this.filters = [];
        this.applyFilter();
        return this;
    },
    
    applyFilter: function() {
        if (!this.attributeGrid.rendered) {
            return this;
        }
        if (this.filters.length) {
            this.attributeGrid.store.filterBy(function(record) {
                var result = true;
                Ext.each(this.filters, function(filter) {
                    if (!filter(record)) {
                        return (result = false);
                    }
                });
                return result;
            }, this);
        } else {
            this.attributeGrid.store.clearFilter();
        }
        
        return this;
    },
    
    onLoad: function(store, records, options) {
        this.applyFilter();
    },
    
    onRowSelect: function(sm, index, record) {
        var store = this.attributeGrid.store;

        if (store.isLoadedNode(record)) {
            this.selectRecord(record);
        } else { // load and select all child nodes
            params = {};
            params[store.paramNames.active_node] = record.id;
            store.load({
                add: true,
                params: params,
                scope: this,
                callback: function(r, options, success) {
                    var record = this.attributeGrid.store.getById(
                        options.params[this.attributeGrid.store.paramNames.active_node]
                    );
                    if (success && record) {
                        this.attributeGrid.store.setNodeLoaded(record, true);
                        this.selectRecord(record);
                    }
                }
            });
        }
    },
    
    onRowDeselect: function(sm, index, record) {
        var ds = this.attributeGrid.store;
        Ext.each(ds.getNodeChildren(record), function(r) {
            sm.deselectRow(ds.indexOf(r));
        }, this);
    },
    
    selectRecord: function(record) {
        var sm = this.attributeGrid.getSelectionModel();
        var ds = this.attributeGrid.store;
        
        if (!this.singleSelect) {
            sm.selectRecords(ds.getNodeChildren(record), true);
        } else {
            if (null === record.get('parent')) { // option (first level)
                // deselect children
                var children = ds.getNodeChildren(record);
                var hasSelected = false;
                
                Ext.each(children, function(r) {
                    if (hasSelected) {
                        return false;
                    }
                    if (sm.isSelected(r)) {
                        hasSelected = true;
                    }
                }, this);
                
                // select first record if no records were selected yet
                if (children.length && !hasSelected) {
                    sm.selectRecords([children[0]], true);
                }
            } else { // value (second level)
                var parent = ds.getNodeParent(record);
                // deselect siblings
                Ext.each(ds.getNodeChildren(parent), function(r) {
                    if (r.get('value_id') == record.get('value_id')) {
                        return;
                    }
                    if (sm.isSelected(r)) {
                        sm.deselectRow(ds.indexOf(r));
                    }
                });
                // select parent
                if (!sm.isSelected(parent)) {
                    sm.selectRecords([parent], true);
                }
            }
        }
    },
    
    deselectAll: function() {
        if (this.attributeGrid.rendered) {
            this.attributeGrid.getSelectionModel().clearSelections();
        }
        return this;
    },
    
    hide: function() {
        this.deselectAll();
        this.window.hide();
    },
    
    show: function() {
        this.deselectAll();
        this.window.show();
    },
    
    okPress: function() {
        if (false === this.fireEvent('okpress', this.attributeGrid.getSelectionModel().getSelections())) {
            return;
        }
        this.hide();
    },
    
    cancelPress: function() {
        this.fireEvent('cancelpress');
        this.hide();
    }
    
});
