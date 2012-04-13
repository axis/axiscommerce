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

var RootCategoryWindow = {

    el: null,

    form: null,

    close: function() {
        RootCategoryWindow.hide();
    },

    hide: function() {
        RootCategoryWindow.el.hide();
    },

    save: function(closeWindow) {
        RootCategoryWindow.form.getForm().submit({
            url: Axis.getUrl('catalog/category/save-root'),
            method: 'post',
            success: function(form, response) {
                CategoryGrid.reload();
                if (closeWindow) {
                    RootCategoryWindow.hide();
                    RootCategoryWindow.form.getForm().clear();
                } else {
                    var response = Ext.decode(response.response.responseText);
                    Category.load(response.data.category_id);
                }
            },
            failure: function(form, response) {
                if (response.failureType == 'client') {
                    return;
                }
            }
        });
    },

    show: function() {
        RootCategoryWindow.el.show();
    }

};

Ext.onReady(function() {

    Ext.form.Field.prototype.msgTarget = 'qtip';

    var reader = [
        {name: 'status'},
        {name: 'id', type: 'int'},
        {name: 'site_id', type: 'int'}
    ];

    RootCategoryWindow.form = new Axis.FormPanel({
        id: 'form-root-category',
        bodyStyle: {
            padding: '7px 10px 0'
        },
        reader: new Ext.data.JsonReader({
                root: 'data'
            }, reader
        ),
        defaults: {
            allowBlank: false,
            anchor: '100%',
        },
        items: [{
            editable: false,
            fieldLabel: 'Site'.l(),
            hiddenName: 'site_id',
            name: 'site_id',
            triggerAction: 'all',
            valueField: 'id',
            displayField: 'name',
            store: new Ext.data.JsonStore({
                url: Axis.getUrl('core/site/list'),
                root: 'data',
                fields: [
                    {name: 'name'},
                    {name: 'id', type: 'int'},
                    {name: 'root_category', type: 'int'}
                ],
                listeners: {
                    load: function(store, records) {
                        store.filterBy(function(r) {
                            return r.get('root_category') == 0;
                        });
                    }
                }
            }),
            lastQuery: '',
            listeners: {
                beforequery: function(qe) {
                    delete qe.combo.lastQuery;
                }
            },
            xtype: 'combo'
        }]
    });

    RootCategoryWindow.el = new Axis.Window({
        id: 'window-root-category',
        items: [RootCategoryWindow.form],
        title: 'Category'.l(),
        width: 350,
        height: 160,
        buttons: [{
            icon: Axis.skinUrl + '/images/icons/database_save.png',
            text: 'Save'.l(),
            handler: function() {
                RootCategoryWindow.save(true);
            }
        }, {
            icon: Axis.skinUrl + '/images/icons/cancel.png',
            text: 'Cancel'.l(),
            handler: RootCategoryWindow.hide
        }]
    });
});
