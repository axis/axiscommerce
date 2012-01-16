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

var Group = {

    id: null,

    add: function() {
        Group.id = null;
        GroupWindow.el.setTitle('Group'.l());
        GroupWindow.form.getForm().clear();
        GroupWindow.show();
    },

    /**
     * Load data of recieved fieldgroup id, and open GroupWindow on success
     *
     * @param {int} id
     */
    load: function(id) {
        if (isNaN(id)) {
            return;
        }
        Group.id = id;
        GroupWindow.form.getForm().load({
            url: Axis.getUrl('account/field-group/load'),
            params: {
                id: id
            },
            success: function(form, action) {
                var data = Ext.decode(action.response.responseText).data;
                GroupWindow.el.setTitle(data.group.name);
                GroupWindow.show();
            },
            failure: function() {
                console.log(arguments);
            }
        });
    }
};
