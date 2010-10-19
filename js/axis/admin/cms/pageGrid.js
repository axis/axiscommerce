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

    Ext.QuickTips.init();

    var status = new Axis.grid.CheckColumn({
        header: 'Status'.l(),
        width: 60,
        dataIndex: 'is_active'
    });

    var comment = new Axis.grid.CheckColumn({
        header: 'Comments'.l(),
        width: 80,
        dataIndex: 'comment'
    });

    var showInBox = new Axis.grid.CheckColumn({
        header: 'Show in box'.l(),
        width: 80,
        dataIndex: 'show_in_box'
    });

    var pageFields = new Ext.data.Record.create([
        'id',
        'category_name',
        'name',
        'link',
        'is_active',
        'comment',
        'layout',
        'show_in_box'
    ]);

    pageStore = new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('cms_index/get-pages')
        }),
        reader: new Ext.data.JsonReader({
            totalProperty: 'totalCount',
            root: 'pages',
            id: 'id'
        }, pageFields),
        sortInfo: {field: 'category_name', direction: 'ASC'},
        remoteSort: true
    });

    layoutStore = new Ext.data.Store({
        url: Axis.getUrl('template_layout/list-collect'),
        reader: new Ext.data.JsonReader({
            root: 'data'
        }, ['name']),
        autoLoad: true
    });

    var batchMenu = new Ext.menu.Menu({
        items: [
        {
            text: 'Copy Selected to'.l(),
            icon: Axis.skinUrl + '/images/icons/copy_multiple.png',
            menu: storeCategoryMenu
        }, {
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
                    batch('is_active', 0);
                }
            }, {
                text: 'Enabled'.l(),
                icon: Axis.skinUrl + '/images/icons/enabled.png',
                handler: function() {
                    batch('is_active', 1);
                }
            }]
        }, {
            text: 'Comments'.l(),
            icon: Axis.skinUrl + '/images/icons/comments.png',
            menu: [{
                text: 'Disabled'.l(),
                icon: Axis.skinUrl + '/images/icons/disabled.png',
                handler: function() {
                    batch('comment', 0);
                }
            }, {
                text: 'Enabled'.l(),
                icon: Axis.skinUrl + '/images/icons/enabled.png',
                handler: function() {
                    batch('comment', 1);
                }
            }]
        }, {
            text: 'Remove from db'.l(),
            icon: Axis.skinUrl + '/images/icons/drive_delete.png',
            handler: function() {
                batch('remove', 1);
            }
        }]
    });

    var pageColumn = new Ext.grid.ColumnModel([{
        header: 'Category'.l(),
        width: 120,
        dataIndex: 'category_name'
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
        header: 'Link'.l(),
        id: 'link',
        width: 100,
        dataIndex: 'link',
        editor: new Ext.form.TextField({
            allowBlank: true,
            maxLength: 45
        })
    }, {
        header: 'Layout'.l(),
        width: 150,
        dataIndex: 'layout',
        editor: new Ext.form.ComboBox({
            triggerAction: 'all',
            displayField: 'name',
            typeAhead: true,
            mode: 'local',
            valueField: 'name',
            store: layoutStore
        })
    },
        status,
        comment,
        showInBox
    ]);
    pageColumn.defaultSortable = true;

    var searchPage = new Ext.ux.grid.Search({
        iconCls:false,
        width: 170,
        emptyText: 'Find a Page'.l(),
        showSelectAll: false,
        paramNames: {
            query:  'filterSearch[query]',
            fields: 'filterSearch[fields]'
        },
        minLength: 0,
        checkIndexes: ['name'],
        disableIndexes: ['id', 'category_name', 'link', 'layout', 'is_active', 'comment']
    })

    var paging = new Axis.PagingToolbar({
        store: pageStore
    })

    pageGrid = new Axis.grid.EditorGridPanel({
        ds: pageStore,
        cm: pageColumn,
        id: 'grid-page-list',
        plugins: [
            status,
            comment,
            showInBox,
            searchPage
        ],
        view: new Ext.grid.GroupingView({
            forceFit: true,
            emptyText: 'No records found'.l()
        }),
        tbar: [{
            text: 'Add'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler: createPage
        }, {
            text: 'Edit'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            handler: editPage
        }, {
            text: 'Batch'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/menu_action.png',
            menu: batchMenu
        }, {
            text: 'Save'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler: saveChanges
        }, {
            text: 'Delete'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: function() {
                batch('remove', 0);
            }
        }, '->', {
            text: 'Reload'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: reloadGrid
        }],
        bbar: paging
    });

    pageGrid.on('rowdblclick', function(grid, pageId, e) {
        editPage();
    })

    pageGrid.getStore().baseParams = {
        'filterTree[type]': 'category',
        'filterTree[data]': 'all'
    };

    pageGrid.getStore().load({params:{start:0, limit:25}})

})

function batch(field, value) {
    switch (field) {
        case 'remove':
            if (value == '0') {       //remove from selected treeNode
                categoryFrom = category;
                deletePage();
            } else if (value == '1') { //remove from db
                categoryFrom = 'all';
                deletePage();
            }
            break;
        case 'category':
            var selectedItems = pageGrid.getSelectionModel().selections.items;

            if (selectedItems.length < 1)
                return;

            var data = {};

            for (var i = 0; i < selectedItems.length; i++) {
                data[i] = selectedItems[i].id;
            }
            var jsonData = Ext.encode(data);
            Ext.Ajax.request({
                url: Axis.getUrl('cms_index/copy-page'),
                params: {pages: jsonData, catId: value}
            });
            break;
        default:
            var selected = pageGrid.getSelectionModel().getSelections();
            for (var i = 0; i < selected.length; i++) {
                selected[i].set(field, value);
            }
            break;
    }
}

function deletePage() {
    var selectedItems = pageGrid.getSelectionModel().selections.items;

    if (selectedItems.length < 1)
        return;

    if (!confirm('Delete Page(s)?'))
        return;

    var data = {};

    for (var i = 0; i < selectedItems.length; i++) {
        data[i] = selectedItems[i].id;
    }
    var jsonData = Ext.encode(data);
    Ext.Ajax.request({
        url: Axis.getUrl('cms_index/delete-page'),
        params: {data: jsonData, catId: categoryFrom, siteId: site},
        callback: function() {
            pageGrid.getStore().reload();
        }
    });
}

function createPage() {
    page = 'new';
    pageForm.getForm().clear();
    resetNodes();
    pageWindow.show();
}

function editPage() {
    page = pageGrid.getSelectionModel().getSelected();
    if (!page) return;
    page = page.id;
    pageForm.getForm().clear();
    pageWindow.show();
    pageForm.getForm().load({
        url: Axis.getUrl('cms_index/get-page-data'),
        params: {pageId: page},
        success: setCheckedNodes
    });
}

function setCheckedNodes() {
    resetNodes();
    var node;

    for (var i = 0; i < pageForm.reader.jsonData.category.length; i++) {
        node = categoryBlock.getNodeById(parseInt(pageForm.reader.jsonData.category[i]))
        node.ui.toggleCheck(true);
        categoryBlock.getSelectionModel().select(node, null, true);
        node.checked = true;
        node.attributes.checked = true;
    }
}

function resetNodes() {
    categoryBlock.root.cascade(function(){
        this.ui.toggleCheck(false);
        this.unselect();
        this.checked = false;
        this.attributes.checked = false;
    })
}

function saveChanges() {
    var modified = pageGrid.getStore().getModifiedRecords();
    if (modified.length < 1) return alert('Nothing to save');
    var data = {};

    for (var i = 0; i < modified.length; i++) {
        data[modified[i]['id']] = modified[i]['data'];
    }

    var jsonData = Ext.encode(data);
    Ext.Ajax.request({
        url: Axis.getUrl('cms_index/quick-save-page'),
        params: {data: jsonData},
        callback: function() {
            pageGrid.getStore().commitChanges();
            pageGrid.getStore().reload();
        }
    })
}
function reloadGrid() {
    pageGrid.getStore().reload();
}