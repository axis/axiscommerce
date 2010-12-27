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

var BlockWindow = {

    el: null,

    form: null,

    save: function() {
        BlockWindow.form.getForm().submit({
            url: Axis.getUrl('cms_block/save'),
            success: function() {
                BlockWindow.el.hide();
                BlockGrid.reload();
            }
        });
    }
};

Ext.onReady(function(){

    BlockWindow.form = new Ext.form.FormPanel({
        labelWidth: 80,
        autoScroll: true,
        border: false,
        labelAlign: 'top',
        bodyStyle: 'padding: 5px 5px 0px 5px',
        reader: new Ext.data.JsonReader({
            root: 'data'
        }, [
            {name: 'id', type: 'int'},
            {name: 'name'},
            {name: 'is_active', type: 'int'},
            {name: 'content'}
        ]),
        items: [{
            layout: 'column',
            border: false,
            bodyStyle: 'padding: 5px 0px 0px',
            items: [{
                columnWidth: '.5',
                layout: 'form',
                border: false,
                items: [{
                    xtype: 'textfield',
                    fieldLabel: 'Name'.l(),
                    name: 'name',
                    allowBlank: false,
                    maxLength: 45,
                    anchor: '-20'
                }]
            }, {
                columnWidth: '.5',
                layout: 'form',
                border: false,
                items: [{
                    fieldLabel: 'Status'.l(),
                    xtype: 'combo',
                    name: 'is_active',
                    emptyText: 'Select status'.l(),
                    hiddenName: 'is_active',
                    store: new Ext.data.SimpleStore({
                        fields: ['id', 'value'],
                        data: [['0', 'Disabled'.l()], ['1', 'Enabled'.l()]]
                    }),
                    initialValue: 1,
                    displayField: 'value',
                    valueField: 'id',
                    mode: 'local',
                    editable: false,
                    triggerAction: 'all',
                    anchor: '-5',
                    maxLength: 45
                }]
            }]
        }, {
            name: 'content',
            fieldLabel: 'Content'.l(),
            anchor: '99%',
            height: 300,
            xtype: 'textarea'
        }, {
            name: 'id',
            initialValue: 0,
            xtype: 'hidden'
        }]
    });

    BlockWindow.el = new Axis.Window({
        width: 640,
        height: 500,
        maximizable: true,
        title: 'Static Block'.l(),
        items: [BlockWindow.form],
        buttons: [{
            icon: Axis.skinUrl + '/images/icons/database_save.png',
            text: 'Save'.l(),
            handler: BlockWindow.save
        }, {
            icon: Axis.skinUrl + '/images/icons/cancel.png',
            text: 'Cancel'.l(),
            handler: function() {
                BlockWindow.el.hide();
            }
        }]
     });
});
