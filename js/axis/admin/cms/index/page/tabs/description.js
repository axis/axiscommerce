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

    PageWindow.formFields.push(
        {name: 'id', type: 'int'},
        {name: 'name'},
        {name: 'is_active', type: 'int'},
        {name: 'layout'},
        {name: 'comment', type: 'int'}
    );

    for (var id in Axis.locales) {
        PageWindow.formFields.push({
            name: 'content[' + id + '][title]',
            mapping: 'content.lang_' + id + '.title'
        }, {
            name: 'content[' + id + '][content]',
            mapping: 'content.lang_' + id + '.content'
        });
    }

    PageWindow.addTab({
        title: 'Description'.l(),
        bodyStyle: 'padding: 10px',
        defaults: {
            anchor: '-20',
            border: false
        },
        items: [{
            layout: 'column',
            border: false,
            anchor: '100%',
            bodyStyle: 'padding: 5px 0px 0px',
            items: [{
                columnWidth: 0.5,
                layout: 'form',
                border: false,
                items: [{
                    fieldLabel: 'Name'.l(),
                    xtype: 'textfield',
                    name: 'name',
                    allowBlank: false,
                    anchor: '-10'
                }]
            }, {
                columnWidth: 0.5,
                layout: 'form',
                border: false,
                items: [{
                    fieldLabel: 'Status'.l(),
                    xtype: 'combo',
                    name: 'is_active',
                    hiddenName: 'is_active',
                    store: new Ext.data.SimpleStore({
                        fields: ['id', 'value'],
                        data: [['0', 'Disabled'.l()], ['1', 'Enabled'.l()]]
                    }),
                    displayField: 'value',
                    valueField: 'id',
                    mode: 'local',
                    editable: false,
                    initialValue: 1,
                    triggerAction: 'all',
                    anchor: '100%'
                }, {
                    fieldLabel: 'Layout'.l(),
                    xtype: 'combo',
                    name: 'layout',
                    hiddenName: 'layout',
                    store: Page.layoutStore,
                    displayField: 'name',
                    valueField: 'id',
                    mode: 'local',
                    editable: false,
                    emptyText: 'Select layout'.l(),
                    triggerAction: 'all',
                    anchor: '100%'
                }, {
                    fieldLabel: 'Comments'.l(),
                    xtype: 'combo',
                    name: 'comment',
                    editable: false,
                    hiddenName: 'comment',
                    store: new Ext.data.SimpleStore({
                        fields: ['id', 'value'],
                        data: [['0', 'Disabled'.l()], ['1', 'Enabled'.l()]]
                    }),
                    displayField: 'value',
                    valueField: 'id',
                    mode: 'local',
                    initialValue: 0,
                    triggerAction: 'all',
                    anchor: '100%'
                }]
            }]
        }, {
            fieldLabel: 'Title'.l(),
            name: 'content[title]',
            xtype: 'langset'
        }, {
            defaultType: 'textarea',
            height: 250,
            fieldLabel: 'Content'.l(),
            name: 'content[content]',
            xtype: 'langset'
        }, {
            xtype: 'hidden',
            name: 'id',
            initiaValue: 0
        }]
    }, 10);
});
