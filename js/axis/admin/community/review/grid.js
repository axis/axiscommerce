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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

Ext.onReady(function(){
    
    var ds = new Ext.data.Store({
        url: Axis.getUrl('community_review/get-list'),
        method: 'get',
        reader: new Ext.data.JsonReader({
            id: 'id',
            root: 'data',
            totalProperty: 'total'
        }, review_object),
        remoteSort: true,
        sortInfo: {
            field: 'date_created',
            direction: 'DESC'
        }
    })
    
    function averageRating(value, p, record){
        var sum = 0;
        var count = 0;
        for (record_entity in record.data) {
            if (record_entity.indexOf('rating_') == 0) {
                if (record.data[record_entity] > 0) {
                    sum += record.data[record_entity];
                    count++;
                }
            }
        }
        if (!count) {
            return 'N/A'.l();
        }
        return (sum/count).toFixed(2);
    }
    
    var cm = new Ext.grid.ColumnModel([
        expander, {
            header: 'Product'.l(),
            dataIndex: 'product_name',
            id: 'product_name',
            menuDisabled: true,
            width: 300
        }, {
            header: 'Rating'.l(),
            menuDisabled: true,
            renderer: averageRating,
            width: 60
        }, {
            header: 'Author'.l(),
            menuDisabled: true,
            dataIndex: 'author'
        }, {
            header: 'Title'.l(),
            menuDisabled: true,
            dataIndex: 'title',
            id: 'title'
        }, {
            header: 'Date created'.l(),
            dataIndex: 'date_created',
            menuDisabled: true,
            renderer: function(v) {
                return Ext.util.Format.date(v);
            }
        }, {
            header: 'Status'.l(),
            menuDisabled: true,
            dataIndex: 'status',
            renderer: function(value) {
                return value.l();
            },
            width: 100
        }
    ]);
    cm.defaultSortable = true;
    
    var pagingBar = new Axis.PagingToolbar({
        store: ds,
        items:[
            '-', {
            pressed: false,
            enableToggle: true,
            text: 'Show review text'.l(),
            iconCls: 'x-btn-icon',
            icon: Axis.skinUrl + '/images/icons/application_view_list.png',
            toggleHandler: toggleDetails
        }]
    });
    
    function toggleDetails(btn, pressed){
        if (pressed) {
            Ext.each(Ext.query('.x-grid3-row', '#grid'), function(row){
                expander.expandRow(row);
            })
        } else {
            Ext.each(Ext.query('.x-grid3-row', '#grid'), function(row){
                expander.collapseRow(row);
            })
        }
    }
    
    var grid = new Axis.grid.GridPanel({
        autoExpandColumn: 'title',
        cm: cm,
        id: 'grid',
        enableColumnMove: false,
        store: ds,
        plugins: expander,
        tbar: [{
            text: 'Add'.l(),
            handler: function(){
                Ext.getCmp('form').getForm().clear();
                Ext.getCmp('window').show();
            },
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/add.png'
        }, {
            text: 'Edit'.l(),
            handler: edit,
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/page_edit.png'
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
        }],
        bbar: pagingBar
    })
    
    grid.on('rowdblclick', function(grid, rowIndex, e){
        loadEditForm(Ext.getCmp('grid').store.getAt(rowIndex));
    })
    
    Ext.getCmp('grid').store.load({params:{start:0, limit: pagingBar.pageSize}});
    
    new Axis.Panel({
        items: [
            grid
        ]
    });
    
    function reload(){
        Ext.getCmp('grid').store.reload();
    }
    
    function edit(){
        var selected = Ext.getCmp('grid').getSelectionModel().getSelected();
        
        if (!selected) {
            return;
        }
        
        loadEditForm(selected);
    }
    
    var tries = 0;
    function loadEditForm(row) {
        Ext.getCmp('form').getForm().clear();
        Ext.getCmp('product_combo').store.load({
            params: {
                id: row.get('product_id')
            },
            callback: function() {
                tryShowWindow();
            }
        })
        if (typeof row.get('customer_id') == 'number') {
            Ext.getCmp('customer_combo').store.load({
                params: {
                    id: row.get('customer_id')
                },
                callback: function() {
                    tryShowWindow();
                }
            });
        } else {
            tryShowWindow();
        }
        function tryShowWindow() {
            if (++tries == 2) {
                Ext.getCmp('window').setTitle(row.get('product_name')).show();
                fillForm(row);
                tries = 0;
            }
        }
    }
    
    function deleteSelected(){
        var selections = Ext.getCmp('grid').getSelectionModel().getSelections();
        
        if (!selections.length || !confirm('Are you sure?'.l())) {
            return;
        }
         
        var obj = {};   
        for (var i = 0, len = selections.length; i < len; i++) {
            obj[i] = selections[i]['id'];
        }
        var jsonData = Ext.encode(obj);
        Ext.Ajax.request({
            url: Axis.getUrl('community_review/delete'),
            method: 'post',
            params: {
                data: jsonData
            },
            success: reload,
            filure: function(){
                alert('An error has been occured'.l());
            }
        })
    }
})