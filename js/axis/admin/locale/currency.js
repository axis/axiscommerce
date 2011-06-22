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

Ext.onReady(function() {

    var Item = {

        record: Ext.data.Record.create([
            {name: 'id', type: 'int'},
            {name: 'code', type: 'string'},
            {name: 'title', type: 'string'},
            {name: 'position', type: 'string'},
            {name: 'display', type: 'string'},
            {name: 'format', type: 'string'},
            {name: 'currency_precision', type: 'int'},
            {name: 'rate', type: 'float'}
        ]),

        create: function() {
            window.show();
            Ext.getCmp('form_currency').getForm().clear();
        },

        edit: function(row) {
            window.show();
            Ext.getCmp('form_currency').getForm().setValues({
                'currency[id]': row.id,
                'currency[title]': row.get('title'),
                'currency[code]': row.get('code'),
                'currency[position]': row.get('position'),
                'currency[display]': row.get('display'),
                'currency[format]': row.get('format'),
                'currency[currency_precision]': row.get('currency_precision'),
                'currency[rate]': row.get('rate')
            });
        },

        save: function() {
            Ext.getCmp('form_currency').getForm().submit({
                url: Axis.getUrl('locale_currency/save'),
                success: function(form, response) {
                    form.clear();
                    window.hide();
                    ds.reload();
                }
            })
        },

        batchSave: function() {
            var modified = ds.getModifiedRecords();

            if (!modified.length)
                return;

            var data = {};

            for (var i = 0; i < modified.length; i++) {
                data[modified[i]['id']] = modified[i]['data'];
            }
            var jsonData = Ext.encode(data);
            Ext.Ajax.request({
                url: Axis.getUrl('locale_currency/batch-save'),
                params: {data: jsonData},
                callback: function() {
                    ds.reload();
                }
            });
        },

        remove: function() {
            var selectedItems = grid.getSelectionModel().selections.items;

            if (!selectedItems.length)
                return;

            if (!confirm('Delete currency?'))
                return;

            var data = {};

            for (var i = 0; i < selectedItems.length; i++) {
                data[i] = selectedItems[i].id;
            }
            var jsonData = Ext.encode(data);
            Ext.Ajax.request({
                url: Axis.getUrl('locale_currency/delete'),
                params: {data: jsonData},
                callback: function() {
                    ds.reload();
                }
            });
        }
    }

    var ds = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('locale_currency/list')
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Item.record),
        pruneModifiedRecords: true
    });
    
    var cm = new Ext.grid.ColumnModel([{
        header: "Id".l(),
        dataIndex: 'id',
        width: 60
    }, {
        header: "Code".l(),
        dataIndex: 'code',
        width: 60
    }, {
        id: 'title',
        header: "Title".l(),
        dataIndex: 'title',
        width: 60,
        editor: new Ext.form.TextField({
           allowBlank: false
        })
    }, {
        header: "Position".l(),
        dataIndex: 'position',
        width: 100,
        editor: new Ext.form.ComboBox({
           typeAhead: true,
           triggerAction: 'all',
           transform: 'currency-position',
           lazyRender: true
        }),
        renderer: renderPosition
    }, {
        header: "Display".l(),
        dataIndex: 'display',
        width: 100,
        editor: new Ext.form.ComboBox({
            typeAhead: true,
            triggerAction: 'all',
            transform: 'currency-display',
            lazyRender: true
        }),
        renderer: renderDisplay
    }, {
        header: "Format".l(),
        dataIndex: 'format',
        width: 180,
        editor: new Ext.form.ComboBox({
            typeAhead: true,
            triggerAction: 'all',
            transform: 'format',
            lazyRender: true,
            allowBlank: false
        }),
        renderer : renderLocale
    }, {
        header: "Precision".l(),
        dataIndex: 'currency_precision',
        width: 60,
        editor: new Ext.form.TextField({
            allowBlank: false
        })
    }, {
        header: "Rate".l(),
        dataIndex: 'rate',
        width: 60,
        editor: new Ext.form.TextField({
            allowBlank: false
        })
    }]);
    cm.defaultSortable = true;

    var grid = new Axis.grid.EditorGridPanel({
        ds: ds,
        cm: cm,
        autoExpandColumn: 'title',
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
            handler : Item.create
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/accept.png',
            cls: 'x-btn-text-icon',
            handler : Item.batchSave
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler : Item.remove
        }, '->', {
            text: 'Reload'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: function() {
                grid.getStore().reload();
            }
        }]
    });

    new Axis.Panel({
        items: [
            grid
        ]
    });

    grid.on('rowdblclick', function(grid, rowIndex, e){
        Item.edit(grid.getStore().getAt(rowIndex));
    })

    ds.load();

    var zendCurrencies = new Ext.form.ComboBox({
        transform: 'currency',
        triggerAction: 'all',
        name: 'currency[code]',
        hiddenName: 'currency[code]',
        lazyRender: true,
        fieldLabel: 'Currency'.l(),
        value: 'USD',
        allowBlank: false
    });

    var zendLocales = new Ext.form.ComboBox({
        transform: 'locale',
        triggerAction: 'all',
        name: 'currency[format]',
        hiddenName: 'currency[format]',
        lazyRender: true,
        fieldLabel: 'Format'.l(),
        value: 'en_US',
        allowBlank: false
    });

    var form = new Ext.form.FormPanel({
        border: false,
        labelAlign: 'left',
        labelWidth: 80,
        id: 'form_currency',
        bodyStyle: 'padding: 10px;',
        defaults: {
            anchor: '100%',
            border: false
        },
        items: [{
            xtype: 'textfield',
            fieldLabel: 'Title'.l(),
            name: 'currency[title]',
            allowBlank: false,
            maxLength: 45
        }, {
            layout: 'column',
            defaults: {
                border: false
            },
            items: [{
                columnWidth: 0.5,
                layout: 'form',
                defaults: {
                    anchor: '98%'
                },
                items: [
                    zendCurrencies,
                    zendLocales, {
                    xtype: 'combo',
                    name: 'currency[display]',
                    hiddenName: 'currency[display]',
                    fieldLabel: 'Display'.l(),
                    store: new Ext.data.SimpleStore({
                        data: [
                            ['1', 'No Symbol'.l()],
                            ['2', 'Use Symbol'.l()],
                            ['3', 'Use Shortname'.l()],
                            ['4', 'Use Name'.l()]
                        ],
                        fields: ['id', 'title']
                    }),
                    displayField: 'title',
                    valueField: 'id',
                    initialValue: 2,
                    mode: 'local',
                    triggerAction: 'all'
                }]
            }, {
                columnWidth: 0.5,
                layout: 'form',
                defaults: {
                    anchor: '100%'
                },
                items: [{
                    xtype: 'textfield',
                    fieldLabel: 'Precision'.l(),
                    name: 'currency[currency_precision]',
                    initialValue: 2,
                    allowBlank: true,
                    maxLength: 2
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Rate'.l(),
                    name: 'currency[rate]',
                    initialValue: 1,
                    allowBlank: true,
                    maxLength: 8
                }, {
                    xtype: 'combo',
                    name: 'currency[position]',
                    hiddenName: 'currency[position]',
                    fieldLabel: 'Position'.l(),
                    store: new Ext.data.SimpleStore({
                        data: [
                            ['8', 'Standard'.l()],
                            ['16', 'Right'.l()],
                            ['32', 'Left'.l()]
                        ],
                        fields: ['id', 'title']
                    }),
                    displayField: 'title',
                    valueField: 'id',
                    initialValue: 8,
                    mode: 'local',
                    triggerAction: 'all'
                }]
            }]
        }, {
            xtype: 'hidden',
            name: 'currency[id]',
            initialValue: ''
        }]
    })

    var window = new Ext.Window({
        id: 'window_currency',
        title: 'Currency'.l(),
        items: form,
        closeAction: 'hide',
        resizable: true,
        maximizable: true,
        constrainHeader: true,
        autoScroll: true,
        bodyStyle: 'background: white;',
        width: 500,
        height: 220,
        minWidth: 260,
        buttons: [{
            text: 'Save'.l(),
            handler: Item.save
        }, {
            text: 'Close'.l(),
            handler: function() {
                window.hide();
            }
        }]
    })
});