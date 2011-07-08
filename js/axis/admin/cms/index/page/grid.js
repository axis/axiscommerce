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

var PageGrid = {

    el: null,

    remove: function() {
        var selectedItems = PageGrid.el.getSelectionModel().getSelections();
        if (!selectedItems.length || !confirm('Are you sure?')) {
            return;
        }

        var data = {};
        for (var i = 0; i < selectedItems.length; i++) {
            data[i] = selectedItems[i].id;
        }
        Ext.Ajax.request({
            url: Axis.getUrl('cms_index/delete-page'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                PageGrid.el.getStore().reload();
            }
        });
    },

    reload: function() {
        PageGrid.el.getStore().reload();
    },

    save: function() {
        var modified = PageGrid.el.getStore().getModifiedRecords();
        if (!modified.length) {
            return;
        }

        var data = {};
        for (var i = 0; i < modified.length; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }

        Ext.Ajax.request({
            url: Axis.getUrl('cms_index/batch-page-save'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                PageGrid.el.getStore().commitChanges();
                PageGrid.el.getStore().reload();
            }
        });
    },

    batchProcess: function(field, value) {
        var selected = PageGrid.el.getSelectionModel().getSelections();
        for (var i = 0; i < selected.length; i++) {
            selected[i].set(field, value);
        }
    }
};

Ext.onReady(function(){

    Ext.QuickTips.init();

    var ds = new Ext.data.GroupingStore({
        autoLoad: true,
        baseParams: {
            limit: 25
        },
        url: Axis.getUrl('cms_index/get-pages'),
        reader: new Ext.data.JsonReader({
            totalProperty: 'count',
            root: 'data',
            id: 'id'
        }, [
            {name: 'id', type: 'int'},
            {name: 'category_name'},
            {name: 'name'},
            {name: 'is_active', type: 'int'},
            {name: 'comment', type: 'int'},
            {name: 'layout'},
            {name: 'show_in_box', type: 'int'}
        ]),
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        },
        remoteSort: true
    });

    var batchMenu = new Ext.menu.Menu({
        items: [{
            text: 'Change Layout to'.l(),
            icon: Axis.skinUrl + '/images/icons/layout.png',
            menu: layoutMenu
        }, {
            text: 'Status'.l(),
            icon: Axis.skinUrl + '/images/icons/status.png',
            menu: [{
                text: 'Disabled'.l(),
                icon: Axis.skinUrl + '/images/icons/disabled.png',
                handler: function() {
                    PageGrid.batchProcess('is_active', 0);
                }
            }, {
                text: 'Enabled'.l(),
                icon: Axis.skinUrl + '/images/icons/enabled.png',
                handler: function() {
                    PageGrid.batchProcess('is_active', 1);
                }
            }]
        }, {
            text: 'Comments'.l(),
            icon: Axis.skinUrl + '/images/icons/comments.png',
            menu: [{
                text: 'Disabled'.l(),
                icon: Axis.skinUrl + '/images/icons/disabled.png',
                handler: function() {
                    PageGrid.batchProcess('comment', 0);
                }
            }, {
                text: 'Enabled'.l(),
                icon: Axis.skinUrl + '/images/icons/enabled.png',
                handler: function() {
                    PageGrid.batchProcess('comment', 1);
                }
            }]
        }]
    });

    var statusStore = new Ext.data.ArrayStore({
        data: [['reset', ''], [0, 'Disabled'.l()], [1, 'Enabled'.l()]],
        fields: ['id', 'name']
    })

    var status = new Axis.grid.CheckColumn({
        header: 'Status'.l(),
        width: 80,
        dataIndex: 'is_active',
        filter: {
            prependResetValue: false,
            editable: false,
            resetValue: 'reset',
            store: statusStore
        }
    });

    var comment = new Axis.grid.CheckColumn({
        header: 'Comments'.l(),
        width: 80,
        dataIndex: 'comment',
        filter: {
            prependResetValue: false,
            editable: false,
            resetValue: 'reset',
            store: statusStore
        }
    });

    var showInBox = new Axis.grid.CheckColumn({
        header: 'Show in box'.l(),
        width: 120,
        dataIndex: 'show_in_box',
        filter: {
            prependResetValue: false,
            editable: false,
            resetValue: 'reset',
            store: statusStore
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
            header: 'Category'.l(),
            width: 120,
            dataIndex: 'category_name',
            table: ''
        }, {
            header: 'Name'.l(),
            id: 'name',
            width: 160,
            dataIndex: 'name',
            editor: new Ext.form.TextField({
                allowBlank: false,
                maxLength: 45
            })
        }, {
            header: 'Layout'.l(),
            width: 210,
            dataIndex: 'layout',
            editor: new Ext.form.ComboBox({
                editable: false,
                triggerAction: 'all',
                displayField: 'name',
                typeAhead: true,
                mode: 'local',
                valueField: 'id',
                store: Page.layoutStore
            }),
            renderer: function(v) {
                var record = Page.layoutStore.getById(v);
                if (record) {
                    return record.get('name');
                }
                return v;
            }
        },
            status,
            comment,
            showInBox
        ]
    });

    PageGrid.el = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'name',
        ds: ds,
        cm: cm,
        plugins: [
            status,
            comment,
            showInBox,
            new Axis.grid.Filter()
        ],
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler: Page.add
        }, {
            text: 'Edit'.l(),
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            handler: function() {
                var selected = PageGrid.el.getSelectionModel().getSelected();
                if (!selected) {
                    return;
                }
                Page.load(selected.get('id'));
            }
        }, {
            text: 'Batch'.l(),
            icon: Axis.skinUrl + '/images/icons/menu_action.png',
            menu: batchMenu
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler: PageGrid.save
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: function() {
                PageGrid.remove();
            }
        }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: PageGrid.reload
        }],
        bbar: new Axis.PagingToolbar({
            store: ds
        })
    });

    PageGrid.el.on('rowdblclick', function(grid, index, e) {
        Page.load(grid.getStore().getAt(index).get('id'));
    })
});
