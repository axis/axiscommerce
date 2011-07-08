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

var CategoryTree = {

    el: null,

    reload: function() {
        CategoryTree.el.getLoader().load(CategoryTree.el.root);
    },

    add: function() {
        var node = CategoryTree.el.getSelectionModel().getSelectedNode();
        if (!node || !node.attributes.site_id) {
            alert('Select site or parent category'.l());
            return;
        }

        if (isNaN(node.id)) {
            Category.add(0, node.attributes.site_id);
        } else {
            Category.add(node.id, node.attributes.site_id);
        }
    },

    edit: function(id) {
        var node = CategoryTree.el.getSelectionModel().getSelectedNode();
        if (!node || isNaN(node.id)) {
            return;
        }
        Category.load(node.id);
    },

    remove: function(id) {
        var selected = CategoryTree.el.getSelectionModel().getSelectedNode();
        if (!selected
            || isNaN(selected.id)
            || !confirm('Are you sure? All child categories will be also deleted'.l())) {

            return;
        }

        Ext.Ajax.request({
            url: Axis.getUrl('cms_index/delete-category'),
            params: {
                id: selected.id
            },
            success: function() {
                CategoryTree.el.getLoader().load(CategoryTree.el.root);
            }
        });
    },

    onClick: function(node, e) {
        var category = node.attributes.id,
            baseParams = PageGrid.el.getStore().baseParams;

        delete baseParams['uncategorized'];
        delete baseParams['filter[site][field]'];
        delete baseParams['filter[site][value]'];

        if (category.indexOf('_') === 0) {
            baseParams['filter[site][field]'] = 'cc.site_id';
            baseParams['filter[site][value]'] = node.attributes.site_id;
        } else if (category == 'lost') {
            baseParams['uncategorized'] = 1;
        } else if (category != 'all') {
            baseParams['filter[site][field]'] = 'cc.id';
            baseParams['filter[site][value]'] = category;
        }

        PageGrid.el.getStore().reload();
    },

    onNodeDragOver: function(dragEvent) {
        var parentNode  = dragEvent.target,
            dropNode    = dragEvent.dropNode;

        if (dragEvent.point == "above" || dragEvent.point == "below") {
            parentNode = parentNode.parentNode;
        }

        if (parentNode.id == 'rootNode' || isNaN(dropNode.id)) {
            return false;
        }
    },

    onNodeDrop: function(dropEvent) {
        var parentNode  = dropEvent.target,
            dropNode    = dropEvent.dropNode;

        if (dropEvent.point == 'above' || dropEvent.point == 'below') {
            parentNode = parentNode.parentNode;
        }

        Ext.Ajax.request({
            url: Axis.getUrl('cms_index/move-category'),
            params: {
                id          : dropNode.id,
                parent_id   : isNaN(parentNode.id) ? null : parentNode.id,
                site_id     : parentNode.attributes.site_id
            },
            failure: CategoryTree.reload
        });
    }
};

Ext.onReady(function() {

    Ext.QuickTips.init();

    var root = new Ext.tree.AsyncTreeNode({
        text: 'Axis root node'.l(),
        draggable:false,
        id:'rootNode'
    });

    var stLoader = new Ext.tree.TreeLoader({
        url: Axis.getUrl('cms_index/get-site-tree'),
        listeners: {
            'beforeload': function(loader, node) {
                CategoryTree.el.root.appendChild([
                     new Ext.tree.TreeNode({
                         text:'All Pages'.l(),
                         id: 'all',
                         iconCls: 'icon-folder',
                         allowDrag: false
                     }),
                     new Ext.tree.TreeNode({
                         text:'Uncategorized'.l(),
                         id: 'lost',
                         iconCls: 'icon-bin',
                         allowDrag: false
                     })
                ]);
            }
        }
    });

    CategoryTree.el = new Ext.tree.TreePanel({
        id: 'tree-site',
        root: root,
        rootVisible: false,
        width: 250,
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
        listeners: {
            'click'         : CategoryTree.onClick,
            'nodedragover'  : CategoryTree.onNodeDragOver,
            'nodedrop'      : CategoryTree.onNodeDrop
        },
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler: function() {
                CategoryTree.add();
            }
        }, {
            text: 'Edit'.l(),
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            handler: function() {
                CategoryTree.edit();
            }
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: function() {
                CategoryTree.remove();
            }
        }, '->', {
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: function() {
                CategoryTree.reload();
            }
        }]
     });
});
