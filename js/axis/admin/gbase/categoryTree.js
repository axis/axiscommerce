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

var CategoryGrid = {

    siteId: null,

    /**
     * @property {Axis.grid.GridTree} el
     */
    el: null,

    /**
     * @property {Ext.data.Record} record
     */
    record: null,

    /**
     * @property {Ext.menu.Menu} menu
     */
    contextMenu: null,

    collapseAll: function() {
        CategoryGrid.el.store.each(function(r) {
            CategoryGrid.el.store.collapseNode(r);
        })
    },

    expandAll: function() {
        CategoryGrid.el.store.each(function(r) {
            CategoryGrid.el.store.expandNode(r);
        })
    },

    reload: function() {
        CategoryGrid.el.store.reload();
    },

    onRowClick: function(grid, index, e) {
        var el = Ext.get(Ext.lib.Event.getTarget(e));
        if (el.hasClass('x-grid3-row-checker')
            || el.hasClass('ux-maximgb-treegrid-elbow-active')
            || el.hasClass('ux-row-action-item')) {

            return false;
        }

        var record = CategoryGrid.el.store.getAt(index);

        CategoryGrid.siteId = record.get('site_id');

        if (Ext.getCmp('tab-grid-wrapper').getActiveTab().id == 'grid-gbase-list') {
            gbaseGrid.getStore().baseParams = {
                site_id     : CategoryGrid.siteId,
                product_type: record.get('name'),
                limit       : Ext.getCmp('paging-toolbar-gbase').pageSize
            };
            gbaseGrid.getStore().load();
        } else {
            localGrid.getStore().baseParams = {
                catId: record.get('id'),
                limit: Ext.getCmp('paging-toolbar-local').pageSize
            };
            localGrid.getStore().load();
        }
    },

    onRowContextMenu: function(grid, index, e) {
        e.preventDefault();

        var record = CategoryGrid.el.store.getAt(index),
            lft = record.get('lft'),
            rgt = record.get('rgt');

        if (0 == record.get('lvl')) {
            Ext.getCmp('menu-category').hide();
            Ext.getCmp('menu-branch').show();
        } else if (rgt - lft > 1) {
            Ext.getCmp('menu-category').show();
            Ext.getCmp('menu-branch').show();
        } else {
            Ext.getCmp('menu-category').show();
            Ext.getCmp('menu-branch').hide();
        }

        CategoryGrid.contextMenu.showAt(e.getXY());
        grid.getSelectionModel().selectRecords([record]);
    },

    exportItems: function(recursive) {
        var record = CategoryGrid.el.getSelectionModel().getSelected();
        if (!record) {
            return false;
        }

        var params = {
            catId       : record.get('id'),
            site        : record.get('site_id'),
            language    : Ext.getDom('language').value,
            country     : Ext.getDom('gCountry').value,
            currency    : Ext.getDom('currency').value,
            recursive   : recursive
        };

        CategoryGrid.doExportRequest(params, 1);
    },

    doExportRequest: function(params, clearSession) {
        if (clearSession) {
            Ext.getCmp('extProgressBar').updateProgress();
            Ext.getCmp('extProgressBar').updateText('Initializing...');
            Ext.get('lightbox-info').show();
        }

        Ext.Ajax.request({
            url: Axis.getUrl('googlebase/export-branch'),
            params: Ext.apply(params, {
                clearSession: clearSession
            }),
            callback: function(options, success, response){
                if (success) {
                    var obj = Ext.util.JSON.decode(response.responseText);
                    Ext.getCmp('extProgressBar').updateProgress(obj.processed/obj.count, 'Exported ' + obj.processed + ' of ' + obj.count);
                    if (!obj.finalize) {
                        CategoryGrid.doExportRequest(params, 0);
                    } else {
                        Ext.get('lightbox-info').hide();
                    }
                } else {
                    Ext.getCmp('extProgressBar').updateText('An error has been occured. Connecting...');
                    CategoryGrid.doExportRequest(params, 0);
                }
            }
        });
    }

};

Ext.onReady(function() {

    CategoryGrid.contextMenu = new Ext.menu.Menu({
        items: [{
            text: 'Export category'.l(),
            id: 'menu-category',
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/move.png',
            handler: function(){
                CategoryGrid.exportItems(0)
            }
        }, {
            text: 'Export branch'.l(),
            id: 'menu-branch',
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/tree_expand.png',
            handler: function(){
                CategoryGrid.exportItems(1)
            }
        }]
    });

    CategoryGrid.record = Ext.data.Record.create([
        { name: 'id', type: 'int' },
        { name: 'name' },
        { name: 'lvl', type: 'int' },
        { name: 'lft', type: 'int' },
        { name: 'rgt', type: 'int' },
        { name: 'site_id', type: 'int' },
        { name: 'status' },
        { name: 'disable_remove' },
        { name: 'disable_edit' }
    ]);

    var ds = new Axis.data.NestedSetStore({
        autoLoad: true,
        reader: new Ext.data.JsonReader({
            root: 'data',
            idProperty: 'id'
        }, CategoryGrid.record),
        rootFieldName: 'site_id',
        url: Axis.getUrl('catalog_category/get-flat-tree')
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: false,
            menuDisabled: true
        },
        columns: [{
            dataIndex: 'name',
            header: 'Name'.l(),
            id: 'name',
            renderer: function (value, meta, record) {
                if (record.get('status') != 'enabled') {
                    value = '<span class="disabled">' + value + '</span>';
                }

                meta.attr += 'ext:qtip="ID: ' + record.get('id') + '"';
                return value;
            }
        }]
    });

    CategoryGrid.el = new Axis.grid.GridTree({
        autoExpandColumn: 'name',
        cm: cm,
        collapseMode: 'mini',
        ds: ds,
        sm: new Ext.grid.RowSelectionModel({
            singleSelect: true
        }),
        enableDragDrop: false,
        id: 'grid-category-list',
        massAction: false,
        master_column_id: 'name',
        region: 'west',
        width: 280,
        listeners: {
            'rowclick': CategoryGrid.onRowClick,
            'rowcontextmenu': CategoryGrid.onRowContextMenu
        },
        tbar: {
            enableOverflow: true,
            items: [{
                handler: CategoryGrid.expandAll,
                icon: Axis.skinUrl + '/images/icons/expand-all.gif',
                overflowText: 'Expand'.l(),
                tooltip: 'Expand'.l()
            }, '-',  {
                handler: CategoryGrid.collapseAll,
                icon: Axis.skinUrl + '/images/icons/collapse-all.gif',
                overflowText: 'Collapse'.l(),
                tooltip: 'Collapse'.l()
            }, '->', {
                handler: CategoryGrid.reload,
                icon: Axis.skinUrl + '/images/icons/refresh.png',
                text: 'Reload'.l()
            }]
        }
    });

    var progressBar = new Ext.ProgressBar({
        text:'Initializing...'.l(),
        id: 'extProgressBar',
        renderTo: 'lightbox-info'
    });

});