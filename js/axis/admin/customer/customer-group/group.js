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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

Ext.onReady(function(){
    
     Ext.QuickTips.init();
    
     
     var ds = new Ext.data.Store({
        url: Axis.getUrl('account/group/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, [
            {name: 'id',          type: 'int',    mapping: 'id'},
            {name: 'name',        type: 'string', mapping: 'name'},
            {name: 'description', type: 'string', mapping: 'description'}
        ]),
        pruneModifiedRecords: true
    });
    
    var cm = new Ext.grid.ColumnModel([{
        header: "Group Name".l(),
        dataIndex: 'name',
        sortable: true,
        editor: new Ext.form.TextField({
            allowBlank: false
        }),
        width: 300
     }, {
         header: "Description".l(),
         id: 'description',
         dataIndex: 'description',
         sortable: true,
         editor: new Ext.form.TextField({
             allowBlank: true
         })
     }]);
     
    var grid = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'description',
        ds: ds,
        cm: cm,
        sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
        tbar: [{
             text: 'Add'.l(),
             icon: Axis.skinUrl + '/images/icons/add.png',
             cls: 'x-btn-text-icon',
             handler : function(){
                 var p = new Group({
                     name: 'New Group',
                     description: ''
                 });
                 grid.stopEditing();
                 ds.insert(0, p);
                 grid.startEditing(0, 0);
             }
         },{
             text: 'Save'.l(),
             icon: Axis.skinUrl + '/images/icons/accept.png',
             cls: 'x-btn-text-icon',
             handler : function(){
                  var data = {};
                 var modified = ds.getModifiedRecords();
                 for (var i = 0; i < modified.length; i++) {
                      data[modified[i]['id']] = modified[i]['data'];
                 }
                 var jsonData = Ext.encode(data);
                 Ext.Ajax.request({
                      url: Axis.getUrl('account/group/batch-save'),
                      params: {data: jsonData},
                      callback: function() {
                           ds.reload();
                      }
                 });
             }
         },{
             text: 'Delete'.l(),
             icon: Axis.skinUrl + '/images/icons/delete.png',
             cls: 'x-btn-text-icon',
             handler : function(){
                  if (!confirm('Delete group?'))
                       return;
                  var data = {};
                 var selectedItems = grid.getSelectionModel().selections.items;
                 for (var i = 0; i < selectedItems.length; i++) {
                      data[i] = selectedItems[i].id;
                 }
                 var jsonData = Ext.encode(data);
                 Ext.Ajax.request({
                      url: Axis.getUrl('account/group/remove'),
                      params: {data: jsonData},
                      callback: function() {
                           ds.reload();
                      }
                 });
             }
         }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-text-icon',
            handler: function(){
                grid.getStore().reload();
            }
        }]
    });
    
    new Axis.Panel({
        items: [
            grid
        ]
    });
    
    var Group = Ext.data.Record.create([
        {name: 'name', type: 'string'},
        {name: 'description'}
    ]);

    ds.load();
    
}, this);