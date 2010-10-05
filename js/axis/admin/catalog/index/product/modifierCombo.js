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

Ext.onReady(function() {
    
    ProductWindow.modifierType = new Ext.form.ComboBox({
        displayField: 'title',
        editable: false,
        mode: 'local',
        triggerAction: 'all',
        valueField: 'value',
        store: new Ext.data.ArrayStore({
            data: [
                ['by', 'Fixed Amount'.l()],
                ['to', 'Fixed Value'.l()],
                ['percent', 'Percent'.l()]
            ],
            fields: ['value', 'title'],
            idIndex: 0,
            storeId: 'store-modifier-type' 
        })
    });
    
    ProductWindow.modifierRenderer = function(value) {
        var record = Ext.StoreMgr.lookup('store-modifier-type').getById(value);
        if (record) {
            return record.get('title');
        }
        return value;
    };
    
});
