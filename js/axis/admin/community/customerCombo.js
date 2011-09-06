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

    Ext.QuickTips.init();

    Ext.form.Field.prototype.msgTarget = 'qtip';

    var customer_object = Ext.data.Record.create([
        {name: 'id', type: 'int'},
        {name: 'email', type: 'string'}
    ]);

    var customer_store = new Ext.data.Store({
        url: Axis.getUrl('account/customer/list'),
        reader: new Ext.data.JsonReader({
            id: 'id',
            totalProperty: 'count',
            root: 'data'
        }, customer_object),
        autoLoad: false
    })

    var customer_combo = new Ext.form.ComboBox({
        store: customer_store,
        allowBlank: true,
        fieldLabel: 'Customer'.l(),
        name: 'customer_id',
        hiddenName: 'customer_id',
        triggerAction: 'all',
        emptyText: 'Guest'.l(),
        id: 'customer_combo',
        displayField: 'email',
        valueField: 'id',
        typeAhead: false,
        loadingText: 'Loading...'.l(),
        pageSize: 40,
        listWidth: Ext.isIE ? '' : '455',
        resizable: true,
        minChars: 3
    })

})