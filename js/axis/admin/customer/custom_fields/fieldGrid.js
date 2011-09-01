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

    var sm = new Ext.grid.CheckboxSelectionModel();
    var required = new Axis.grid.CheckColumn({
        header: "Required".l(),
        dataIndex: 'required',
        width: 68
    });
    var isActive = new Axis.grid.CheckColumn({
        header: "Visibility".l(),
        dataIndex: 'is_active',
        width: 60
    });

    ds = new Ext.data.Store({
        url: Axis.getUrl('account/field/list'),
        baseParams: {
            groupId: groupId
        },
        reader: new Ext.data.JsonReader({
                root: 'data',
                id: 'id'
            },
            Field //see index.phtml
        ),
        sortInfo:{
            field:'is_active',
            direction:'DESC'
        }
    });

    vss.on('load', function(){
        tryLoadGrid();
    })
    gs.on('load', function(){
        tryLoadGrid();
    })
    typeStore.on('load', function(){
        tryLoadGrid();
    })
    validatorStore.on('load', function(){
        tryLoadGrid();
    })

    vss.on('exception', function(){
        vss.load();
    })
    gs.on('exception', function(){
        gs.load();
    })
    typeStore.on('exception', function(){
        typeStore.load();
    })
    validatorStore.on('exception', function(){
        validatorStore.load();
    })
    ds.on('exception', function(){
        ds.load();
    })

    var loadedStore = 0;

    function tryLoadGrid(){
        if (++loadedStore == 4)
            ds.load();
    }

    var cm = new Ext.grid.ColumnModel([
        sm, {
            header: 'Group*'.l(),
            dataIndex: 'customer_field_group_id',
            editor: Ext.getCmp('group-combo').cloneConfig(),
            renderer: function(value) {
                if (value == '' || !value) {
                    return "None";
                } else {
                    if (gs.getById(value) && gs.getById(value).get('title')) {
                        return gs.getById(value).get('title');
                    } else {
                        return value;
                    }
                }
            }
        }, {
            header: 'Name'.l(),
            dataIndex: 'name',
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        },
        isActive,
        required,  {
           header: "Field Type".l(),
           dataIndex: 'field_type',
           width: 80,
           editor: Ext.getCmp('type-combo').cloneConfig()
        }, {
           header: "Valueset".l(),
           dataIndex: 'customer_valueset_id',
           width: 90,
           editor: Ext.getCmp('value-set-combo').cloneConfig({
               mode: 'local'
           }),
           renderer: function(value) {
               if (value == '' || !value) {
                   return "None";
               } else {
                   return vss.getById(value).get('text');
               }
           }
        }, {
            header: 'Sort Order'.l(),
            dataIndex: 'sort_order',
            width: 80,
            editor: new Ext.form.TextField({
               allowBlank: false
            })
        }, {
            header: 'Validator'.l(),
            dataIndex: 'validator',
            width: 70,
            editor: Ext.getCmp('validator-combo').cloneConfig(),
            renderer: function(value) {
                if (value == '' || !value) {
                    return Ext.getCmp('validator-combo').store.data.items[0].data.value;
                } else {
                    return Ext.getCmp('validator-combo').store.getById(value).get('value');
                }
            }
        }
    ]);
    cm.defaultSortable = true;

    var grid = new Ext.grid.EditorGridPanel({
        store: ds,
        id: 'grid-fields',
        cm: cm,
        sm: sm,
        renderTo: 'fields-grid',
        height: 400,
        plugins: [isActive, required],
        clicksToEdit: 1,
        viewConfig: {
            forceFit: true,
            emptyText: 'No records found'.l()
        },
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
            handler : function(){
                Ext.getCmp('fieldEditWindow').show();
                Ext.getCmp('fieldForm').getForm().clear();
                fillForm(null, true);
            }
        }, {
            text: 'Edit'.l(),
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            cls: 'x-btn-text-icon',
            handler : editField
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            cls: 'x-btn-text-icon',
            handler : function(){
                var data = {};

                var modified = ds.getModifiedRecords();
                var length = modified.length;

                if (length > 0){
                    for (var i = 0; i < length; i++) {
                        data[modified[i]['id']] = modified[i]['data'];
                    }
                    var jsonData = Ext.encode(data);
                    Ext.Ajax.request({
                        url: Axis.getUrl('account/field/batch-save'),
                        params: {data: jsonData},
                        callback: function() {
                            ds.commitChanges();
                            ds.reload();
                        }
                    })
                }
            }
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler : function(){
                var selectedItems = grid.getSelectionModel().selections.items;

                if (selectedItems.length < 1)
                    return;

                if (!confirm('Delete field(s)?'))
                    return;

                var data = {};

                for (var i = 0; i < selectedItems.length; i++) {
                    data[i] = selectedItems[i].id;
                }
                var jsonData = Ext.encode(data);
                Ext.Ajax.request({
                    url: Axis.getUrl('account/field/remove'),
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
            handler: function() {
                ds.reload();
            }
        }],
        listeners: {
            'beforerender': function(cmp) {
                cmp.width = $('#ext-cmp-layout').width() - $('#ext-cmp-layout .sidebox').width() - 20;
            }
        }
    });

    grid.on('rowdblclick', function(grid, index, e){
        Ext.getCmp('fieldEditWindow').show();
        Ext.getCmp('fieldForm').getForm().clear();
        fillForm(grid.store.getAt(index));
    })
})

function editField(){
    selected = Ext.getCmp('grid-fields').getSelectionModel().getSelected();

    if (!selected)
        return;

    Ext.getCmp('fieldEditWindow').show();
    Ext.getCmp('fieldForm').getForm().clear();
    fillForm(selected); //see index.phtml
}