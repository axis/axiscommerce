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

var ValuesetValueGrid = {

    el: null,

    add: function() {
        var store = ValuesetValueGrid.el.getStore();
        if (!store.baseParams['valuesetId']) {
            return alert('Select the valueset on the left panel'.l());
        }

        var grid = ValuesetValueGrid.el,
            data = {
                is_active   : 1,
                sort_order  : 20,
                customer_valueset_id: store.baseParams['valuesetId']
            };

        for (var langId in Axis.languages) {
            data['label' + langId] = '';
        }
        var record = new store.recordType(data);
        record.markDirty();
        grid.stopEditing();
        store.insert(0, record);
        grid.startEditing(0, 2);
    },

    remove: function(records) {
        var selectedItems = records || ValuesetValueGrid.el.getSelectionModel().getSelections();
        if (!selectedItems.length || !confirm('Are you sure?'.l())) {
            return;
        }

        var data = {};
        for (var i = 0; i < selectedItems.length; i++) {
            data[i] = selectedItems[i].id;
        }
        Ext.Ajax.request({
            url: Axis.getUrl('account/value-set-value/remove'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                ValuesetValueGrid.reload();
            }
        });
    },

    reload: function() {
        ValuesetValueGrid.el.getStore().reload();
    },

    save: function() {
        var modified = ValuesetValueGrid.el.getStore().getModifiedRecords();
        if (!modified.length) {
            return;
        }

        var data = {};
        for (var i = 0; i < modified.length; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }

        Ext.Ajax.request({
            url: Axis.getUrl('account/value-set-value/batch-save'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                ValuesetValueGrid.el.getStore().commitChanges();
                ValuesetValueGrid.reload();
            }
        });
    }
};

Ext.onReady(function(){

    var fields = [
        {name: 'id',            type: 'int'},
        {name: 'sort_order',    type: 'int'},
        {name: 'is_active',     type: 'int'},
        {name: 'customer_valueset_id', type: 'int'}
    ];
    for (var id in Axis.languages) {
        fields.push({name: 'label' + id});
    }

    var ds = new Ext.data.JsonStore({
        url         : Axis.getUrl('account/value-set-value/list'),
        idProperty  : 'id',
        root        : 'data',
        fields      : fields,
        listeners   : {
            beforeload: function(store, options) {
                if (!store.baseParams['valuesetId']) {
                    return false;
                }
            }
        }
    });

    var columns = [{
        header      : 'Id'.l(),
        dataIndex   : 'id',
        width       : 60
    }];
    for (var id in Axis.languages) {
        columns.push({
            header      : 'Title ({language})'.l('core', Axis.languages[id]),
            dataIndex   : 'label' + id,
            width       : 110,
            editor      : new Ext.form.TextField({
                allowBlank  : false,
                maxLength   : 128
            })
        });
    }
    var status = new Axis.grid.CheckColumn({
        header      : 'Status'.l(),
        width       : 60,
        dataIndex   : 'is_active'
    });
    columns.push(status);
    columns.push({
        header      : 'Sort Order'.l(),
        dataIndex   : 'sort_order',
        width       : 80,
        editor      : new Ext.form.TextField({
            allowBlank: false
        })
    });
    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: columns
    });

    ValuesetValueGrid.el = new Axis.grid.EditorGridPanel({
        ds: ds,
        cm: cm,
        plugins: [
            status
        ],
        tbar: [{
            text    : 'Add'.l(),
            icon    : Axis.skinUrl + '/images/icons/add.png',
            handler : ValuesetValueGrid.add
        }, {
            text    : 'Save'.l(),
            icon    : Axis.skinUrl + '/images/icons/save_multiple.png',
            handler : ValuesetValueGrid.save
        }, {
            text    : 'Delete'.l(),
            icon    : Axis.skinUrl + '/images/icons/delete.png',
            handler : function() {
                ValuesetValueGrid.remove();
            }
        }, '->', {
            text    : 'Reload'.l(),
            icon    : Axis.skinUrl + '/images/icons/refresh.png',
            handler : ValuesetValueGrid.reload
        }]
    });
});
