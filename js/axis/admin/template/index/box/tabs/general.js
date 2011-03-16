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

    Box.Window.formFields.push(
        {name: 'box[class]', mapping: 'class'},
        {name: 'box[block]', mapping: 'block'},
        {name: 'box[sort_order]', mapping: 'sort_order'},
        {name: 'box[box_status]', mapping: 'box_status'},
        {name: 'box[config]', mapping: 'config'},
        {name: 'box[id]', mapping: 'id', type: 'int'},
        {name: 'box[template_id]', mapping: 'template_id', type: 'int'}
    );

    Box.Window.addTab({
        title: 'General'.l(),
        bodyStyle: 'padding: 10px',
        defaults: {
            anchor: '100%',
            border: false
        },
        items: [{
            allowBlank: false,
            editable: false,
            fieldLabel: 'Class'.l(),
            typeAhead: true,
            triggerAction: 'all',
            lazyRender: true,
            name: 'box[class]',
            hiddenName: 'box[class]',
            store: Axis.boxClasses,
            mode: 'local',
            xtype: 'combo'
        }, {
            allowBlank: false,
            name: 'box[block]',
            fieldLabel: 'Output Container'.l(),
            xtype: 'textfield'
        }, {
            allowBlank: false,
            name: 'box[sort_order]',
            fieldLabel: 'Sort Order'.l(),
            initialValue: 70,
            xtype: 'textfield'
        }, {
            allowBlank: false,
            columns: [100, 100],
            fieldLabel: 'Status'.l(),
            xtype: 'radiogroup',
            value: 1,
            initialValue: 1,
            items: [{
                boxLabel: 'Enabled'.l(),
                checked: true,
                name: 'box[box_status]',
                inputValue: 1
            }, {
                boxLabel: 'Disabled'.l(),
                name: 'box[box_status]',
                inputValue: 0
            }]
        }, {
            allowBlank: true,
            name: 'box[config]',
            fieldLabel: 'Configuration'.l(),
            xtype: 'textfield'
        }, {
            allowBlank: true,
            initialValue: 0,
            name: 'box[id]',
            xtype: 'hidden'
        }, {
            allowBlank: false,
            name: 'box[template_id]',
            xtype: 'hidden'
        }]
    }, 10);
});
