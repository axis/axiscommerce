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
    var modifiedSet = false;
    var deleted = false;

    root = new Ext.tree.AsyncTreeNode({
        text: 'set'.l()
    });

    valueSet = new Ext.tree.TreePanel({
        region: 'west',
        header: false,
        autoScroll: true,
        collapsible: true,
        collapseMode: 'mini',
        split: true,
        width: 165,
        border: true,
        lines: false,
        animate: true,
        enableDD: false,
        rootVisible: false,
        loader: new Ext.tree.TreeLoader({
            url: Axis.getUrl('customer_custom-fields/get-value-sets')
        }),
        tbar: [{
            text:'Add'.l(),
            handler: function(){
                var node = root.appendChild(new Ext.tree.TreeNode({
                    text:'New Valueset'.l(),
                    allowDrag:false,
                    cls: 'newValueSet'
                }));

                valueSet.getSelectionModel().select(node);
                setTimeout(function(){
                    editor.editNode = node;
                    editor.startEdit(node.ui.textNode);
                }, 10);
                valueGrid.disable();
            },
            scope: this
        },{
            text:'Delete'.l(),
            handler: function(){
                if (!valueSet.getSelectionModel().selNode) {
                    return;
                } else if (valueSet.getSelectionModel().selNode.attributes.cls == 'newValueSet') {
                    valueSet.getSelectionModel().selNode.remove();
                } else if (confirm('Are you sure?')) {
                    Ext.Ajax.request({
                       url: Axis.getUrl('customer_custom-fields/ajax-delete-value-set'),
                       params: {id: valuesetId},
                       success: function() {
                           valueSet.getSelectionModel().selNode.remove();
                           modifiedSet = true;
                           deleted = true;
                           valueGrid.disable();
                       }
                    });
                }

            },
            scope: this
        },{
            text:'Save'.l(),
            handler: function(){
                var node = valueSet.getSelectionModel().selNode;
                if (!node) {
                    return;
                }
                data = {};
                data['name'] = node.text;
                data['id'] = node.id;
                jsonData = Ext.encode(data);
                Ext.Ajax.request({
                    url: Axis.getUrl('customer_custom-fields/ajax-save-value-set'),
                    params: {data: jsonData},
                    success: function(response) {
                        valuesetId = Ext.decode(response.responseText).valueset_id;
                        node.id = valuesetId;
                        node.attributes.cls = '';
                        node.getUI().removeClass('newValueSet');

                        vs.baseParams.valuesetId = valuesetId;
                        vs.reload();
                        modifiedSet = true;
                        valueGrid.enable();
                    }
                });
            },
            scope: this
        }]
    });

    valueSet.setRootNode(root);

    valueSet.on('click', function(node, e){
        if (node.attributes.cls == 'newValueSet') { //if valueSet is not saved
            valueGrid.disable();
        } else {
            valueGrid.enable();
            valuesetId = node.id;
            vs.baseParams.valuesetId = valuesetId;
            vs.reload();
        }
    });

    editor = new Ext.tree.TreeEditor(valueSet, new Ext.form.Field({
        cancelOnEsc: true,
        allowBlank:false,
        selectOnFocus: false
    }));

    vcm = new Ext.grid.ColumnModel(valuesetColumn);
    vcm.defaultSortable = true;

    var Value = new Ext.data.Record.create(valuesetRow);
    var valueRowClear = new Value(valuesetRowClear);

    vs = new Ext.data.Store({
        url: Axis.getUrl('customer_custom-fields/get-values'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        },Value)
    });
    var i=0;
    var valueGrid = new Ext.grid.EditorGridPanel({
        region: 'center',
        autoScroll: true,
        split: true,
        ds: vs,
        cm: vcm,
        clicksToEdit: 1,
        disabled: true,
        viewConfig: {
            forceFit: true,
            emptyText: 'No records found'.l()
        },
        sm: sm2,
        border: true,
        plugins: [isActive2],
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
            handler : function(){
                i++;
                valueGrid.stopEditing();
                valueRow = valueRowClear.copy('value'+i);
                vs.insert(0, valueRow);
                valueGrid.startEditing(0, 3);
            }
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            cls: 'x-btn-text-icon',
            handler : function(){
                var data = {};

                var modified = vs.getModifiedRecords();

                if (!modified.length)
                    return false;

                for (var i = 0, n = modified.length; i < n; i++) {
                    data[modified[i]['id']] = modified[i]['data'];
                }

                var jsonData = Ext.encode(data);
                Ext.Ajax.request({
                    url: Axis.getUrl('customer_custom-fields/ajax-save-value-set-values'),
                    params: {
                        data: jsonData,
                        customer_valueset_id: valuesetId
                    },
                    callback: function() {
                        vs.commitChanges();
                        vs.reload();
                    }
                })
            }
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler : function(){
                var selectedItems = valueGrid.getSelectionModel().selections.items;

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
                    url: Axis.getUrl('customer_custom-fields/ajax-delete-value-set-values'),
                    params: {data: jsonData},
                    callback: function() {
                        vs.reload();
                    }
                });
            }
        }]
    });

    win = new Axis.Window({
        border: false,
        title: 'Customer valueset'.l(),
        closeAction: 'hide',
        maximizable: true,
        layout: 'border',
        items: [valueGrid, valueSet]
    });

    win.on('resize', function(){
        valueGrid.doLayout();
    })

    win.on('beforehide', function(){
        editor.hide();
        if (modifiedSet) {
            Ext.getCmp('value-set-combo').store.reload();
            if (deleted)
                ds.reload();

            modifiedSet = false;
        }
    });
})

function editValues(){
     win.show();
}