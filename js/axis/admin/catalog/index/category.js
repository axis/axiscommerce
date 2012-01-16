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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

var Category = {
    
    /**
     * Opens categoryWindow
     * 
     * @param {int} parentId
     */
    add: function(parentId, siteId) {
        if (!parentId || !siteId) {
            var parent = CategoryGrid.el.selModel.getSelected();
            if (!parent || !parent.get('id') || !parent.get('site_id')) {
                alert('Select parent category or site, on the left panel'.l());
                return false;
            }
            parentId = parent.get('id');
            siteId = parent.get('site_id');
        }
        
        CategoryWindow.el.setTitle('Category'.l());
        CategoryWindow.form.getForm().clear();
        CategoryWindow.form.getForm().setValues({
            'site_id': siteId,
            'parent_id': parentId
        });
        CategoryWindow.show();
    },
    
    /**
     * Load data of recieved category id, and open categoryWindow on success
     * 
     * @param {int} id
     */
    load: function(id) {
        CategoryWindow.form.getForm().load({
            url: Axis.getUrl('catalog/category/load/categoryId/' + id),
            method: 'get',
            success: function(form, response) {
                var response = Ext.decode(response.response.responseText);
                CategoryWindow.el.setTitle(response.data['name_' + Axis.language]);
                CategoryWindow.show();
            }
        });
    }
    
};
