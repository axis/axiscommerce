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

var ValuesetGrid = {

    el: null,

    add: function() {
        var grid    = ValuesetGrid.el,
            store   = grid.getStore();
        var record = new store.recordType({
            name: ''
        });
        record.markDirty();
        grid.stopEditing();
        store.insert(0, record);
        grid.startEditing(0, 2);
    },

    remove: function(records) {
        var selectedItems = records || ValuesetGrid.el.getSelectionModel().getSelections();
        if (!selectedItems.length || !confirm('Are you sure?'.l())) {
            return;
        }

        var data = {};
        for (var i = 0; i < selectedItems.length; i++) {
            data[i] = selectedItems[i].id;
        }
        Ext.Ajax.request({
            url: Axis.getUrl('account/value-set/remove'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                ValuesetGrid.reload();
            }
        });
    },

    reload: function() {
        ValuesetGrid.el.getStore().reload();
    },

    save: function() {
        var modified = ValuesetGrid.el.getStore().getModifiedRecords();
        if (!modified.length) {
            return;
        }

        var data = {};
        for (var i = 0; i < modified.length; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }

        Ext.Ajax.request({
            url: Axis.getUrl('account/value-set/batch-save'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                ValuesetGrid.el.getStore().commitChanges();
                ValuesetGrid.reload();
            }
        });
    }

};

Ext.onReady(function(){

    var ds = new Ext.data.JsonStore({
        storeId     : 'store-valueset',
        url         : Axis.getUrl('account/value-set/list'),
        idProperty  : 'id',
        root        : 'data',
        fields      : ['id', 'name'],
        autoLoad    : true
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header      : 'Id'.l(),
            dataIndex   : 'id',
            width       : 60
        }, {
            header      : 'Name'.l(),
            id          : 'name',
            dataIndex   : 'name',
            editor      : new Ext.form.TextField({
                allowBlank  : false,
                maxLength   : 45
            })
        }]
    });

    ValuesetGrid.el = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'name',
        collapseMode    : 'mini',
        region  : 'west',
        width   : 250,
        ds      : ds,
        cm      : cm,
        tbar    : [{
            text    : 'Add'.l(),
            icon    : Axis.skinUrl + '/images/icons/add.png',
            handler : ValuesetGrid.add
        }, {
            text    : 'Save'.l(),
            icon    : Axis.skinUrl + '/images/icons/save_multiple.png',
            handler : ValuesetGrid.save
        }, {
            text    : 'Delete'.l(),
            icon    : Axis.skinUrl + '/images/icons/delete.png',
            handler : function() {
                ValuesetGrid.remove();
            }
        }, '->', {
            icon    : Axis.skinUrl + '/images/icons/refresh.png',
            handler : ValuesetGrid.reload
        }]
    });

    ValuesetGrid.el.on('rowclick', function(grid, index, e) {
        var valuesGridStore = ValuesetValueGrid.el.getStore(),
            baseParams      = valuesGridStore.baseParams,
            valuesetId      = grid.getStore().getAt(index).get('id'),
            gridView        = grid.getView();

        if (baseParams['valuesetId'] == valuesetId) {
            return;
        }

        $('.x-grid3-row-active', ValuesetGrid.el.getEl().dom).removeClass('x-grid3-row-active');
        if (!isNaN(valuesetId)) {
            baseParams['valuesetId'] = valuesetId;
            $(gridView.getRow(index)).addClass('x-grid3-row-active');
        }
        valuesGridStore.load();
    });
});
