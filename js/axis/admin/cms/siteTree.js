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

    Ext.QuickTips.init();

    Ext.form.Field.prototype.msgTarget = 'under';

    var root = new Ext.tree.AsyncTreeNode({
        text: 'Axis root node'.l(),
        draggable:false,
        id:'rootNode'
    });

    stLoader = new Ext.tree.TreeLoader({
        url: Axis.getUrl('cms_index/get-site-tree')
    });

    stLoader.on('beforeload', function(stLoader, node) {
        stLoader.baseParams.siteId = node.attributes.siteId;
        root.appendChild(
            new Ext.tree.TreeNode({
                 text:'All Pages'.l(),
                 id: 'all',
                 siteId: 'null',
                 catId: 'null',
                 iconCls: 'icon-folder',
                 allowDrag: false
             }),
             new Ext.tree.TreeNode({
                 text:'Uncategorized'.l(),
                 id: 'lost',
                 siteId: 'null',
                 catId: 'null',
                 iconCls: 'icon-bin',
                 allowDrag: false
             })
        );
    }, this);

    stLoader.on('load', function(stLoader, node){
        buildGridMenu();
    }, this)

    siteTree = new Ext.tree.TreePanel({
        id: 'tree-site',
        root: root,
        rootVisible: false,
        width: 230,
        border: true,
        autoScroll: true,
        loader: stLoader,
        enableDD: true,
        animate: false,
        lines: false,
        region: 'west',
        collapsible: true,
        collapseMode: 'mini',
        header: false,
        split: true,
        tbar: [{
            text: 'Add'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler: createCategory
        },{
            text: 'Edit'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            handler: editCategory
        },{
            text: 'Delete'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: deleteCategory
        }, {
            cls: 'x-btn-icon',
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: reloadTree
        }]
     });

     siteTree.on('click', function(node, e) {
         site = node.attributes.siteId;
         pageCategory = category = node.attributes.id;

         pageGrid.getStore().baseParams = {
             'filterTree[type]' : site != 'null' ? 'site' : 'category',
             'filterTree[data]': site != 'null' ? site : category
         };

         pageGrid.getStore().load({params:{start:0, limit:20}});
    });

    siteTree.on('nodedragover', function(dragEvent) {
        parentNode = dragEvent.target;
        dropNode = dragEvent.dropNode;

        if(dragEvent.point == "above" || dragEvent.point == "below")
            parentNode = parentNode.parentNode;

        if(parentNode.id == 'rootNode' || isNaN(dropNode.id)) {
            return false;
        }
    });

    siteTree.on('nodedrop', function(dropEvent) {
        parentNode = dropEvent.target;
        dropNode = dropEvent.dropNode;

        category = dropNode.id;

        if (dropEvent.point == 'above' || dropEvent.point == 'below') {
            parentNode = parentNode.parentNode;
        }

        getNodeParams(parentNode);

        Ext.Ajax.request({
            url: Axis.getUrl('cms_index/move-category'),
            params: {catId: category, parentId: parentId, siteId: site},
            success: buildGridMenu
        })
    })
});

var root, stLoader;

function reloadTree() {
    siteTree.getLoader().load(siteTree.root);
}

function getNodeParams(node) {
    if (node.attributes.siteId != 'null') {
        site = node.attributes.siteId;
        parentId = 0;
    } else {
        parentId = node.id;
        site = 0;
    }
}

function createCategory(){
    node = siteTree.getSelectionModel().getSelectedNode();
    if (!node) {
        alert('Select site or parent category'.l());
        return;
    }

    getNodeParams(node);

    category = 'new';
    categoryForm.getForm().clear();
    categoryWindow.show();
}

function editCategory() {
    var node = siteTree.getSelectionModel().getSelectedNode();
    if (!node || isNaN(node.id)) {
        return;
    }
    parentNode = node.parentNode;

    getNodeParams(parentNode);

    category = node.id;
    categoryForm.getForm().clear();
    categoryWindow.show();
    categoryForm.getForm().load({
        url: Axis.getUrl('cms_index/get-category/catId/') + category,
        method: 'get'
    });
}

function deleteCategory() {
    var itemToDelete = siteTree.getSelectionModel().getSelectedNode();
    if (!itemToDelete || itemToDelete.attributes.siteId != 'null') {
        alert('Select category to delete');
        return;
    }
    if (!confirm('Are you sure? All child categories will be also deleted'.l())) {
        return;
    }

    parentNode = itemToDelete.parentNode;

    Ext.Ajax.request({
        url: Axis.getUrl('cms_index/delete-category'),
        params: {id: itemToDelete.id},
        success: function() {
            siteTree.getLoader().load(siteTree.root, function() {
                categoryWindow.hide();
            });
            pageGrid.getStore().load({params:{start:0, limit:15}});
        }
    });
}

/*
 * grid menu builder. rebuil after every siteTree load
 */
function buildGridMenu() {
    tree = siteTree.root.childNodes;
}
