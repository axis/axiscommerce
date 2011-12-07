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

var Config, grid;
Ext.onReady(function() {
    Config = {
        siteId: $('#site_id').val(),

        showWindow: function() {
            this.window.show();
        },

        getSelectedIds: function() {
            var data = [];
            var selectedItems = grid.getSelectionModel().selections.items;
            for (var i = 0; i < selectedItems.length; i++) {
                if (!selectedItems[i].id)
                    continue;
                data[i] = selectedItems[i].id;
            }
            return data;
        },

        edit: function(row) {
            if (!row.id) {
                return;
            }

            Ext.Ajax.request({
                url: Axis.getUrl('core/config-value/load'),
                params: {
                    path: row.id,
                    siteId: Config.siteId
                },
                success: function(response, request) {
                    if (isJSON(response.responseText)) {
                        return;
                    }
                    Config.window.show();
                    Config.window.body.update(response.responseText, true);
                    $('#confValue').focus();
                }
            });
        },

        onTreeClick: function(node, e) {
            if (typeof(node) == undefined) {
                return;
            }

            delete ds.baseParams['filter[tree][field]'];
            delete ds.baseParams['filter[tree][operator]'];
            delete ds.baseParams['filter[tree][value]'];

            if (node.id != tree.root.id) {
                ds.baseParams['filter[tree][field]']    = 'path';
                ds.baseParams['filter[tree][operator]'] = 'STRICT_LIKE';
                ds.baseParams['filter[tree][value]']    = node.id + '/%';
            }

            if (ds.lastOptions && ds.lastOptions.params) {
                ds.lastOptions.params.start = 0; // reset pagination
            }

            ds.reload();
        },

        save: function() {
            Ext.Ajax.request({
                url: $('#form-edit').attr('action'),
                form: 'form-edit',
                callback: function(response, options) {
                    Config.window.hide();
                    ds.reload();
                }
            });
        },

        clear: function() {
            if (Config.siteId == '0' || !confirm('Are you sure?'.l())) {
                return;
            }
            var items = Config.getSelectedIds();
            Ext.Ajax.request({
                url: Axis.getUrl('core/config-value/use-global'),
                params: {
                    pathItems: Ext.encode(items),
                    siteId: Config.siteId
                },
                callback: function() {
                    ds.reload();
                }
            });
        },

        copyGlobal: function() {
            if (Config.siteId == '0' || !confirm('Are you sure?'.l())) {
                return;
            }
            var items = Config.getSelectedIds();
            Ext.Ajax.request({
                url: Axis.getUrl('core/config-value/copy-global'),
                params: {
                    pathItems: Ext.encode(items),
                    siteId: Config.siteId
                },
                callback: function() {
                    ds.reload();
                }
            });
        },
        window: new Ext.Window({
            layout: 'fit',
            width: 345,
            height: 340,
            autoScroll: true,
            bodyStyle:'background:#FFF;',
            closeAction: 'hide',
            title: 'Editing Config Value'.l(),
            buttons: [{
                text: 'Save'.l(),
                handler: function() {
                    Config.save()
                }
            }, {
                text: 'Cancel'.l(),
                handler: function(){
                    Config.window.hide();
                }
            }]
        })
    };

    Ext.get('site_id').on('change', function(evt, elem, o) {
        Config.siteId = elem.value;

        var bp = ds.baseParams;

        delete bp['site_id'];
        if (elem.value) {
            bp['site_id'] = Config.siteId;
        }

        ds.reload();
        toggleButtons();
    });

    function disableButtons() {
        Ext.getCmp('copy-from-global').disable();
        Ext.getCmp('clear-from-global').disable();
    }

    function enableButtons() {
        Ext.getCmp('copy-from-global').enable();
        Ext.getCmp('clear-from-global').enable();
    }

    var treeToolBar = new Ext.Toolbar();
    treeToolBar.addText('Site: ');
    treeToolBar.addElement('site_id');
    treeToolBar.addFill();

    treeToolBar.addButton({
        cls: 'x-btn-icon',
        icon: Axis.skinUrl + '/images/icons/refresh.png',
        handler: function(){
            rootNode.reload();
        }
    });

    /* Configuration tree */
    var tree = new Ext.tree.TreePanel({
        region: 'west',
        collapsible: true,
        collapseMode: 'mini',
        header: false,
        split: true,
        width: 230,
        useArrows:true,
        autoScroll:true,
        animate: false,
        containerScroll: true,
        loader: new Ext.tree.TreeLoader({
            dataUrl: Axis.getUrl('core/config-field/list')
        }),
        tbar: treeToolBar
    });

    // set the root node
    var rootNode = new Ext.tree.AsyncTreeNode({
        text: 'Configuration'.l(),
        draggable:false,
        id: '0'
    });
    tree.setRootNode(rootNode);

    tree.on('click', Config.onTreeClick);
    rootNode.expand();

    // Configuration grid
    var ds = new Ext.data.Store({
        autoLoad: true,
        baseParams: {
            limit: 25
        },
        url: Axis.getUrl('core/config-value/list'),
        reader: new Ext.data.JsonReader({
            id: 'path',
            root: 'data',
            totalProperty: 'count'
        }, [
            {name: 'id', type: 'int'},
            {name: 'config_type'},
            {name: 'path'},
            {name: 'title'},
            {name: 'value'},
            {name: 'from'}
        ]),
        remoteSort: true,
        sortInfo: {
            field: 'path',
            direction: 'ASC'
        }
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: 'Path'.l(),
            dataIndex: 'path',
            width: 300
        }, {
            header: 'Value'.l(),
            dataIndex: 'value',
            width: 200,
            sortable: false,
            filterable: false
        }, {
            header: 'Title'.l(),
            dataIndex: 'title',
            id: 'title',
            sortable: false,
            filterable: false
        }, {
            header: 'Took from'.l(),
            dataIndex: 'from',
            filterable: false,
            width: 90
        }]
    });

    var grid = new Axis.grid.GridPanel({
        autoExpandColumn: 'title',
        ds: ds,
        cm: cm,
        plugins: [new Axis.grid.Filter()],
        tbar: [{
            text: 'Copy from global'.l(),
            icon: Axis.skinUrl + '/images/icons/copy.png',
            disabled: true,
            id: 'copy-from-global',
            handler: Config.copyGlobal
        }, {
            text: 'Clear'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            disabled: true,
            id: 'clear-from-global',
            handler: Config.clear
        }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: function() {
                ds.reload();
            }
        }],
        bbar: new Axis.PagingToolbar({
            store: ds
        })
    });

    disableButtons();

    grid.on('rowdblclick', function(grid, index) {
        Config.edit(grid.getStore().getAt(index));
    });

    grid.getSelectionModel().on('selectionchange', function(evt, rowIndex, record) {
        toggleButtons();
    });

    function toggleButtons() {
        var selectedItem = grid.getSelectionModel().getSelected();
        if (!selectedItem || Config.siteId == 0) {
            disableButtons();
        } else {
            enableButtons();
        }
    };

    new Axis.Panel({
        items: [
            tree,
            grid
        ]
    });
});
