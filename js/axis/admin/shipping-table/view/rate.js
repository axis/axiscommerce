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
Ext.onReady(function () {
    
    var store                = Ext.StoreMgr.lookup('shippingTable/rate');
    var storeCoreSite        = Ext.StoreMgr.lookup('core/site');
    var storeLocationCountry = Ext.StoreMgr.lookup('location/country');
    var storeLocationZone    = Ext.StoreMgr.lookup('location/zone');
    
    var columnModel = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: "Id".l(),
            width: 90,
            dataIndex: 'id'
        }, {
            header: "Site".l(),
            id : 'name',
            dataIndex: 'site_id',
            renderer: function(value) {
                var row = storeCoreSite.getById(value);
                return row ? row.get('name') : value;
            },
            editor: new Ext.form.ComboBox({
                editable: false,
                typeAhead: true,
                triggerAction: 'all',
                store: storeCoreSite,
                displayField: 'name',
                valueField: 'id',
                mode: 'local',
                allowBlank: false
            }),
            filter: {
                editable: false,
                store: storeCoreSite
            }
        }, {
            header: "Country".l(),
            width:120,
            dataIndex: 'country_id',
            renderer: function(value, metaData, record) {
                var row = storeLocationCountry.getById(value);
                var rowZone = storeLocationZone.getById(record.get('zone_id'));
                if (value !== rowZone.get('country_id')) {
                    record.set('zone_id', 0);
                }
                return row ? row.get('name') : value;
            },
            editor: new Ext.form.ComboBox({
                editable: false,
                typeAhead: true,
                triggerAction: 'all',
                store: storeLocationCountry,
                displayField: 'name',
                valueField: 'id',
                mode: 'local',
                allowBlank: false
            }),
            filter: {
                editable: false,
                store: storeLocationCountry
            }
        }, {
            header: "Zone".l(),
            width: 120,
            dataIndex: 'zone_id',
            renderer: function(value, metaData, record) {
                var row = storeLocationZone.getById(value);
                if (record.get('country_id') !== row.get('country_id')) {
                    record.set('zone_id', 0);
                }
                return row ? row.get('name') : value;
            },
            editor: new Ext.form.ComboBox({
                editable: false,
                typeAhead: true,
                triggerAction: 'all',
                store: storeLocationZone,
                displayField: 'name',
                valueField: 'id',
                mode: 'local',
                allowBlank: false
            }),
            filter: {
                editable: false,
                store: storeLocationZone
            }
        }, {
            header: "Postcode".l(),
            width: 80,
            dataIndex: 'zip',
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }, {
            header: "Value".l(),
            width: 80,
            dataIndex: 'value',
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }, {
            header: "Price".l(),
            width: 80,
            dataIndex: 'price',
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }]
    });
    
    new Axis.grid.EditorGridPanel({
        id: 'gridShippingTableRate',
//        autoExpandColumn: 'name',
        ds: store,
        cm: columnModel,
        plugins: [new Axis.grid.Filter()],
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler : RateController.add
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler: RateController.save
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler : RateController.remove
        }, new Ext.Toolbar.Separator(), {
            text: 'Export'.l(),
            icon: Axis.skinUrl + '/images/icons/brick_go.png',
            cls: 'x-btn-text-icon',
            handler : RateController.Export
        }, {
            text: 'Import'.l(),
            icon: Axis.skinUrl + '/images/icons/brick_add.png',
            cls: 'x-btn-text-icon',
            handler : RateController.Import
        }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler : function() {
                store.reload();
            }
        }], 
        bbar: new Axis.PagingToolbar({
            store: store
        })
    });
});