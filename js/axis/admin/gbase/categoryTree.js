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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

Ext.onReady(function(){
    
    var contextMenu = new Ext.menu.Menu({
        id: 'context-menu',
        items: [{
            text: 'Export category'.l(),
            id: 'menu-category',
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/move.png',
            handler: function(){
                exportItems(0)
            }
        }, {
            text: 'Export branch'.l(),
            id: 'menu-branch',
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/tree_expand.png',
            handler: function(){
                exportItems(1)
            }
        }]
    })
    
    var rootNode = new Ext.tree.AsyncTreeNode({
        text: 'Sites'.l(),
        draggable: false,
        id: '0'
    })
    
    var categoryTree = new Ext.tree.TreePanel({
        id: 'tree-category-list',
        animate: false,
        enableDD: false,
        useArrows: true,
        width: 230,
        rootVisible: true,
        autoScroll: true,
        root: rootNode,
        region: 'west',
        collapsible: true,
        collapseMode: 'mini',
        header: false,
        split: true,
        loader: new Ext.tree.TreeLoader({
            url: Axis.getUrl('catalog_category/get-items/')
        })
    })
    
    var progressBar = new Ext.ProgressBar({
        text:'Initializing...'.l(),
        id: 'extProgressBar',
        renderTo: 'lightbox-info'
    })
    
    categoryTree.on('click', function(node, e) {
        if (Ext.getCmp('tab-grid-wrapper').getActiveTab().id == 'grid-gbase-list') {
            gbaseGrid.getStore().baseParams = {
                site_id : node.attributes.site_id,
                product_type: node.attributes.name
            };
            gbaseGrid.getStore().load({params:{
                start: 0, 
                limit: Ext.getCmp('paging-toolbar-gbase').pageSize
            }});
        } else if (node.id == 0) {
            return false;
        } else {
            siteId = node.attributes.site_id;
            
            localGrid.getStore().baseParams = {
                catId: node.id
            };
            localGrid.getStore().load({params:{
                start: 0, 
                limit: Ext.getCmp('paging-toolbar-local').pageSize
            }});
        }
    });
    
    categoryTree.on('contextmenu', function(node, e) {
        e.preventDefault();
        if (node.id == 0) 
            return false;
        
        if (node.attributes.site_node) {
            Ext.getCmp('menu-category').hide();
            Ext.getCmp('menu-branch').show();
        }
        else if (!node.hasChildNodes()) {
            Ext.getCmp('menu-branch').hide();
            Ext.getCmp('menu-category').show();
        }
        else {
            Ext.getCmp('menu-branch').show();
            Ext.getCmp('menu-category').show();
        }
            
        contextMenu.showAt(e.getXY());
        node.select();
    })
    
    categoryTree.getRootNode().expand();
})

function exportItems(recursive){
    var node = Ext.getCmp('extCategoryTree').getSelectionModel().getSelectedNode();
    
    if (!node || node.id == 0)
        return false;
    
    var params = {};
        
    params['catId'] = node.id;
    params['siteId'] = node.attributes.site_id;
    params['language'] = Ext.getDom('language').value;
    params['country']  = Ext.getDom('gCountry').value;
    params['currency'] = Ext.getDom('currency').value;
    params['recursive'] = recursive;
    
    if (isNaN(params['siteId'])) {
        alert('Site is undefined');
        return false;
    }
    
    ajaxExportBranch(params, 1);
    
}

function ajaxExportBranch(params, clearSession){
    
    if (clearSession) {
        Ext.getCmp('extProgressBar').clear();
        Ext.getCmp('extProgressBar').updateText('Initializing...');
        Ext.get('lightbox-info').show();
    }
    
    Ext.Ajax.request({
        url: Axis.getUrl('gbase_index/export-branch'),
        method: 'post',
        params: {
            language: params['language'],
            country: params['country'],
            currency: params['currency'],
            site: params['siteId'],
            catId: params['catId'],
            recursive: params['recursive'],
            clearSession: clearSession
        },
        callback: function(options, success, response){
            if (success) {
                var obj = Ext.util.JSON.decode(response.responseText);
                Ext.getCmp('extProgressBar').updateProgress(obj.processed/obj.count, 'Exported ' + obj.processed + ' of ' + obj.count);
                if (!obj.finalize) {
                    ajaxExportBranch(params, 0);
                } else {
                    Ext.get('lightbox-info').hide();
                }
            } else {
                Ext.getCmp('extProgressBar').updateText('An error has been occured. Connecting...');
                ajaxExportBranch(params, 0);
            }
        }
    })
}