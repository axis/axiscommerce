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

var FieldGrid = {

    el: null,

    remove: function(records) {
        var selectedItems = records || FieldGrid.el.getSelectionModel().getSelections();
        if (!selectedItems.length || !confirm('Are you sure?'.l())) {
            return;
        }

        var data = {};
        for (var i = 0; i < selectedItems.length; i++) {
            data[i] = selectedItems[i].id;
        }
        Ext.Ajax.request({
            url: Axis.getUrl('account/field/remove'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                FieldGrid.el.getStore().reload();
            }
        });
    },

    reload: function() {
        FieldGrid.el.getStore().reload();
    },

    save: function() {
        var modified = FieldGrid.el.getStore().getModifiedRecords();
        if (!modified.length) {
            return;
        }

        var data = {};
        for (var i = 0; i < modified.length; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }

        Ext.Ajax.request({
            url: Axis.getUrl('account/field/batch-save'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                FieldGrid.el.getStore().commitChanges();
                FieldGrid.el.getStore().reload();
            }
        });
    }
};

Ext.onReady(function() {

    Ext.QuickTips.init();

    var ds = new Ext.data.Store({
        autoLoad    : true,
        url         : Axis.getUrl('account/field/list'),
        baseParams  : {
            limit: 25
        },
        reader: new Ext.data.JsonReader({
            totalProperty   : 'count',
            root            : 'data',
            idProperty      : 'id'
        }, [
            {name: 'id', type: 'int'},
            {name: 'name'},
            {name: 'customer_field_group_id', type: 'int'},
            {name: 'field_type'},
            {name: 'requiredColumn', type: 'int'},
            {name: 'is_active', type: 'int'},
            {name: 'sort_order', type: 'int'},
            {name: 'validator'},
            {name: 'axis_validator'}
        ]),
        sortInfo: {
            field       : 'id',
            direction   : 'DESC'
        },
        remoteSort: true
    });

    var statusColumnStore = new Ext.data.ArrayStore({
        data: [['reset', ''], [0, 'Disabled'.l()], [1, 'Enabled'.l()]],
        fields: ['id', 'name']
    });
    var statusColumn = new Axis.grid.CheckColumn({
        header      : 'Status'.l(),
        width       : 80,
        dataIndex   : 'is_active',
        filter      : {
            prependResetValue   : false,
            editable            : false,
            resetValue          : 'reset',
            store               : statusColumnStore
        }
    });
    var requiredColumn = new Axis.grid.CheckColumn({
        header      : 'Required'.l(),
        width       : 80,
        dataIndex   : 'required',
        filter      : {
            prependResetValue   : false,
            editable            : false,
            resetValue          : 'reset',
            store               : statusColumnStore
        }
    });

    var validatorCombo = new Ext.form.ComboBox({
        id              : 'combobox-validator',
        typeAhead       : true,
        triggerAction   : 'all',
        lazyRender      : true,
        mode            : 'local',
        valueField      : 'id',
        displayField    : 'name',
        forceSelection  : false,
        store: new Ext.data.JsonStore({
            local       : true,
            idProperty  : 'id',
            fields      : ['id', 'name'],
            data        : validators // see index.phtml
        })
    });

    var typeCombo = new Ext.form.ComboBox({
        id              : 'combobox-fieldtype',
        typeAhead       : true,
        triggerAction   : 'all',
        lazyRender      : true,
        mode            : 'local',
        valueField      : 'id',
        displayField    : 'name',
        forceSelection  : false,
        store: new Ext.data.JsonStore({
            local       : true,
            idProperty  : 'id',
            fields      : ['id', 'name'],
            data        : fieldTypes // see index.phtml
        })
    });

    var actions = new Ext.ux.grid.RowActions({
        header:'Actions'.l(),
        actions:[{
            iconCls: 'icon-folder-edit',
            tooltip: 'Edit'.l()
        }, {
            iconCls: 'icon-folder-delete',
            tooltip: 'Delete'.l()
        }],
        callbacks: {
            'icon-folder-edit': function(grid, record, action, row, col) {
                Field.load(record.get('id'));
            },
            'icon-folder-delete': function(grid, record, action, row, col) {
                FieldGrid.remove([record]);
            }
        }
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header      : 'Id'.l(),
            dataIndex   : 'id',
            width       : 80
        }, {
            header      : 'Name'.l(),
            id          : 'name',
            width       : 160,
            dataIndex   : 'name',
            editor      : new Ext.form.TextField({
                allowBlank  : false,
                maxLength   : 45
            })
        }, {
            header      : "Field Type".l(),
            dataIndex   : 'field_type',
            width       : 80,
            editor      : typeCombo,
            filter      : {
                editable: false,
                store   : new Ext.data.JsonStore({
                    local       : true,
                    idProperty  : 'id',
                    fields      : ['id', 'name'],
                    data        : fieldTypes // see index.phtml
                })
            }
        },
        statusColumn,
        requiredColumn,
        {
            header  : 'Validator'.l(),
            dataIndex: 'validator',
            width   : 120,
            editor  : validatorCombo,
            filter  : {
                editable: false,
                store: new Ext.data.JsonStore({
                    local       : true,
                    idProperty  : 'id',
                    fields      : ['id', 'name'],
                    data        : validators // see index.phtml
                })
            },
            renderer: function(value) {
                if (value == '' || !value) {
                    return validatorCombo.getStore().data.items[0].data.name;
                } else {
                    return validatorCombo.getStore().getById(value).get('name');
                }
            }
        }, {
            align   : 'right',
            header  : 'Sort Order'.l(),
            dataIndex: 'sort_order',
            width   : 80,
            editor  : new Ext.form.NumberField({
               allowBlank: false
            })
        }, actions]
    });

    FieldGrid.el = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'name',
        ds: ds,
        cm: cm,
        plugins: [
            actions,
            statusColumn,
            requiredColumn,
            new Axis.grid.Filter()
        ],
        tbar: [{
            text    : 'Add'.l(),
            icon    : Axis.skinUrl + '/images/icons/add.png',
            handler : Field.add
        }, {
            text    : 'Edit'.l(),
            icon    : Axis.skinUrl + '/images/icons/page_edit.png',
            handler : function() {
                var selected = FieldGrid.el.getSelectionModel().getSelected();
                if (!selected) {
                    return;
                }
                Field.load(selected.get('id'));
            }
        }, {
            text    : 'Save'.l(),
            icon    : Axis.skinUrl + '/images/icons/save_multiple.png',
            handler : FieldGrid.save
        }, {
            text    : 'Delete'.l(),
            icon    : Axis.skinUrl + '/images/icons/delete.png',
            handler : function() {
                FieldGrid.remove();
            }
        }, '->', {
            text    : 'Reload'.l(),
            icon    : Axis.skinUrl + '/images/icons/refresh.png',
            handler : FieldGrid.reload
        }],
        bbar: new Axis.PagingToolbar({
            store: ds
        })
    });

    FieldGrid.el.on('rowdblclick', function(grid, index, e) {
        Field.load(grid.getStore().getAt(index).get('id'));
    })
});
