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

var CommentGrid = {

    el: null,

    add: function() {
        if (null == CommentTree.pageId) {
            return alert('Select page in left tree panel'.l());
        }
        CommentWindow.form.getForm().clear();
        CommentWindow.form.getForm().setValues({
            cms_page_id: CommentTree.pageId
        });
        CommentWindow.el.setTitle('New Comment'.l());
        CommentWindow.show();
    },

    save: function() {
        var modified = CommentGrid.el.getStore().getModifiedRecords();
        if (!modified.length) {
            return;
        }

        var data = {};
        for (var i = 0; i < modified.length; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }

        Ext.Ajax.request({
            url: Axis.getUrl('cms_comment/quick-save'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                CommentGrid.el.getStore().commitChanges();
                CommentGrid.el.getStore().reload();
            }
        });
    },

    edit: function(record) {
        var r = record || CommentGrid.el.getSelectionModel().getSelected();
        if (!r) {
            return;
        }
        CommentWindow.el.setTitle('Comment'.l() + ': ' + r.get('email'));
        CommentWindow.form.getForm().setValues({
            author      : r.get('author'),
            email       : r.get('email'),
            status      : r.get('status'),
            content     : r.get('content'),
            id          : r.get('id'),
            cms_page_id : r.get('cms_page_id')
        });
        CommentWindow.show();
    },

    remove: function() {
        var selectedItems = CommentGrid.el.getSelectionModel().selections.items;
        if (!selectedItems.length || !confirm('Are you sure?'.l())) {
            return;
        }

        var data = {};
        for (var i = 0; i < selectedItems.length; i++) {
            data[i] = selectedItems[i].id;
        }
        Ext.Ajax.request({
            url: Axis.getUrl('cms_comment/delete-comment'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                CommentGrid.el.getStore().reload();
            }
        });
    },

    reload: function() {
        CommentGrid.el.getStore().reload();
    },

    setStatus: function(status) {
        var selected = CommentGrid.el.getSelectionModel().getSelections();
        for (var i = 0, len = selected.length; i < len; i++) {
            selected[i].set('status', status);
        }
    }
};

Ext.onReady(function() {

    var ds = new Ext.data.GroupingStore({
        autoLoad: true,
        baseParams: {
            limit: 25
        },
        url: Axis.getUrl('cms_comment/get-comments'),
        reader: new Ext.data.JsonReader({
            totalProperty: 'count',
            root: 'data',
            id: 'id'
        }, [
            {name: 'id', type: 'int'},
            {name: 'cms_page_id', type: 'int'},
            {name: 'category_name'},
            {name: 'author'},
            {name: 'email'},
            {name: 'created_on', type: 'date', dateFormat: 'Y-m-d H:i:s'},
            {name: 'modified_on', type: 'date', dateFormat: 'Y-m-d H:i:s'},
            {name: 'page_name'},
            {name: 'status', type: 'int'},
            {name: 'content'}
        ]),
        groupField: 'page_name',
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        },
        remoteSort: true
    });

    var statusCombo = new Ext.form.ComboBox({
        editable: false,
        triggerAction: 'all',
        store: new Ext.data.ArrayStore({
            data: status,
            fields: ['id', 'name']
        })
    });

    var commentExpander = new Ext.grid.RowExpander({
        tpl : new Ext.Template(
            '<p style="padding-left: 45px; margin: 5px 0px;"><b>Author:</b> {author}</p>',
            '<p style="padding-left: 45px; margin: 5px 0px;"><b>Email:</b> {email}</p>',
            '<div style="padding: 0px 8px 5px 45px; line-height: 17px; text-align: justify"><b>Content:</b> {content}</div>'
        )
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [commentExpander, {
            header: 'Id'.l(),
            dataIndex: 'id',
            width: 90
        }, {
            header: 'Category'.l(),
            dataIndex: 'category_name',
            width: 150
        }, {
            header: 'Page Name'.l(),
            dataIndex: 'page_name',
            id: 'page-name',
            table: 'cp',
            sortName: 'name',
            filter: {
                name: 'name'
            },
            width: 150
        }, {
            header: 'Email'.l(),
            dataIndex: 'email',
            width: 200
        }, {
            header: 'Created'.l(),
            dataIndex: 'created_on',
            renderer: function(v) {
                return Ext.util.Format.date(v) + ' ' + Ext.util.Format.date(v, 'H:i:s');
            },
            width: 130
        }, {
            header: 'Modified'.l(),
            dataIndex: 'modified_on',
            renderer: function(v) {
                return Ext.util.Format.date(v) + ' ' + Ext.util.Format.date(v, 'H:i:s');
            },
            width: 130
        }, {
            header: 'Status'.l(),
            dataIndex: 'status',
            width: 100,
            editor: statusCombo,
            renderer: function(v) {
                var i = 0;
                while (status[i]) {
                    if (v == status[i][0]) {
                        return status[i][1];
                    }
                    i++;
                }
                return v;
            },
            filter: {
                editable: false,
                resetValue: 'reset',
                store: new Ext.data.ArrayStore({
                    data: status,
                    fields: ['id', 'name']
                })
            }
        }]
    });

    var commentActionMenu = new Ext.menu.Menu({
        id: commentActionMenu,
        items: menuStatus
    });

    CommentGrid.el = new Axis.grid.GridPanel({
        id: 'grid-comment',
        autoExpandColumn: 'page-name',
        cm: cm,
        ds: ds,
        listeners: {
            'rowdblclick': function(grid, index, e) {
                CommentGrid.edit(grid.getStore().getAt(index));
                return false; // prevent expanding of commentExpander
            }
        },
        plugins: [
            commentExpander,
            new Axis.grid.Filter()
        ],
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler: CommentGrid.add
        }, {
            text: 'Edit'.l(),
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            handler: CommentGrid.edit
        }, {
            text: 'Change Status to'.l(),
            icon: Axis.skinUrl + '/images/icons/menu_action.png',
            menu: commentActionMenu
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler: CommentGrid.save
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
             handler: CommentGrid.remove
        }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: CommentGrid.reload
        }],
        bbar: new Axis.PagingToolbar({
            store: ds
        })
    });
});
