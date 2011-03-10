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

var Page = {

    id: null,

    layoutStore: null,

    add: function() {
        Page.id = null;
        PageWindow.el.setTitle('New Page'.l());
        PageWindow.form.getForm().clear();
        PageWindow.clearData();
        PageWindow.show();
    },

    /**
     * Load data of recieved product id, and open productWindow on success
     *
     * @param {int} id
     */
    load: function(id) {
        Page.id = id;
        PageWindow.form.getForm().load({
            url: Axis.getUrl('cms_index/get-page-data/id/' + id),
            success: function(form, action) {
                var data = Ext.decode(action.response.responseText).data;
                PageWindow.el.setTitle(data.name);
                PageWindow.loadData(data);
                PageWindow.show();
            },
            failure: function() {
                console.log(arguments);
            }
        });
    }
};

Ext.onReady(function() {

    Page.layoutStore = new Ext.data.Store({
        url: Axis.getUrl('template_layout/list'),
        reader: new Ext.data.JsonReader({
            root: 'data'
        }, ['id', 'name']),
        autoLoad: true
    });

});
