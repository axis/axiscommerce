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

Ext.onReady(function(){
    var Item = {
        activeId: 0,
        record: Ext.data.Record.create([
            {name: 'id'},
            {name: 'code'},
            {name: 'language'},
            {name: 'locale'}
        ]),
        
        create: function() {
            window.show();
            Ext.getCmp('form_language').getForm().clear();
        },
        
        edit: function(row) {
            window.show();
            Ext.getCmp('form_language').getForm().setValues({
                'locale_code': row.get('locale'),
                'language': row.get('language'),
                'id': row.id
            })
        },
        
        save: function() {
            Ext.getCmp('form_language').getForm().submit({
                url: Axis.getUrl('locale_language/save'),
                success: function(form, response) {
                    form.clear();
                    window.hide();
                    ds.reload();
                }
            })
        },
        
        remove: function() {
            if (!confirm('Are you sure?'.l())) {
                return;
            }
            var data = {};
            var selectedItems = grid.getSelectionModel().selections.items;
            for (var i = 0; i < selectedItems.length; i++) {
                if (!selectedItems[i]['data']['id']) continue;
                data[i] = selectedItems[i]['data']['id'];
            }
                
            Ext.Ajax.request({
                url: Axis.getUrl('locale_language/delete'),
                params: {data: Ext.encode(data)},
                callback: function() {
                    ds.reload();
                }
            });
        }
    };
    
    Ext.QuickTips.init();
    
    var ds = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('locale_language/list')
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Item.record),
        remoteSort: true,
        pruneModifiedRecords: true
    });
    
    var cm = new Ext.grid.ColumnModel([{
        header: "Id".l(),
        dataIndex: 'id',
        width: 30,
        sortable: true
    }, {
        header: "Code".l(),
        dataIndex: 'code',
        width: 50,
        sortable: true
    }, {
        header: "Title".l(),
        dataIndex: 'language',
        width: 150
    }, {
        header: "Locale".l(),
        dataIndex: 'locale',
        width: 100
    }]);
    
    var grid = new Axis.grid.GridPanel({
        ds: ds,
        cm: cm,
        viewConfig: {
            forceFit: true,
            emptyText: 'No records found'.l()
        },
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
            handler: Item.create
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler: Item.remove
        }, '->', {
            text: 'Reload'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: function(){
                grid.getStore().reload();
            }
        }]
    });
    
    new Axis.Panel({
        items: [
            grid
        ]
    })
    
    grid.on('rowdblclick', function(grid, rowIndex, e){
        Item.edit(grid.getStore().getAt(rowIndex));
    })
    
    ds.load();
    
    var zendLocales = new Ext.form.ComboBox({
        transform: 'locale',
        triggerAction: 'all',
        name: 'locale_code',
        hiddenName: 'locale_code',
        lazyRender: true,
        fieldLabel: 'Locale'.l(),
        value: 'en_US',
        allowBlank: false
    })
    
    var form = new Ext.form.FormPanel({
        border: false,
        labelAlign: 'left',
        id: 'form_language',
        defaults: {
            anchor: '100%'
        },
        items: [zendLocales, {
            xtype: 'textfield',
            fieldLabel: 'Title'.l(),
            name: 'language',
            allowBlank: false,
            maxLength: 45
        }, {
            xtype: 'hidden',
            name: 'id',
            value: ''
        }]
    })
    
    var window = new Ext.Window({
        title: 'Language'.l(),
        items: form,
        closeAction: 'hide',
        resizable: true,
        maximizable: true,
        id: 'window_language',
        constrainHeader: true,
        autoScroll: true,
        bodyStyle: 'background: white; padding: 10px;',
        width: 450,
        height: 150,
        minWidth: 260,
        buttons: [{
            text: 'Save'.l(),
            handler: Item.save
        }, {
            text: 'Close'.l(),
            handler: close
        }]
    })

}, this);

function close(){
    Ext.getCmp('window_language').hide();
}