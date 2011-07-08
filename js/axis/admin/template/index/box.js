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

var Box = {

    add: function() {
        Box.Window.el.setTitle('New box'.l());
        Box.Window.form.getForm().clear();
        Box.Window.clearData();
        Box.Window.show();
    },

    load: function(id) {
        Box.Window.form.getForm().load({
            url: Axis.getUrl('template_box/edit/id/' + id),
            method: 'get',
            success: function(form, action) {
                var data = Ext.decode(action.response.responseText).data;
                Box.Window.el.setTitle(data['class']);
                Box.Window.loadData(data);
                Box.Window.show();
            }
        });
    }
};
