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
var RateController = function (){
    
    function _init(){
        Ext.onReady(function () {
            // 00
            // /\
            Ext.StoreMgr.lookup('core/site').load({
                callback: function(){
                    Ext.StoreMgr.lookup('location/country').load({
                        callback: function(){
                            Ext.StoreMgr.lookup('location/zone').load({
                                callback: function(){
                                    Ext.StoreMgr.lookup('shippingTable/rate').load();
                                }
                            });
                        }
                    });
                }
            });
            
            new Axis.Panel({
                items: [
                    Ext.getCmp('gridShippingTableRate')
                ]
            });
        });
    }
    _init();
    
    return {
        add: function() {
            var grid = Ext.getCmp('gridShippingTableRate');
            var store = Ext.StoreMgr.lookup('shippingTable/rate');
            grid.stopEditing();
            var emptyRow = new store.recordType({
                country_id: 0,
                zone_id: 0
            });
            grid.getStore().insert(0, emptyRow);
            grid.startEditing(0, 0);
        },
        save: function(){
            var store = Ext.StoreMgr.lookup('shippingTable/rate');
            var modified = store.getModifiedRecords();
            if (!modified.length) {
                return;
            }

            var data = {};

            for (var i = 0; i < modified.length; i++) {
                data[modified[i]['id']] = modified[i]['data'];
            }
            
            Ext.Ajax.request({
                url: Axis.getUrl('shipping-table/rate/batch-save'),
                params: {
                    data: Ext.encode(data)
                },
                callback: function() {
                    store.commitChanges();
                    store.reload();
                }
            });
        },
        remove: function(){
            var grid = Ext.getCmp('gridShippingTableRate');
            var selectedItems = grid.getSelectionModel().selections.items;

            if (!selectedItems.length || !confirm('Are you sure?'.l())) {
                return;
            }

            var data = {};

            for (var i = 0; i < selectedItems.length; i++) {
                if (!selectedItems[i]['data']['id']) {
                    continue
                };
                data[i] = selectedItems[i]['data']['id'];
            }
            Ext.Ajax.request({
                url: Axis.getUrl('shipping-table/rate/remove'),
                params: {
                    data: Ext.encode(data)
                },
                callback: function() {
                    grid.getStore().reload();
                }
            });
        },
        Export: function (){
            window.location = Axis.getUrl('shipping-table/rate/export');
        },
        Import: function (){
            
        }
    };
}(); 