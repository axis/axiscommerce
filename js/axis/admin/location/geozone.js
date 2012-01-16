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

var GZone = {

    activeId: 0,

    grid: null,

    record: null,

    create: function (){
        GZone.grid.stopEditing();
        var record = new GZone.record({
            name        : '',
            description : '',
            priority    : 5,
            type        : 'new'
        });
        GZone.grid.getStore().insert(0, record);
        GZone.grid.startEditing(0, 2);
    },

    getSelectedId: function() {
        var selectedItems = GZone.grid.getSelectionModel().getSelections();
        if (!selectedItems.length) {
            return false;
        }
        if (selectedItems[0]['data']['id']) {
            return selectedItems[0].id;
        }
        return false;
    },

    save: function() {
        var modified = GZone.grid.getStore().getModifiedRecords();
        if (!modified.length) {
            return;
        }

        var data = {};
        for (var i = 0; i < modified.length; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }

        Ext.Ajax.request({
            url: Axis.getUrl('location/geozone/batch-save'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                GZone.grid.getStore().commitChanges();
                GZone.grid.getStore().reload();
            }
        });
    },

    editAssigns: function(id) {
        var id = id || GZone.getSelectedId();
        if (!id) {
            alert('Please select/save geozone first');
            return;
        }
        GZone.activeId = id;
        Assign.grid.getStore().baseParams['geozone_id'] = GZone.activeId;
        Assign.grid.getStore().load();
    },

    remove: function() {
        var selectedItems = GZone.grid.getSelectionModel().getSelections();
        if (!selectedItems.length || !confirm('Are you sure?'.l())) {
            return;
        }

        var data = {};
        for (var i = 0; i < selectedItems.length; i++) {
            if (!selectedItems[i]['data']['id']) {
                continue;
            }
            data[i] = selectedItems[i]['data']['id'];
        }

        Ext.Ajax.request({
            url:  Axis.getUrl('location/geozone/remove'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                GZone.activeId = 0;
                GZone.grid.getStore().reload();
            }
        });
    }
};

var Assign = {

    activeId: null,

    window: null,

    grid: null,

    create: function (){
        if (!GZone.activeId) {
            alert('Please select and load geozone on the left panel first');
            return false;
        }
        Assign.activeId = null;
        Assign.window.show();
    },

    edit: function(grid, rowIndex, e) {
        var aId = Assign.grid.getStore().data.items[rowIndex].id;
        Assign.activeId = aId;
        Ext.Ajax.request({
            url:  Axis.getUrl('location/geozone-zone/load'),
            method: 'post',
            params: {
                id: Assign.activeId
            },
            callback: function(options, success, response) {
                oResponse = Ext.decode(response.responseText);
                if (!oResponse.country_id) {
                    oResponse.country_id = 0;
                }
                $('#country > option[value=' + oResponse.country_id + ']').attr({selected: true});
                updateZones();

                if (!oResponse.zone_id) {
                    oResponse.zone_id = 0;
                }
                $('#zone > option[value=' + oResponse.zone_id + ']').attr({selected: true});
                Assign.window.show();
            }
        });
    },

    save: function() {
        Assign.window.disable();
        Ext.Ajax.request({
            url:  Axis.getUrl('location/geozone-zone/save'),
            method: 'post',
            params: {
                geozone_id: GZone.activeId,
                id:         Assign.activeId,
                country_id: $('#country').attr('value'),
                zone_id:    $('#zone').attr('value')
            },
            callback: function(response, options) {
                Assign.window.hide();
                Assign.window.enable();
                Assign.grid.getStore().reload();
            }
        });
    }
};

Ext.onReady(function() {

    GZone.record = Ext.data.Record.create([
        {name: 'id', type: 'int'},
        {name: 'name'},
        {name: 'description'},
        {name: 'priority', type: 'int'}
    ]);

    var renderName = function (value) {
        if (value == null || value == '') {
            return 'All';
        }
        return value;
    };

    var dsGZone = new Ext.data.Store({
        autoLoad: true,
        baseParams: {
            limit: 25
        },
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, GZone.record),
        remoteSort: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        },
        url:  Axis.getUrl('location/geozone/list')
    });

    var cmGZone = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: 'Id'.l(),
            dataIndex: 'id',
            width: 90
        }, {
            header: "Name".l(),
            dataIndex: 'name',
            id: 'name',
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
        }]
    });

    GZone.grid = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'name',
        ds: dsGZone,
        cm: cmGZone,
        collapseMode: 'mini',
        width: 450,
        region: 'west',
        plugins: [new Axis.grid.Filter()],
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler: GZone.create
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler: GZone.save
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: GZone.remove
        }, '->', {
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: function() {
                GZone.grid.getStore().reload();
            }
        }],
        bbar: new Axis.PagingToolbar({
            store: dsGZone
        }),
        listeners: {
            'rowclick': function(grid, index, e) {
                GZone.editAssigns(grid.getStore().getAt(index).get('id'));
            }
        }
    });

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
        }, {
            text: 'Cancel'.l(),
            handler: function(){
                Assign.window.hide();
            }
        }]
    });

    var dsAssign = new Ext.data.Store({
        baseParams: {
            limit: 25
        },
        url:  Axis.getUrl('location/geozone-zone/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, [
            {name: 'id', type: 'int'},
            {name: 'geozone_name'},
            {name: 'country_name'},
            {name: 'zone_name'}
        ]),
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        },
        remoteSort: true
    });

    var cmAssign = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: 'Id'.l(),
            dataIndex: 'id',
            width: 90
        }/*, {
            header: 'Geozone'.l(),
            dataIndex: 'geozone_name',
            width: 180
        }*/, {
            header: 'Country'.l(),
            dataIndex: 'country_name',
            id: 'country_name',
            renderer: renderName,
            table: 'lc',
            sortName: 'name',
            filter: {
                name: 'name'
            }
        }, {
            header: 'State / Province'.l(),
            dataIndex: 'zone_name',
            renderer: renderName,
            width: 220,
            table: 'lz',
            sortName: 'name',
            filter: {
                name: 'name'
            }
        }]
    });

    Assign.grid = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'country_name',
        ds: dsAssign,
        cm: cmAssign,
        plugins: [new Axis.grid.Filter()],
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
            handler : Assign.create
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler: function() {
                var selectedItems = Assign.grid.getSelectionModel().getSelections();
                if (!selectedItems.length || !confirm('Are you sure?'.l())) {
                    return;
                }

                var data = {};
                for (var i = 0; i < selectedItems.length; i++) {
                    data[i] = selectedItems[i].id;
                }

                Ext.Ajax.request({
                    url:  Axis.getUrl('location/geozone-zone/remove'),
                    params: {
                        data: Ext.encode(data)
                    },
                    callback: function() {
                        Assign.grid.getStore().reload();
                    }
                });
            }
        }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-text-icon',
            handler : function() {
                if (!GZone.activeId) {
                    alert('Please select and load geozone on the left panel first');
                    return false;
                }
                Assign.grid.getStore().reload();
            }
        }]
    });

    new Axis.Panel({
        items: [
            GZone.grid,
            Assign.grid
        ]
    });

    Assign.grid.on('rowdblclick', Assign.edit);
});