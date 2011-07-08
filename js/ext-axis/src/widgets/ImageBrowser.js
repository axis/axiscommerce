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

Axis.ImageBrowser = Ext.extend(Ext.util.Observable, {
    
    rootPath: 'media',
    
    rootText: 'media',
    
    constructor: function(config) {
        Ext.apply(this, config);
        
        this.events = {
            /**
             * @event cancel
             * Fires when cancel button was pressed
             */
            'cancelpress': true,
            /**
             * @event ok
             * Fires when ok button was pressed
             * @param {Array} Selected records
             */
            'okpress': true
        };
        Axis.ImageBrowser.superclass.constructor.call(this);
        
        this.mediaTreePanel = new Axis.FileTreePanel({
            collapseMode: 'mini',
            collapsible: true,
            rootPath: this.rootPath,
            rootText: this.rootText,
            split: true,
            region: 'west',
            header: false,
            width: 200,
            listeners: {
                click: {
                    scope: this,
                    fn: this.treeNodeClick
                }
            }
        });
        
        this.mediaTreePanel.getLoader().baseParams.mode = 'folder';
        
        var store = new Ext.data.JsonStore({
            url: this.mediaTreePanel.url,
            baseParams: {
                cmd: 'get',
                mode: 'file',
                recursive: 0
            },
            fields: [
                {name: 'name', mapping: 'text'},
                {name: 'absolute_path'},
                {name: 'path'},
                {name: 'absolute_url'}
            ]
        });
        
        this.dataview = new Ext.DataView({
            store: store,
            tpl: new Ext.XTemplate(
                '<ul class="image-browser">',
                    '<tpl for=".">',
                        '<li class="image">',
                            '<img width="64" height="64" src="{absolute_url}" alt="{name}"/>',
                            '<strong>{name}</strong>',
                        '</li>',
                    '</tpl>',
                '</ul>'
            ),
            itemSelector: 'li.image',
            overClass   : 'image-over',
            singleSelect: true,
            multiSelect : true,
            autoScroll  : true
        });
        
        this.dataPanel = new Ext.Panel({
            border: true,
            collapsible: true,
            header: false,
            items: this.dataview,
            layout: 'fit',
            region: 'center',
            split: true,
            tbar: [{
                handler: this.deleteClick,
                scope: this,
                icon: Axis.skinUrl + '/images/icons/delete.png',
                text: 'Delete'.l()
            }, '->', {
                enableToggle: true,
                scope: this,
                toggleHandler: this.includeSubdirClick,
                icon: Axis.skinUrl + '/images/icons/application_side_tree.png',
                text: 'Search in subdirectories'.l()
            }]
        });
        
        this.window = new Axis.Window({
            border: false,
            layout: 'border',
            maximizable: true,
            split: true,
            title: 'Media browser'.l(),
            width: 720,
            items: [this.mediaTreePanel, this.dataPanel],
            buttons: [{
                icon: Axis.skinUrl + '/images/icons/accept.png',
                text: 'Ok'.l(),
                scope: this,
                handler: this.okPress
            }, {
                icon: Axis.skinUrl + '/images/icons/cancel.png',
                text: 'Cancel'.l(),
                scope: this,
                handler: this.cancelPress
            }]
        });
    },
    
    destroy: function() {
        if (this.mediaTreePanel) {
            this.mediaTreePanel.destroy();
        }
        if (this.dataview) {
            this.dataview.destroy();
        }
        if (this.dataPanel) {
            this.dataPanel.destroy();
        }
        if (this.window) {
            this.window.destroy();
        }
        this.purgeListeners();
    },
    
    hide: function() {
        this.window.hide();
    },
    
    show: function() {
        this.window.show();
    },
    
    deleteClick: function() {
        if (!this.dataview.getSelectedRecords().length || !confirm('Are you sure?'.l())) {
            return;
        }
        
        var file = {};
        Ext.each(this.dataview.getSelectedRecords(), function(r, i) {
            file[i] = r.get('path');
        });
        
        file = Ext.encode(file);
        Ext.Ajax.request({
            url: this.mediaTreePanel.deleteUrl ? 
                this.mediaTreePanel.deleteUrl : this.mediaTreePanel.url,
            params: {
                batch: 1,
                cmd: 'delete',
                file: file
            },
            scope: this,
            callback: function() {
                this.dataview.store.reload();
            }
        })
    },
    
    includeSubdirClick: function(button, state) {
        this.dataview.store.baseParams.recursive = state ? 1 : 0;
        this.dataview.store.reload();
    },
    
    treeNodeClick: function(node, e) {
        if (false === node.attributes.is_dir) {
            return;
        }
        this.dataview.store.baseParams.path = this.mediaTreePanel.getPath(node);
        this.dataview.store.load();
    },
    
    okPress: function() {
        this.fireEvent('okpress', this.dataview.getSelectedRecords());
        this.hide();
    },
    
    cancelPress: function() {
        this.fireEvent('cancelpress');
        this.hide();
    }
});
