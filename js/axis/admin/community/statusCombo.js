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

Ext.onReady(function(){
    
    var status_object = Ext.data.Record.create([
        {name: 'id', type: 'int', mapping: 0},
        {name: 'value', type: 'string', mapping: 1}
    ]);
    
    //@todo Axis_Community_Model_Option_Review_Status
    var status_store = new Ext.data.SimpleStore({
        data: [['pending', 'Pending'.l()],['approved', 'Approved'.l()],['disapproved', 'Disapproved'.l()]],
        fields: ['id', 'name']
    })
    
    var status_combo = new Ext.form.ComboBox({
        allowBlank: false,
        store: status_store,
        fieldLabel: 'Status'.l(),
        name: 'status',
        hiddenName: 'status',
        triggerAction: 'all',
        id: 'status_combo',
        displayField: 'name',
        valueField: 'id',
        mode: 'local',
        value: 'approved',
        initialValue: 'approved',
        editable: false
    })
    
})