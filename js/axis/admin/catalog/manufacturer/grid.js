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
 */

Ext.onReady(function(){
    
    var record = [
        {name: 'id', type: 'int'},
        {name: 'name', type: 'string'},
        {name: 'url', type: 'string'},
        {name: 'image', type: 'string'}
    ];
    for (var langId in Axis.languages) {
        record.push({'name': 'title_' + langId});
    }
    var manufacturer_object = Ext.data.Record.create(record);
    
    var ds = new Ext.data.Store({
        url: Axis.getUrl('catalog_manufacturer/list'),
        method: 'get',
        reader: new Ext.data.JsonReader({
            root : 'data',
            totalProperty: 'count',
            id: 'id'
        }, manufacturer_object),
        remoteSort: false,
        pruneModifiedRecords: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        }
    })
    
    var columns = [];
    columns.push({
        header: 'Id'.l(),
        dataIndex: 'id',
        width: 40
    }, {
        header: 'Name'.l(),
        dataIndex: 'name',
        editor: new Ext.form.TextField({
            allowBlank: false,
            maxLength: 128
        })
    }, {
        header: 'Url'.l(),
        dataIndex: 'url',
        editor: new Ext.form.TextField({
            allowBlank: false,
            maxLength: 128
        })
    }, {
        header: 'Image'.l(),
        dataIndex: 'image',
        editor: new Ext.form.TextField({
            allowBlank: true,
            maxLength: 255
        })
    });
    for (var langId in Axis.languages) {
        columns.push({
            header: 'Title ({language})'.l('core', Axis.languages[langId]),
            dataIndex: 'title_' + langId,
            width: 150,
            editor: new Ext.form.TextField({
               allowBlank: false,
               maxLength: 255
            })
        });
    }
    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: columns
    });
    
    var grid = new Axis.grid.EditorGridPanel({
        cm: cm,
        id: 'grid',
        store: ds,
        viewConfig: {
            forceFit: true,
            deferEmptyText: true
        },
        tbar: [{
            text: 'Add'.l(),
            handler: add,
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/add.png'
        }, {
            text: 'Edit'.l(),
            handler: function(){
                edit();
            },
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/page_edit.png'
        }, {
            text: 'Save'.l(),
            handler: save,
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/save_multiple.png'
        }, {
            text: 'Delete'.l(),
            handler: deleteSelected,
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/delete.png'
        },'->', {
            text: 'Reload'.l(),
            handler: reload,
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/refresh.png'
        }]
    });
    
    new Axis.Panel({
        items: [grid]
    });
    
    Ext.getCmp('grid').store.load();
    Ext.getCmp('grid').on('rowdblclick', function(grid, index){
        edit(grid.getStore().getAt(index));
    });
    
    function reload(){
        Ext.getCmp('grid').store.reload();
    }
    
    function add(){
        Ext.getCmp('window').show();
        Ext.getCmp('window').setTitle('New Manufacturer'.l());
        Ext.getCmp('form').getForm().clear();
    }
    
    function edit(record){
        record = record|| Ext.getCmp('grid').getSelectionModel().getSelected();
        
        if (!record) {
            return;
        }
        
        Ext.getCmp('window').show();
        Ext.getCmp('window').setTitle(record.get('name'));
        var form = Ext.getCmp('form').getForm();
        form.clear();
        var titles = {};
        for (var i in record.data) {
            if (i.indexOf('title_') === 0) {
                titles['data[' + i + ']'] = record.data[i];
            } else {
                form.findField('data[' + i + ']').setValue(record.data[i]);
            }
        }
        form.findField('data[title]').setValue(titles);
    }
    
    function save(){
        var modified = Ext.getCmp('grid').store.getModifiedRecords();
        
        if (!modified.length) {
            return false;
        }
        
        var data = {};
        for (var i = 0, len = modified.length; i < len; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }
        Ext.Ajax.request({
            url: Axis.getUrl('catalog_manufacturer/batch-save'),
            method: 'post',
            params: {
                data: Ext.encode(data)
            },
            success: reload
        })
    }
    
    function deleteSelected(){
        var selections = Ext.getCmp('grid').getSelectionModel().getSelections();
        
        if (!selections.length || !confirm('Are you sure?'.l())) {
            return;
        }
         
        var data = {};   
        for (var i = 0, len = selections.length; i < len; i++) {
            data[i] = selections[i]['id'];
        }
        Ext.Ajax.request({
            url: Axis.getUrl('catalog_manufacturer/delete'),
            method: 'post',
            params: {
                data: Ext.encode(data)
            },
            success: reload
        })
    }
});