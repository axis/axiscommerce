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

Ext.namespace('Axis', 'Axis.Template', 'Axis.Template.Layout');
Ext.onReady(function() {
    var Layout = {
        templateId: 1,

        record: Ext.data.Record.create([
            {name: 'id', type: 'int'},
            {name: 'page_id', type: 'int'},
            {name: 'parent_page_id', type: 'int'},
            {name: 'layout'},
            {name: 'priority', type: 'int'}
        ]),

        loadGrid: function(templateId) {
            Layout.templateId = templateId;
            ds.baseParams['filter[template][field]'] = 'template_id';
            ds.baseParams['filter[template][value]'] = templateId;
            ds.reload();
        },

        create: function() {
            grid.stopEditing();
            var record = new Layout.record({
                page_id: '',
                layout: '',
                priority: '100'
            });
            ds.insert(0, record);
            grid.startEditing(0, 2);
        },

        save: function() {
            var data = {};
            var modified = ds.getModifiedRecords();
            if (!modified.length)
                return;

            for (var i = 0; i < modified.length; i++) {
                data[modified[i]['id']] = modified[i]['data'];
            }

            Ext.Ajax.request({
                url: Axis.getUrl('template_page/save'),
                params: {
                    data: Ext.encode(data),
                    tId: Layout.templateId
                },
                callback: function() {
                    ds.commitChanges();
                    ds.reload();
                }
            });
        },

        remove: function() {
            var selectedItems = grid.getSelectionModel().selections.items;
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
                url: Axis.getUrl('template_page/delete'),
                params: {
                    data: Ext.encode(data),
                    tId: Layout.templateId
                },
                callback: function() {
                    ds.commitChanges();
                    ds.reload();
                }
            });
        }
    }

    Axis.Template.Layout = Layout;

    Ext.QuickTips.init();

    var dsPages = new Ext.data.JsonStore({
        id: 'id',
        fields: ['id', 'name'],
        data: Axis.pages
    });

    var dsParentPages = new Ext.data.JsonStore({
        id: 'id',
        fields: ['id', 'name'],
        data: Axis.pages
    });

    dsParentPages.insert(
        0, new dsParentPages.recordType({id: 0, name: 'None'.l()})
    );

    var ds = new Ext.data.Store({
        baseParams: {
            limit: 25
        },
        url: Axis.getUrl('template_page/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, Layout.record),
        pruneModifiedRecords: true,
        remoteSort: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        }
    });

    var dsLayout = new Ext.data.Store({
        url:  Axis.getUrl('template_layout/list'),
        reader: new Ext.data.JsonReader({
            root: 'data'
        }, ['id', 'name']),
        autoLoad: true,
        listeners: {
            load: function(store, records, options) {
                store.insert(
                    0, new store.recordType({id: '', name: 'None'.l()})
                );
            }
        }
    });

    var comboLayout = new Ext.form.ComboBox({
        id: 'comboLayout',
        triggerAction: 'all',
        displayField: 'name',
        typeAhead: true,
        mode: 'local',
        valueField: 'id',
        editable: false,
        store: dsLayout
    });

    var rendererColumnPage = function(value) {
        if (value == '0' || value == '') {
            return 'None';
        } else {
            for (var i in Axis.pages) {
               if (Axis.pages[i]['id'] == value) {
                   return Axis.pages[i]['name'];
               }
            }
            return value;
        }
    };
    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: 'Id'.l(),
            dataIndex: 'id',
            width: 90
        }, {
            header: "Layout".l(),
            dataIndex: 'layout',
            width: 200,
            editor: comboLayout,
            renderer: function (value) {
                if (value == '0' || value == '') {
                    return 'None';
                }
                return value;
            }
        }, {
            header: "Page".l(),
            dataIndex: 'page_id',
            id: 'page',
            editor: new Ext.form.ComboBox({
                typeAhead: true,
                triggerAction: 'all',
                lazyRender: true,
                store: dsPages,
                editable: false,
                displayField: 'name',
                valueField: 'id',
                mode: 'local'
            }),
            renderer: rendererColumnPage,
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
        },{
            header: "Parent Page".l(),
            dataIndex: 'parent_page_id',
            id: 'parent_page',
            width: 200,
            editor: new Ext.form.ComboBox({
                typeAhead: true,
                triggerAction: 'all',
                lazyRender: true,
                store: dsParentPages,
                editable: false,
                displayField: 'name',
                valueField: 'id',
                mode: 'local'
            }),
            renderer: rendererColumnPage,
            filter: {
                name: 'parent_page_id',
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
            header: "Priority".l(),
            dataIndex: 'priority',
            width: 90,
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }]
    });

    var grid = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'page',
        title: 'Layouts'.l(),
        ds: ds,
        cm: cm,
        bbar: new Axis.PagingToolbar({
            store: ds
        }),
        plugins: [new Axis.grid.Filter()],
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler: Layout.create
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler: Layout.save
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: Layout.remove
        }, '->', {
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: function() {
                grid.getStore().reload();
            }
        }]
    });

    Layout.grid = grid;
}, this);