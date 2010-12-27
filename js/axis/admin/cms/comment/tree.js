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

var CommentTree = {

    pageId: null,

    el: null,

    reload: function() {
        CommentTree.el.getLoader().load(CommentTree.el.root);
    },

    onClick: function(node, e) {
        CommentTree.pageId = null;

        var site        = node.attributes.siteId,
            category    = node.attributes.catId,
            page        = node.attributes.pageId,
            baseParams  = CommentGrid.el.getStore().baseParams;

        delete baseParams['uncategorized'];
        delete baseParams['filter[tree][field]'];
        delete baseParams['filter[tree][value]'];

        if ('null' != site) {
            baseParams['filter[tree][field]'] = 'cc.site_id';
            baseParams['filter[tree][value]'] = site;
        } else if ('null' != category) {
            baseParams['filter[tree][field]'] = 'cpcat.cms_category_id';
            baseParams['filter[tree][value]'] = category;
        } else if ('null' != page) {
            baseParams['filter[tree][field]'] = 'cms_page_id';
            baseParams['filter[tree][value]'] = page;
            CommentTree.pageId = page;
        } else if ('lost' == node.id) {
            baseParams['uncategorized'] = 1;
        }

        CommentGrid.el.getStore().reload();
    }
};

Ext.onReady(function() {

    var root = new Ext.tree.AsyncTreeNode({
        text: 'Axis root node'.l(),
        draggable:false,
        siteId: 'null',
        id:'rootNode'
    });

    var ctLoader = new Ext.tree.TreeLoader({
        url: Axis.getUrl('cms_comment/get-page-tree'),
        listeners: {
            'beforeload': function(stLoader, node) {
                CommentTree.el.root.appendChild([
                    new Ext.tree.TreeNode({
                        text:'All comments'.l(),
                        id: 'all',
                        siteId: 'null',
                        catId: 'null',
                        pageId: 'null',
                        iconCls: 'icon-folder',
                        allowDrag: false
                    })
                ]);
            },
            'load': function(ctLoader, node){
                CommentTree.el.root.collapse(true);
            }
        }
    });

    CommentTree.el = new Ext.tree.TreePanel({
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
        split: true,
        listeners: {
            'click': CommentTree.onClick
        },
        tbar: ['->', {
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: CommentTree.reload
        }]
    });
});
