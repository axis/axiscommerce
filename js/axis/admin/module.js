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

var ModuleGrid = {

    el: null,

    save: function() {
        var modified = ModuleGrid.el.store.getModifiedRecords();
        if (!modified.length) {
            return false;
        }

        var data = {};
        for (var i = 0, n = modified.length; i < n; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }

        var jsonData = Ext.encode(data);
        Ext.Ajax.request({
            url: Axis.getUrl('core/module/batch-save'),
            params: {
                data: jsonData
            },
            callback: function() {
                ModuleGrid.el.store.commitChanges();
                ModuleGrid.reload();
            }
        });
    },

    reload: function() {
        ModuleGrid.el.store.reload();
    }
};

Ext.onReady(function() {
    Ext.QuickTips.init();

    var record = new Ext.data.Record.create([
        {name: 'code'},
        {name: 'name'},
        {name: 'package'},
        {name: 'is_active', type: 'int'},
        {name: 'version'},
        {name: 'hide_install', type: 'int'},
        {name: 'hide_upgrade', type: 'bool'},
        {name: 'hide_uninstall', type: 'bool'},
        {name: 'upgrade_tooltip'},
        {name: 'install_tooltip'}
    ]);

    var ds = new Ext.data.Store({
        autoLoad: true,
        baseParams: {
            limit: 25
        },
        url: Axis.getUrl('core/module/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            idProperty: 'code', // not installed modules doesn't have id
            totalProperty: 'count'
        }, record),
        remoteSort: true,
        sortInfo: {
            field: 'name',
            direction: 'ASC'
        }
    });

    var status = new Axis.grid.CheckColumn({
        header: "Status".l(),
        dataIndex: 'is_active',
        width: 90,
        filter: {
            editable: false,
            resetValue: 'reset',
            store: new Ext.data.ArrayStore({
                data: [[0, 'Disabled'.l()], [1, 'Enabled'.l()]],
                fields: ['id', 'name']
            })
        }
    });

    var actions = new Ext.ux.grid.RowActions({
        actions: [{
            iconCls: 'icon icon-box-green',
            tooltip: 'Install'.l(),
            qtipIndex: 'install_tooltip',
            hideIndex: 'hide_install',
            callback: function(grid, record, action, row, col) {
                Ext.Ajax.request({
                    url: Axis.getUrl('core/module/install/code/' + record.get('code')),
                    callback: ModuleGrid.reload
                });
            }
        }, {
            iconCls: 'icon icon-light',
            tooltip: 'Upgrade'.l(),
            qtipIndex: 'upgrade_tooltip',
            hideIndex: 'hide_upgrade',
            callback: function(grid, record, action, row, col) {
                Ext.Ajax.request({
                    url: Axis.getUrl('core/module/upgrade/code/' + record.get('code')),
                    callback: ModuleGrid.reload
                });
            }
        }, {
            iconCls: 'icon icon-bin',
            tooltip: 'Uninstall'.l(),
            hideIndex: 'hide_uninstall',
            callback: function(grid, record, action, row, col) {
                Ext.Ajax.request({
                    url: Axis.getUrl('core/module/uninstall/code/' + record.get('code')),
                    callback: ModuleGrid.reload
                });
            }
        }]
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: 'Package'.l(),
            id: 'package',
            dataIndex: 'package',
            width: 200
        }, {
            header: 'Name'.l(),
            id: 'name',
            dataIndex: 'name'
        }, {
            header: 'Version'.l(),
            dataIndex: 'version',
            width: 90,
            filter: {
                rangeField: true
            }
        },
        status,
        actions]
    });

    ModuleGrid.el = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'name',
        cm: cm,
        ds: ds,
        id: 'grid-module',
        plugins: [
            status,
            actions,
            new Axis.grid.Filter()
        ],
        bbar: new Axis.PagingToolbar({
            store: ds
        }),
        tbar: [{
            handler: ModuleGrid.save,
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            text: 'Save'.l()
        }, {
            text: 'Install All'.l(),
            icon: Axis.skinUrl + '/images/icons/package_green.png',
            handler: function() {
                Ext.Ajax.request({
                    url: Axis.getUrl('core/module/install'),
                    callback: ModuleGrid.reload
                });
            }
        }, {
            text: 'Upgrade All'.l(),
            icon: Axis.skinUrl + '/images/icons/lightbulb.png',
            handler: function() {
                Ext.Ajax.request({
                    url: Axis.getUrl('core/module/upgrade'),
                    callback: ModuleGrid.reload
                });
            }
        }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: ModuleGrid.reload
        }]
    });

    new Axis.Panel({
        items: [
            ModuleGrid.el
        ]
    });
});
