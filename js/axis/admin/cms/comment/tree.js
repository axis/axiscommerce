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
     
    var root = new Ext.tree.AsyncTreeNode({
        text: 'Axis root node'.l(),
        draggable:false,
        siteId: 'null',
        id:'rootNode'
    });
    
    var ctLoader = new Ext.tree.TreeLoader({
        url: Axis.getUrl('cms_comment/get-page-tree')
    });
    
    ctLoader.on('beforeload', function(stLoader, node) {
        ctLoader.baseParams.siteId = node.attributes.siteId;
    }, this);
   
    ctLoader.on('load', function(ctLoader, node){
        root.collapse(true);
    }, this)
    
    commentTree = new Ext.tree.TreePanel({
        id: 'tree-comment',
        root: root,
        rootVisible: false,
        width: 230,
        border: true,
        autoScroll: true,
        loader: ctLoader,
        enableDD: false,
        animate: false,
        lines: false,
        region: 'west',
        collapsible: true,
        collapseMode: 'mini',
        header: false,
        split: true
    });
    
    root.appendChild(
         new Ext.tree.TreeNode({
              text:'All comments'.l(), 
              id: 'all',
              siteId: 'null',
              catId: 'null',
              pageId: 'null',
              iconCls: 'icon-folder',
              allowDrag: false
         })
    );
    
    commentTree.on('click', function(node, e) {
        site = node.attributes.siteId;
        category = node.attributes.catId;
        page =  node.attributes.pageId;        
        
        commentGrid.getStore().baseParams = {
            'filterTree[type]' : site != 'null' ? 'site' : 
                                (category != 'null' ? 'category' :
                                (page != 'null' ? 'page' : 'node')),
            'filterTree[data]': site != 'null' ? site : 
                               (category != 'null' ? category :
                               (page != 'null' ? page : node.id))
        };
        commentGrid.getStore().load({params:{start:0, limit:15}});
    });
});
