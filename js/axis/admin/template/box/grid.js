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

Ext.onReady(function() {

    Ext.QuickTips.init();

    Ext.form.Field.prototype.msgTarget = 'under';

    var sm = new Ext.grid.CheckboxSelectionModel();

    var show = new Axis.grid.CheckColumn({
        header: 'Show'.l(),
        width: 60,
        dataIndex: 'show'
    });
    
    var hide = new Axis.grid.CheckColumn({
        header: 'Hide'.l(),
        width: 60,
        dataIndex: 'hide'
    });

    var cm = new Ext.grid.ColumnModel([
        sm, {
            header: 'Page'.l(),
            width: 250,
            dataIndex: 'page'
        }, 
        show, 
        hide, {
            header: 'Block'.l(),
            width: 250,
            dataIndex: 'block'
        }, {
            header: 'Config'.l(),
            width: 250,
            dataIndex: 'config'
        }
    ]);
    cm.defaultSortable = true;

    var ds = new Ext.data.Store({
        url: Axis.getUrl('template_box/get-box-page'),
        reader: new Ext.data.JsonReader({
            root: 'blocks', 
            id: 'id'
        }, ['page', 'show', 'hide', 'block', 'config'])
    });

    var grid = new Ext.grid.EditorGridPanel({
        title: 'Exception'.l(),
        sm: sm,
        cm: cm,
        store: ds,
        id: 'grid-box-exception',
        border: false,
        viewConfig: {
            forceFit: true,
            emptyText: 'No records found'.l()
        },
        height: 200,
        autoScroll: true,
        clicksToEdit: 1,
        plugins: [show, hide],
        stripeRows: true,
        tbar: [{
            text: 'New'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/add.png',
            //handler: createBlock
        }, {
            text: 'Delete',
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/delete.png',
            //handler: deleteBlock
        }, '->', {
            text: 'Reload',
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: reloadGrid
        }]
    });

    //grid.getStore().load();
})

function reloadGrid() {
    blockGrid.getStore().reload();
}

function deleteBlock() {
    var selectedItems = blockGrid.getSelectionModel().selections.items;
    
    if (selectedItems.length < 1)
        return; 
        
    if (!confirm('Delete Block(s)?'))
        return;
        
    var data = {};
   
    for (var i = 0; i < selectedItems.length; i++) {
        data[i] = selectedItems[i].id;
    }
    var jsonData = Ext.encode(data);
    Ext.Ajax.request({
        url: Axis.getUrl('cms_block/delete-block'),
        params: {data: jsonData},
        callback: function() {
            blockGrid.getStore().reload();
        }
    });
}

function saveChanges() {
    var modified = blockGrid.getStore().getModifiedRecords();
    if (modified.length < 1) return alert('Nothing to save');
    var data = {};
    
    for (var i = 0; i < modified.length; i++) {
        data[modified[i]['id']] = modified[i]['data'];
    }
    
    var jsonData = Ext.encode(data);
    Ext.Ajax.request({
        url:  Axis.getUrl('cms_block/quick-save-block'),
        params: {data: jsonData},
        callback: function() {
            blockGrid.getStore().commitChanges();
            blockGrid.getStore().reload();
        }
    })
}

function editBlock() {
    block = blockGrid.getSelectionModel().getSelected();
    if (!block) return;
    currentBlock = block.id;

    blockForm.getForm().reset();
    blockWindow.show();
    blockForm.getForm().load({
        url:  Axis.getUrl('cms_block/get-block-data'),
        params: {blockId: currentBlock}
    });
}

function createBlock() {
    currentBlock = 'new';
    form.getForm().reset();
    window.show();
}
