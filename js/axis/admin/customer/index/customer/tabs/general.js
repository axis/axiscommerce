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

    CustomerWindow.formFields.push(
        {name: 'customer[id]', mapping: 'customer.id', type:'int'},
        {name: 'customer[firstname]', mapping: 'customer.firstname'},
        {name: 'customer[lastname]', mapping: 'customer.lastname'},
        {name: 'customer[email]', mapping: 'customer.email'},
        {name: 'customer[password]', mapping: 'customer.dummy_field'},
        {name: 'customer[site_id]', mapping: 'customer.site_id'},
        {name: 'customer[group_id]', mapping: 'customer.group_id'},
        {name: 'customer[is_active]', mapping: 'customer.is_active', type: 'int'},
        {name: 'customer[created_at]', mapping: 'customer.created_at', type: 'date', dateFormat: 'Y-m-d'},
        {name: 'customer[modified_at]', mapping: 'customer.modified_at', type: 'date', dateFormat: 'Y-m-d'}
    );

    CustomerWindow.addTab({
        title: 'General'.l(),
        bodyStyle: 'padding: 10px',
        defaults: {
            allowBlank: false,
            anchor: '-20',
            border: false,
            xtype: 'textfield'
        },
        items: [{
            allowBlank: false,
            anchor: '100%',
            name: 'customer[id]',
            xtype: 'hidden'
        }, {
            fieldLabel: 'Firstname'.l(),
            name: 'customer[firstname]'
        }, {
            fieldLabel: 'Lastname'.l(),
            name: 'customer[lastname]'
        }, {
            fieldLabel: 'Email'.l(),
            name: 'customer[email]',
            vtype: 'email'
        }, {
            allowBlank: true,
            fieldLabel: 'Password'.l(),
            name: 'customer[password]',
            inputType: 'password'
        }, {
            columns: [100, 100],
            fieldLabel: 'Status'.l(),
            name: 'customer[is_active]',
            xtype: 'radiogroup',
            value: 1,
            initialValue: 1,
            items: [{
                boxLabel: 'Enabled'.l(),
                checked: true,
                name: 'customer[is_active]',
                inputValue: 1
            }, {
                boxLabel: 'Disabled'.l(),
                name: 'customer[is_active]',
                inputValue: 0
            }]
        },
        Customer.groupCombo.cloneConfig({
            allowBlank: false,
            name: 'customer[group_id]',
            hiddenName: 'customer[group_id]'
        }),
        Customer.siteCombo.cloneConfig({
            allowBlank: false,
            name: 'customer[site_id]',
            hiddenName: 'customer[site_id]'
        })
        ]
    }, 10);

});
