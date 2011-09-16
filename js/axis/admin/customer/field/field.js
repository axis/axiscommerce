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

var Field = {

    add: function() {
        FieldWindow.el.setTitle('Field'.l());
        FieldWindow.form.getForm().clear();

        // select default fieldgroup
        var currentGroupId = FieldGrid.el.getStore().baseParams['filter[fieldgroup][value]'];
        if (!currentGroupId) {
            currentGroup = GroupGrid.el.getStore().getAt(0);
            if (currentGroup) {
                currentGroupId = currentGroup.get('id');
            }
        }
        if (!currentGroupId) {
            return alert('You need to create a fieldgroup before adding fields'.l());
        }

        FieldWindow.form.getForm()
            .findField('field[customer_field_group_id]')
            .setValue(currentGroupId);
        FieldWindow.show();
    },

    /**
     * Load data of recieved product id, and open productWindow on success
     *
     * @param {int} id
     */
    load: function(id) {
        FieldWindow.form.getForm().load({
            url     : Axis.getUrl('account/field/load/id/' + id),
            method  : 'get',
            success : function(form, action) {
                var data = Ext.decode(action.response.responseText).data;
                FieldWindow.el.setTitle(data.field.name);
                FieldWindow.show();
            },
            failure: function() {
                console.log(arguments);
            }
        });
    }
};
