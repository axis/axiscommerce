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
        {name: 'status', type: 'string'}
    ];
    for (var langId in Axis.languages) {
        record.push({'name': 'title_' + langId});
    }
    var rating_object = Ext.data.Record.create(record);
    
    var ds = new Ext.data.Store({
        url: Axis.getUrl('community_rating/get-list'),
        method: 'get',
        reader: new Ext.data.JsonReader({
            id: 'id',
            root: 'data'
        }, rating_object),
        remoteSort: false,
        pruneModifiedRecords: true,
        sortInfo: {
            field: 'name',
            direction: 'ASC'
        }
    })
    
    var columns = [{
        header: 'Id'.l(),
        dataIndex: 'id',
        width: 40,
        menuDisabled: true
    }, {
        header: 'Name'.l(),
        dataIndex: 'name',
        menuDisabled: true,
        editor: new Ext.form.TextField({
            allowBlank: false,
            maxLength: 64
        })
    }];
    for (var langId in Axis.languages) {
        columns.push({
            header: 'Title ({language})'.l('core', Axis.languages[langId]),
            dataIndex: 'title_' + langId,
            width: 150,
            editor: new Ext.form.TextField({
               allowBlank: false,
               maxLength: 128
            })
        });
    }
    
    var status = new Axis.grid.CheckColumn({
        header: 'Status'.l(),
        dataIndex: 'status',
        fields: {
            enabled: 'enabled',
            disabled: 'disabled'
        }
    });
    columns.push(status);
    var cm = new Ext.grid.ColumnModel(columns);
    cm.defaultSortable = true;
    
    var grid = new Axis.grid.EditorGridPanel({
        cm: cm,
        id: 'grid',
        enableColumnMove: false,
        store: ds,
        plugins: status,
        viewConfig: {
            forceFit: true,
            deferEmptyText: true,
            emptyText: 'No records found'.l()
        },
        tbar: [{
            text: 'Add'.l(),
            handler: add,
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/add.png'
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
    })
    
    Ext.getCmp('grid').store.load();
    
    new Axis.Panel({
        items: [
            grid
        ]
    })
    
    function reload(){
        Ext.getCmp('grid').store.reload();
    }
    
    function add(){
        var empty_record = {
            id: 'new',
            name: '',
            status: 'enabled'
        };
        for (var langId in Axis.languages) {
            empty_record['title_' + langId] = '';
        }
        var rating = new rating_object(empty_record);
        var cnt = Ext.getCmp('grid');
        cnt.stopEditing();
        cnt.store.insert(0, rating);
        cnt.startEditing(0, 2);
    }
    
    function save(){
        var modified = Ext.getCmp('grid').store.getModifiedRecords();
        
        if (!modified.length) {
            return false;
        }
        
        var data = {};
        
        for (var i = 0; i < modified.length; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }
        
        var jsonData = Ext.encode(data);
        
        Ext.Ajax.request({
            url: Axis.getUrl('community_rating/save'),
            method: 'post',
            params: {
                data: jsonData
            },
            success: reload
        })
    }
    
    function deleteSelected(){
        var selections = Ext.getCmp('grid').getSelectionModel().getSelections();
        
        if (!selections.length) {
            return;
        }
        
        if (!confirm('Are you sure?'.l())) {
            return;
        }
         
        var obj = new Object();   
        for (var i = 0, len = selections.length; i < len; i++) {
            obj[i] = selections[i]['id'];
        }
        var jsonData = Ext.encode(obj);
        Ext.Ajax.request({
            url: Axis.getUrl('community_rating/delete'),
            method: 'post',
            params: {
                data: jsonData
            },
            success: reload
        })
    }
});