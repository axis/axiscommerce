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

Ext.onReady(function(){
    var renderName = function (value) {
        if (value == null || value == '')
            return 'All';
        return value;
    };
    
    var GZone = {
        activeId: 0,
        record: Ext.data.Record.create([
            {name: 'id'},
            {name: 'name', type: 'string'},
            {name: 'description', type: 'string'},
            {name: 'priority', type: 'string'}
        ]),
        create: function (){
            gridGZone.stopEditing();
            var record = new GZone.record({
                name: '',
                description: '',
                priority: '',
                type: 'new'
            });
            dsGZone.insert(0, record);
            gridGZone.startEditing(0, 0);
        },
        
        getSelectedId: function() {
            var selModel = gridGZone.getSelectionModel();
            var selectedItems = gridGZone.getSelectionModel().selections.items;
            if (!selectedItems.length) {
                return false;
            }
            if (selectedItems[0]['data']['id'])
                return selectedItems[0].id;
            return false;
        },
        
        save: function() {
            var data = {};
            var modified = dsGZone.getModifiedRecords();
            if (!modified.length)
                return;
            
            for (var i = 0; i < modified.length; i++) {
                data[modified[i]['id']] = modified[i]['data'];
            }
            
            Ext.Ajax.request({
                url: Axis.getUrl('location_zone-definition/save'),
                params: {
                    data: Ext.encode(data)
                },
                callback: function() {
                    dsGZone.commitChanges();
                    dsGZone.reload();
                }
            });
        },
        
        editAssigns: function() {
            var id = GZone.getSelectedId();
            if (!id) {
                return;
            }
            GZone.activeId = id;
            dsAssign.proxy.conn.url =  Axis.getUrl('location_zone-definition/list-assigns/gzoneId/') + id;
            dsAssign.load();
        },
        
        remove: function() {
            if (!confirm('Delete definition(s)?'))
                return;
            var data = {};
            var selectedItems = gridGZone.getSelectionModel().selections.items;
            for (var i = 0; i < selectedItems.length; i++) {
                if (!selectedItems[i]['data']['id']) continue;
                data[i] = selectedItems[i]['data']['id'];
            }
                
            Ext.Ajax.request({
                url:  Axis.getUrl('location_zone-definition/delete'),
                params: {data: Ext.encode(data)},
                callback: function() {
                    dsGZone.reload();
                }
            });
        },
        grid: {}
    };
    
    var dsGZone = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            url:  Axis.getUrl('location_zone-definition/list')
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, GZone.record),
        
        sortInfo: {field: 'priority', direction: 'DESC'},
        remoteSort: true
        
    });
    var cmGZone = new Ext.grid.ColumnModel([{
        header: "Name".l(),
        dataIndex: 'name',
        id: 'name',
        editor: new Ext.form.TextField({
            allowBlank: false
        })
    }, {
        header: "Description".l(),
        dataIndex: 'description',
        editor: new Ext.form.TextField({
            allowBlank: false
        })
    }, {
        header: "Priority".l(),
        dataIndex: 'priority',
        width: 80,
        editor: new Ext.form.TextField({
            allowBlank: false
        })
    }]);
    cmGZone.defaultSortable = true;
    
    var gridGZone = new Axis.grid.EditorGridPanel({
        ds: dsGZone,
        cm: cmGZone,
        width: 400,
        viewConfig: {
            forceFit: true,
            emptyText: 'No records found'.l()
        },
        region: 'west',
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
            handler : GZone.create
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            cls: 'x-btn-text-icon',
            handler : GZone.save
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler : GZone.remove
        }, {
            text: 'Load Zones'.l(),
            icon: Axis.skinUrl + '/images/icons/details.png',
            cls: 'x-btn-text-icon',
            handler : GZone.editAssigns
        }],
        bbar: new Axis.PagingToolbar({
            store: dsGZone
        })
    });
    
    dsGZone.load({params:{start:0, limit:25}});
    
    var Assign = {
        activeId: 0,
        record: Ext.data.Record.create([
            {name: 'sort_order', type: 'string'}
        ]),
        create: function (){
            if (!GZone.activeId) {
                alert('Select some definition & press "Load Zones" to activate zones list');
                return false;
            }
            Assign.activeId = 0;
            Assign.window.show();
        },
        
        edit: function(grid, rowIndex, e) {
            var aId = dsAssign.data.items[rowIndex].id;
            Assign.activeId = aId;
            Ext.Ajax.request({
                url:  Axis.getUrl('location_zone-definition/get-assign/assignId/') + aId,
                method: 'post',
                params: {
                    gzoneId:  GZone.activeId,
                    assignId: Assign.activeId,
                    country: $('#country').attr('value'),
                    zone: $('#zone').attr('value')
                },
                callback: function(options, success, response) {
                    oResponse = Ext.decode(response.responseText);
                    if (!oResponse.country_id)
                        oResponse.country_id = 0;
                    $('#country > option[value=' + oResponse.country_id + ']').attr({selected: true});
                    updateZones();
                    
                    if (!oResponse.zone_id)
                        oResponse.zone_id = 0;
                    $('#zone > option[value=' + oResponse.zone_id + ']').attr({selected: true});
                    Assign.window.show();
                }
            });
        },
        
        save: function() {
            Assign.window.disable();
            Ext.Ajax.request({
                url:  Axis.getUrl('location_zone-definition/save-assign'),
                method: 'post',
                params: {
                    gzoneId:  GZone.activeId,
                    assignId: Assign.activeId,
                    country: $('#country').attr('value'),
                    zone: $('#zone').attr('value')
                },
                callback: function(response, options) {
                    Assign.window.hide();
                    Assign.window.enable();
                    dsAssign.reload();
                }
            });
        },
        
        window: {},
        grid: {}
    };
    
    Assign.window = new Ext.Window({
        contentEl: 'form-assign',
        layout: 'fit',
        width: 440,
        height: 125,
        closeAction: 'hide',
        plain: true,
        title: 'Zones',
        maskDisabled: true,
        buttons: [{
            text: 'Save'.l(),
            handler: Assign.save
        },{
            text: 'Cancel'.l(),
            handler: function(){
                Assign.window.hide();
            }
        }]
    });
    
    var dsAssign = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            url:  Axis.getUrl('location_zone-definition/list-assigns')
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, [
            {name: 'id'},
            {name: 'geozone_name'},
            {name: 'country_name'},
            {name: 'zone_name'}
        ]),
        
        sortInfo: {field: 'id', direction: 'ASC'},
        remoteSort: true
    });
    
    var cmAssign = new Ext.grid.ColumnModel([{
        header: 'Definition'.l(),
        dataIndex: 'geozone_name',
        width: 150
    }, {
        header: 'Country'.l(),
        dataIndex: 'country_name',
        renderer: renderName,
        width: 200
    }, {
        header: 'Zone'.l(),
        dataIndex: 'zone_name',
        renderer: renderName,
        width: 150
    }]);
    
    var gridAssign = new Axis.grid.EditorGridPanel({
        ds: dsAssign,
        cm: cmAssign,
        sm: new Ext.grid.RowSelectionModel({singleSelect:false}),
        viewConfig: {
            forceFit: true,
            emptyText: 'No records found'.l()
        },
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
            handler : Assign.create
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler : function(){
                if (!confirm('Delete assignments?'))
                        return;
                var data = {};
                var selectedItems = gridAssign.getSelectionModel().selections.items;
                for (var i = 0; i < selectedItems.length; i++) {
                    data[i] = selectedItems[i].id;
                }
                
                Ext.Ajax.request({
                    url:  Axis.getUrl('location_zone-definition/delete-assigns'),
                    params: {data: Ext.encode(data)},
                    callback: function() {
                        dsAssign.reload();
                    }
                });
            }
        }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-text-icon',
            handler : function() {
                if (!GZone.activeId) {
                    alert('Please load zone list before reload');
                    return false;
                }
                dsAssign.reload();
            }
        }]
    });
    
    new Axis.Panel({
        items: [
            gridGZone,
            gridAssign
        ]
    });
    
    gridAssign.on('rowdblclick', Assign.edit);
    
});