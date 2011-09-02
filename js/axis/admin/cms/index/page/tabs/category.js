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

var PageCategoryTree = {

    el: null,

    clearData: function() {
        PageCategoryTree.el.root.cascade(function() {
            this.ui.toggleCheck(false);
            this.unselect();
            this.checked = false;
            this.attributes.checked = false;
        });
    },

    loadData: function(data) {
        if (!PageCategoryTree.el.root.loaded) {
            PageCategoryTree.el.getLoader().on('load', function() {
                PageCategoryTree.loadData(data);
            });
        } else {
            PageCategoryTree.clearData();
            for (var i = 0, len = data.category.length; i < len; i++) {
                var node = PageCategoryTree.el.getNodeById(data.category[i])
                node.ui.toggleCheck(true);
                PageCategoryTree.el.getSelectionModel().select(node, null, true);
                node.checked = true;
                node.attributes.checked = true;
            }
        }
    },

    getData: function() {
        var data = {};
        PageCategoryTree.el.root.cascade(function() {
            if (this.getUI().isChecked()) {
                data[this.id] = this.id;
            }
        });

        return {
            'category': data
        };
    },

    onCheckChange: function(node, checked) {
        if (isNaN(node.id)) {
            return;
        }
        if (checked) {
            this.getSelectionModel().select(node, null, true);
        } else {
            node.unselect();
        }
    },

    onClick: function(node, e) {
        if (isNaN(node.id)) {
            return;
        }
        if (node.isSelected()) {
            node.unselect();
            node.checked = false;
            node.attributes.checked = false;
            node.ui.toggleCheck(false);
        } else {
            this.getSelectionModel().select(node, e, true);
            node.checked = true;
            node.attributes.checked = true;
            node.ui.toggleCheck(true);
        }
    },

    reload: function() {
        PageCategoryTree.el.getLoader().load(PageCategoryTree.el.root);
    }
};

Ext.onReady(function() {

    var root = new Ext.tree.AsyncTreeNode({
        text: 'Axis root node'.l(),
        id: 'rootNode'
    });

    PageCategoryTree.el = new Ext.tree.TreePanel({
        title: 'Categories'.l(),
        lines: true,
        animate: false,
        enableDD: false,
        selModel: new Ext.tree.MultiSelectionModel(),
        containerScroll: true,
        root: root,
        layout: 'fit',
        rootVisible: false,
        autoScroll: true,
        loader: new Ext.tree.TreeLoader({
            url: Axis.getUrl('cms/page/get-site-tree'),
            baseAttrs: {
                checked: false,
                expanded: true
            }
        }),
        listeners: {
            'checkchange': PageCategoryTree.onCheckChange,
            'click': PageCategoryTree.onClick
        },
        tbar: ['->', {
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: function() {
                PageCategoryTree.reload();
            }
        }]
    });

    PageWindow.addTab(PageCategoryTree.el, 30);
    PageWindow.dataObjects.push(PageCategoryTree);
});
