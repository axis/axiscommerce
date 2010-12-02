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

var Attribute = {

    grid: null,

    window: null,

    form: null,

    add: function () {
        Attribute.form.getForm().clear();
        Attribute.window.setTitle('New Attribute'.l());
        Attribute.window.show();
    },

    load: function(id) {
        Attribute.form.getForm().load({
            url: Axis.getUrl('catalog_product-attributes/get-data/id/' + id),
            method: 'get',
            success: function(form, action) {
                var data = Ext.decode(action.response.responseText).data;
                Attribute.window.setTitle(data.text['lang_' + Axis.language].name);
                Attribute.window.show();
            }
        });
    }
};

Ext.onReady(function() {
    Attribute.inputTypeStore = new Ext.data.Store({
        data: [
            [0, 'Select'],
            [1, 'String'],
            [2, 'Radio'],
            [3, 'Checkbox'],
            [4, 'Textarea'],
            [5, 'File']
        ],
        reader: new Ext.data.ArrayReader({
            idIndex: 0
        }, [
            {name: 'id', type: 'int'},
            {name: 'title'}
        ])
    });
});