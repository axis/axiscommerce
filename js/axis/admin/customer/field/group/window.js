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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

var GroupWindow = {

    el: null,

    form: null,

    save: function(closeWindow) {
        GroupWindow.form.getForm().submit({
            url     : Axis.getUrl('account/field-group/save'),
            success : function(form, action) {
                GroupGrid.reload();
                if (closeWindow) {
                    GroupWindow.hide();
                    GroupWindow.form.getForm().clear();
                } else {
                    var response = Ext.decode(action.response.responseText);
                    Group.load(response.data.id);
                }
            },
            failure: function(form, action) {
                if (action.failureType == 'client') {
                    return;
                }
            }
        });
    },

    show: function() {
        GroupWindow.el.show();
    },

    hide: function() {
        GroupWindow.el.hide();
    }

};

Ext.onReady(function() {

    var fields = [
        {name: 'group[id]',         type: 'int',    mapping: 'group.id'},
        {name: 'group[name]',                       mapping: 'group.name'},
        {name: 'group[is_active]',  type: 'int',    mapping: 'group.is_active'},
        {name: 'group[sort_order]', type: 'int',    mapping: 'group.sort_order'}
    ];
    for (var id in Axis.locales) {
        fields.push({
            name    : 'label[' + id + ']',
            mapping : 'label.lang_' + id
        });
    }

    GroupWindow.form = new Axis.form.FormPanel({
        bodyStyle   : 'padding: 10px 10px 0',
        method      : 'post',
        reader      : new Ext.data.JsonReader({
            root        : 'data',
            idProperty  : 'group.id'
        }, fields),
        defaults: {
            anchor: '-20'
        },
        items: [{
            allowBlank  : false,
            fieldLabel  : 'Name'.l(),
            xtype       : 'textfield',
            name        : 'group[name]'
        }, {
            allowBlank  : false,
            name        : 'label',
            tpl         : '{self}[{language_id}]',
            fieldLabel  : 'Title'.l(),
            xtype       : 'langset'
        }, {
            allowBlank  : false,
            columns     : [100, 100],
            fieldLabel  : 'Status'.l(),
            name        : 'group[is_active]',
            xtype       : 'radiogroup',
            initialValue: 1,
            items: [{
                boxLabel    : 'Enabled'.l(),
                checked     : true,
                name        : 'group[is_active]',
                inputValue  : 1
            }, {
                boxLabel    : 'Disabled'.l(),
                name        : 'group[is_active]',
                inputValue  : 0
            }]
        }, {
            allowBlank  : false,
            name        : 'group[sort_order]',
            fieldLabel  : 'Sort Order'.l(),
            xtype       : 'numberfield',
            initialValue: 20
        }, {
            xtype       : 'hidden',
            name        : 'group[id]'
        }]
    });

    GroupWindow.el = new Axis.Window({
        width   : 450,
        height  : 230,
        items   : GroupWindow.form,
        title   : 'Group'.l(),
        buttons : [{
            icon    : Axis.skinUrl + '/images/icons/database_save.png',
            text    : 'Save'.l(),
            handler : function() {
                GroupWindow.save(true);
            }
        }, {
            icon    : Axis.skinUrl + '/images/icons/database_save.png',
            text    : 'Save & Continue Edit'.l(),
            handler : function() {
                GroupWindow.save(false);
            }
        }, {
            icon    : Axis.skinUrl + '/images/icons/cancel.png',
            text    : 'Cancel'.l(),
            handler : GroupWindow.hide
        }]
    });
});
