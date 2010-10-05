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

var Block = {
    
    add: function() {
        BlockWindow.form.getForm().clear();
        BlockWindow.el.show();
    },
    
    load: function(id) {
        BlockWindow.form.getForm().clear();
        BlockWindow.form.getForm().load({
            url:  Axis.getUrl('cms_block/get-data/id/') + id,
            method: 'get',
            success: function(form, action) {
                BlockWindow.el.show();
            }
        });
    }
};

var BlockGrid = {
    
    el: null,
    
    remove: function() {
        var selectedItems = BlockGrid.el.getSelectionModel().getSelections();
    
        if (!selectedItems.length || !confirm('Are you sure?'.l())) {
            return;
        }
            
        var data = {};
       
        for (var i = 0; i < selectedItems.length; i++) {
            data[i] = selectedItems[i].id;
        }
        var jsonData = Ext.encode(data);
        Ext.Ajax.request({
            url: Axis.getUrl('cms_block/delete'),
            params: {data: jsonData},
            callback: function() {
                BlockGrid.el.getStore().reload();
            }
        });
    },
    
    save: function() {
        var modified = BlockGrid.el.getStore().getModifiedRecords();
        if (!modified.length) {
            return;
        }
        var data = {};
        
        for (var i = 0; i < modified.length; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }
        
        var jsonData = Ext.encode(data);
        Ext.Ajax.request({
            url:  Axis.getUrl('cms_block/batch-save'),
            params: {data: jsonData},
            callback: function() {
                BlockGrid.el.getStore().commitChanges();
                BlockGrid.el.getStore().reload();
            }
        });
    },
    
    reload: function() {
        BlockGrid.el.getStore().reload();
    }
};

Ext.onReady(function() {

    Ext.QuickTips.init();

    Ext.form.Field.prototype.msgTarget = 'side';

    var status = new Axis.grid.CheckColumn({
        header: 'Status'.l(),
        width: 60,
        dataIndex: 'is_active'
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: 'Name'.l(),
            width: 250,
            dataIndex: 'name',
            id: 'name',
            editor: new Ext.form.Field({
                allowBlank: false,
                maxLength: 45
            })
        }, status]
    });

    var ds = new Ext.data.Store({
        autoLoad: true,
        url: Axis.getUrl('cms_block/list'),
        reader: new Ext.data.JsonReader({
            root: 'data', 
            idProperty: 'id'
        }, [
            {name: 'id', type: 'int'},
            {name: 'name'},
            {name: 'is_active', type: 'int'}
        ])
    });

    BlockGrid.el = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'name',
        cm: cm,
        ds: ds,
        plugins: [status],
        tbar: [{
            text: 'Add'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler: Block.add
        }, {
            text: 'Edit'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            handler: function() {
                var record = BlockGrid.el.getSelectionModel().getSelected();
                if (record) {
                    Block.load(record.get('id'));
                }
            }
        }, {
            text: 'Save'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler: BlockGrid.save
        }, {
            text: 'Delete'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: BlockGrid.remove
        }, '->', {
            text: 'Reload'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: BlockGrid.reload
        }],
        listeners: {
            'rowdblclick': function(grid, index, e) {
                Block.load(grid.getStore().getAt(index).get('id'));
            }
        }
    });

    new Axis.Panel({
        items: [
            BlockGrid.el
        ]
    })
});