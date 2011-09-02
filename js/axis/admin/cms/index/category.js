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

var Category = {

    id: null,

    add: function(parentId, siteId) {
        Category.id = null;
        CategoryWindow.el.setTitle('New Сategory'.l());
        CategoryWindow.form.getForm().clear();
        CategoryWindow.form.getForm().setValues({
            'parent_id' : parentId,
            'site_id'   : siteId
        });
        CategoryWindow.show();
    },

    /**
     * Load data of recieved product id, and open productWindow on success
     *
     * @param {int} id
     */
    load: function(id) {
        Category.id = id;
        CategoryWindow.form.getForm().load({
            url: Axis.getUrl('cms/category/load'),
            params: {
                id: id
            },
            success: function(form, action) {
                var data = Ext.decode(action.response.responseText).data;
                CategoryWindow.el.setTitle(data.name);
                CategoryWindow.show();
            },
            failure: function() {
                console.log(arguments);
            }
        });
    }
};
