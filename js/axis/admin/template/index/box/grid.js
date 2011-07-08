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

Box.Grid = {

    el: null,

    record: null,

    templateId: null,

    add: function() {
        if (!Box.Grid.templateId) {
            return alert('Select template on the left panel');
        }

        Box.Grid.el.stopEditing();
        var record = new Box.Grid.record({
            'block'     : 'content',
            'box_status': 1,
            'class'     : '',
            'sort_order': 70,
            'page_ids'  : '1',
            'template_id': Box.Grid.templateId
        });
        record.markDirty();
        Box.Grid.el.store.insert(0, record);
        Box.Grid.el.startEditing(0, 2);
    },

    edit: function(record) {
        var box = record || Box.Grid.el.selModel.getSelected();
        Box.load(box.get('id'));
    },

    load: function(templateId) {
        Box.Grid.templateId = templateId;
        var ds = Box.Grid.el.store;
        ds.baseParams['filter[template][field]'] = 'template_id';
        ds.baseParams['filter[template][value]'] = templateId;
        ds.reload();
    },

    reload: function() {
        Box.Grid.el.store.reload();
    },

    remove: function() {
        var selectedItems = Box.Grid.el.getSelectionModel().selections.items;

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
            url: Axis.getUrl('template_box/delete'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                Box.Grid.el.store.reload();
            }
        });
    },

    save: function() {
        var modified = Box.Grid.el.store.getModifiedRecords();
        if (!modified.length) {
            return;
        }

        var data = {};
        for (var i = 0; i < modified.length; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }

        Ext.Ajax.request({
            url: Axis.getUrl('template_box/batch-save'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                Box.Grid.el.store.commitChanges();
                Box.Grid.el.store.reload();
            }
        });
    }
};

Ext.onReady(function() {

    Box.Grid.record = Ext.data.Record.create([
        {name: 'id', type: 'int'},
        {name: 'block'},
        {name: 'box_status', type: 'int'},
        {name: 'class'},
        {name: 'sort_order', type: 'int'},
        {name: 'page_ids', type: 'string'},
        {name: 'template_id', type: 'int'}
    ]);

    var ds = new Ext.data.Store({
        baseParams: {
            limit: 25
        },
        url: Axis.getUrl('template_box/list'),
        reader: new Ext.data.JsonReader({
            totalProperty: 'count',
            root: 'data',
            id: 'id'
        }, Box.Grid.record),
        remoteSort: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        }
    });

    var dsPages = new Ext.data.Store({
        data: Axis.pages,
        reader: new Ext.data.JsonReader({
            idProperty: 'id',
            fields: [
                {name: 'id', type: 'int'},
                {name: 'name'}
            ]
        })
    });

    var status = new Axis.grid.CheckColumn({
        header: 'Status'.l(),
        width: 100,
        dataIndex: 'box_status',
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
        actions:[{
            iconCls:'icon-edit',
            tooltip:'Edit'.l()
        }],
        callbacks: {
            'icon-edit': function(grid, record, action, row, col) {
                if (isNaN(record.id)) {
                    return;
                }
                Box.load(record.id);
            }
        }
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: 'Id'.l(),
            dataIndex: 'id',
            width: 90
        }, {
            header: "Box".l(),
            id: 'block',
            dataIndex: 'class',
            mode: 'local',
            width: 220,
            editor: new Ext.form.ComboBox({
                editable: false,
                typeAhead: true,
                triggerAction: 'all',
                lazyRender: true,
                store: Axis.boxClasses,
                mode: 'local'
            })
        }, {
            header: 'Output Container'.l(),
            dataIndex: 'block',
            editor: new Ext.form.TextField({
                allowBlank: false
            }),
            width: 120
        }, {
            header: "Show on".l(),
            dataIndex: 'page_ids',
            width: 200,
            editor: new Ext.ux.Andrie.Select({
                multiSelect: true,
                store: dsPages,
                valueField: 'id',
                displayField: 'name',
                triggerAction: 'all',
                mode: 'local'
            }),
            renderer: function(value, meta) {
                if ('' === value)  {
                    return 'None'.l();
                }
                var ret = new Array();
                value = value.split(',');
                for (var i = 0, n = value.length; i < n; i++) {
                    if (value[i] != '') {
                        ret.push(dsPages.getById(value[i]).data.name);
                    }
                }
                ret = ret.join(', ');
                meta.attr = 'ext:qtip="Used on pages : ' + ret + '"';
                return ret;
            },
            sortName: 'page_id',
            table: 'ctbp',
            filter: {
                name: 'page_id',
                store: new Ext.data.Store({
                    data: Axis.pages,
                    reader: new Ext.data.JsonReader({
                        idProperty: 'id',
                        fields: [
                            {name: 'id', type: 'int'},
                            {name: 'name'}
                        ]
                    })
                })
            }
        }, {
            align: 'right',
            header: 'Sort Order'.l(),
            width: 100,
            dataIndex: 'sort_order',
            editor: new Ext.form.NumberField({
               allowBlank: false,
               maxValue: 127,
               minValue: -128
            })
        },
        status,
        actions]
    });

    Box.Grid.el = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'block',
        title: 'Boxes'.l(),
        ds: ds,
        cm: cm,
        plugins: [
            status,
            actions,
            new Axis.grid.Filter()
        ],
        bbar: new Axis.PagingToolbar({
            store: ds
        }),
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler: Box.Grid.add
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            cls: 'x-btn-text-icon',
            handler: Box.Grid.save
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: Box.Grid.remove
        }, '->', {
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: Box.Grid.reload
        }]
    });

    Box.Grid.el.on('rowdblclick', function(grid, rowIndex, e) {
        Box.load(grid.getStore().getAt(rowIndex).get('id'));
    })
});
