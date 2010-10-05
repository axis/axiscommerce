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
    
    Customer.siteCombo = new Ext.form.ComboBox({
        fieldLabel: 'Site'.l(),
        displayField: 'name',
        valueField: 'id',
        editable: false,
        mode: 'local',
        name: 'site_id',
        hiddenName: 'site_id',
        store: new Ext.data.ArrayStore({
            storeId: 'store-site-id',
            idIndex: 0,
            data: sites, // index.phtml
            fields: [
                {name: 'id', type: 'int'}, 
                {name: 'name'}
            ]
        }),
        triggerAction: 'all'
    });
    
    Customer.groupCombo = new Ext.form.ComboBox({
        fieldLabel: 'Group'.l(),
        displayField: 'name',
        valueField: 'id',
        editable: false,
        mode: 'local',
        name: 'group_id',
        hiddenName: 'group_id',
        store: new Ext.data.ArrayStore({
            storeId: 'store-customer-group',
            idIndex: 0,
            data: customerGroups, // index.phtml
            fields: [
                {name: 'id', type: 'int'}, 
                {name: 'name'}
            ]
        }),
        triggerAction: 'all'
    });
    
});
