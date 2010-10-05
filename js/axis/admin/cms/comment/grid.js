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
    
    Ext.form.Field.prototype.msgTarget = 'under';
    
    var filters = new Ext.ux.grid.GridFilters({
        filters: [
            {type: 'numeric', dataIndex: 'id'},
            {type: 'string', dataIndex: 'category_name'},
            {type: 'string', dataIndex: 'page_name'},
            {type: 'string', dataIndex: 'author'},
            {type: 'date', dataIndex: 'created_on', dateFormat: 'Y-m-d'},
            {type: 'date', dataIndex: 'modified_on', dateFormat: 'Y-m-d'},
            {
                 type: 'list',
                 dataIndex: 'status',
                 options: status,
                 phpMode: true
            }
        ]
    });

    var commentStore = new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('cms_comment/get-comments'),
            method: 'post'
        }),
        reader: new Ext.data.JsonReader({
            totalProperty: 'totalCount',
            root: 'comments',
            id: 'id'},
            ['id', 'category_name', 'author', 'email', 'created_on', 'modified_on', 'page_name', 'status', 'content']
        ),
        groupField: 'page_name',
        sortInfo: {field: 'created_on', direction: 'ASC'},
        remoteSort: true
    });

    var statusCombo = new Ext.form.ComboBox({
        triggerAction: 'all',
        transform: 'status-combo',
        lazyRender: true,
        typeAhead: true,
        forceSelection: false
    });

    var postContent = new Ext.grid.RowExpander({
        tpl : new Ext.Template(
            '<p style="padding-left: 45px; margin: 5px 0px;"><b>Author:</b> {author}</p>',
            '<p style="padding-left: 45px; margin: 5px 0px;"><b>Email:</b> {email}</p>',
            '<div style="padding: 0px 8px 5px 45px; line-height: 17px; text-align: justify"><b>Content:</b> {content}</div>'
        )
    });

    var commentColumn = new Ext.grid.ColumnModel([
        postContent, {
            header: 'Id'.l(),
            dataIndex: 'id',
            width: 30
        }, {
            header: 'Category'.l(),
            dataIndex: 'category_name',
            width: 120
        }, {
            header: 'Page Name'.l(),
            dataIndex: 'page_name',
            id: 'page_name',
            width: 150
        }, {
            header: 'Author'.l(),
            dataIndex: 'author',
            width: 130,
            editor: new Ext.form.TextField({
                maxLength: 45
            })
        }, {
            header: 'Created'.l(),
            dataIndex: 'created_on',
            width: 125
        }, {
            header: 'Modified'.l(),
            dataIndex: 'modified_on',
            width: 125
        }, {
            header: 'Status'.l(),
            dataIndex: 'status',
            width: 80,
            editor: statusCombo,
            renderer: function(value) {
               return statusCombo.store.data.items[value].data.text;
            }
        }
    ]);
    commentColumn.defaultSortable = true;

    var commentActionMenu = new Ext.menu.Menu({
        id: commentActionMenu,
        items: menuStatus
    });

    commentGrid = new Axis.grid.EditorGridPanel({
        id: 'grid-comment',
        view: new Ext.grid.GroupingView({
            forceFit: true,
            emptyText: 'No records found'.l()
        }),
        autoExpandColumn: 'page_name',
        cm: commentColumn,
        ds: commentStore,
        plugins: [filters, postContent],
        stripeRows: true,
        tbar: [{
            text: 'Add'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler: createComment
        }, {
            text: 'Edit'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            handler: editComment
        }, {
            text: 'Change Status to'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/menu_action.png',
            menu: commentActionMenu
        }, {
            text: 'Save'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler: saveChanges
        }, {
            text: 'Delete'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/delete.png',
             handler: deleteSelected
        }, '->', {
            text: 'Reload'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: reloadGrid
        }],
        bbar: new Axis.PagingToolbar({
            store: commentStore
        })
    });

    commentGrid.on('rowdblclick', function(grid, comment, e) {
        editComment();
    });

    commentGrid.getStore().load({params:{start:0, limit:25}});
})

function reloadGrid() {
    commentGrid.getStore().reload();
}

function deleteSelected() {
    var selectedItems = commentGrid.getSelectionModel().selections.items;
    
    if (!selectedItems.length || !confirm('Are you sure?'.l())) {
        return;
    }
        
    var data = {};
   
    for (var i = 0; i < selectedItems.length; i++) {
        data[i] = selectedItems[i].id;
    }
    var jsonData = Ext.encode(data);
    Ext.Ajax.request({
        url: Axis.getUrl('cms_comment/delete-comment'),
        params: {data: jsonData},
        callback: function() {
            commentGrid.getStore().reload();
        }
    });
}

function saveChanges() {
    var modified = commentGrid.getStore().getModifiedRecords();
    if (!modified.length) return;

    var data = {};
    
    for (var i = 0; i < modified.length; i++) {
        data[modified[i]['id']] = modified[i]['data'];
    }
    
    var jsonData = Ext.encode(data);
    Ext.Ajax.request({
        url: Axis.getUrl('cms_comment/quick-save'),
        params: {data: jsonData},
        callback: function() {
            commentGrid.getStore().commitChanges();
            commentGrid.getStore().reload();
        }
    });
}

function editComment() {
    var selected = commentGrid.getSelectionModel().getSelected();
    if (!selected) {
        return;
    }
    comment = selected.id;
    commentWindow.show();
    commentWindow.setTitle('Comment'.l());
    commentForm.getForm().setValues({
        author:  selected.get('author'),
        email:   selected.get('email'),
        status:  selected.get('status'),
        content: selected.get('content')
    });
}

function createComment() {
    if (isNaN(page)) {
        return alert('Select page in left tree panel'.l());
    }
    comment = 'new';
    commentForm.getForm().reset();
    commentWindow.show();
    commentWindow.setTitle('Add'.l());
}

function setStatus(status) {
    var selected = commentGrid.getSelectionModel().getSelections();
    for (var i = 0; i < selected.length; i++) {
        selected[i].set('status', status);
    }
}
