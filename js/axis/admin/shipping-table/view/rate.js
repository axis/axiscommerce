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
Ext.onReady(function () {
    
    var store                = Ext.StoreMgr.lookup('shippingTable/rate');
    var storeCoreSite        = Ext.StoreMgr.lookup('core/site');
    var storeLocationCountry = Ext.StoreMgr.lookup('location/country');
    var storeLocationZone    = Ext.StoreMgr.lookup('location/zone');
    
    //@todo clone store
    var storeCoreSiteForEditor = new Ext.data.Store({
        recordType: storeCoreSite.recordType
    });
    storeCoreSite.on('load', function(){
        storeCoreSiteForEditor.add(storeCoreSite.getRange());
    });
    
    var storeLocationCountryForEditor = new Ext.data.Store({
        recordType: storeLocationCountry.recordType
    });
    storeLocationCountry.on('load', function(){
        storeLocationCountryForEditor.add(storeLocationCountry.getRange());
    });
    
    var storeLocationZoneForEditor = new Ext.data.Store({
        recordType: storeLocationZone.recordType
    });
    storeLocationZone.on('load', function(){
        storeLocationZoneForEditor.add(storeLocationZone.getRange());
    });
    
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
            id : 'site',
            dataIndex: 'site_id',
            renderer: function(value) {
                var row = storeCoreSite.getById(value);
                return row ? row.get('name') : value;
            },
            editor: new Ext.form.ComboBox({
                editable: false,
                typeAhead: true,
                triggerAction: 'all',
                store: storeCoreSiteForEditor,
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
                store: storeLocationCountryForEditor,
                displayField: 'name',
                valueField: 'id',
                mode: 'local',
                allowBlank: false
            }),
            filter: {
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
                store: storeLocationZoneForEditor,
                displayField: 'name',
                valueField: 'id',
                mode: 'local',
                allowBlank: false,
                listeners:  {
                    expand: function(combo) {
                        var r = combo.gridEditor.record;
                        storeLocationZoneForEditor.filterBy(function(rec){
                            return rec.get('country_id') == r.get('country_id') 
                                || rec.get('zone_id') == 0;
                        });
                    }
                }
            }),
            filter: {
                store: storeLocationZone
            }
        }, {
            header: "Zip".l(),
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
        autoExpandColumn: 'site',
        ds: store,
        cm: columnModel,
        pruneModifiedRecords: true,
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
            handler : function (){
                window.location = Axis.getUrl('shipping-table/rate/export');
            }
        }, {
            text: 'Import'.l(),
            icon: Axis.skinUrl + '/images/icons/brick_add.png',
            cls: 'x-btn-text-icon',
            handler : function(){
                importForm.getForm().clear();
                importWin.show();
            }
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
    
    var importForm = new Ext.FormPanel({
        url: Axis.getUrl('shipping-table/rate/import'),
        fileUpload: true,
        defaults: {
            anchor: '100%',
            allowBlank: false
        },
        border: false,
        bodyStyle: 'padding: 10px 5px 0',
        items: [new Ext.form.ComboBox({
            fieldLabel: 'Site'.l(),
            editable: false,
            typeAhead: true,
            triggerAction: 'all',
            store: storeCoreSite,
            displayField: 'name',
            valueField: 'id',
            hiddenName: 'site_id', 
            name: 'site_id',
//            mode: 'local',
            allowBlank: false
        }),{
            fieldLabel: 'File'.l(),
            name: 'data',
            xtype: 'fileuploadfield',
            allowBlank: false
        }]
    });
    
    var importWin = new Ext.Window({
        layout: 'fit',
        width: 300,
        height: 200,
        plain: false,
        title: 'Template',
        closeAction: 'hide',
        buttons: [{
            text: 'Ok'.l(),
            handler: function(button, event) {
                importForm.getForm().submit({
                    method: 'get',
                    success: function() {
                        store.reload();
                        button.findParentByType('window').hide();
                    },
                    failure: function(form, response){
                        var data = Ext.decode(response.response.responseText);
                        console.log(data);
                    }
                });
            }//Template.importT
        }, {
            text: 'Cancel'.l(),
            handler: function(button, event){
                button.findParentByType('window').hide();
            }
        }],
        items: importForm
    });
    
});