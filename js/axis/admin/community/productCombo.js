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
    
    var product_object = Ext.data.Record.create([
        {name: 'id', type: 'int'},
        {name: 'name', type: 'string'}
    ]);
    
    var product_store = new Ext.data.Store({
        url: Axis.getUrl('catalog_index/simple-list'),
        reader: new Ext.data.JsonReader({
            id: 'product_id',
            totalProperty: 'totalCount',
            root: 'data'
        }, product_object),
        autoLoad: false
    })
    
    var product_combo = new Ext.form.ComboBox({
        store: product_store,
        allowBlank: false,
        fieldLabel: 'Product'.l(),
        name: 'product_id',
        hiddenName: 'product_id',
        triggerAction: 'all',
        emptyText: 'Select product'.l(),
        id: 'product_combo',
        displayField: 'name',
        valueField: 'id',
        typeAhead: false,
        loadingText: 'Loading...'.l(),
        pageSize: 40,
        listWidth: Ext.isIE ? '' : 'auto',
        resizable: true,
        minChars: 3
    })
    
})