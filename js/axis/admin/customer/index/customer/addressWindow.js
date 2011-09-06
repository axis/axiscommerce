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

Axis.AddressWindow = Ext.extend(Ext.util.Observable, {

    constructor: function(config) {
        Ext.apply(this, config);

        this.events = {
            /**
             * @event cancel
             * Fires when cancel button was pressed
             */
            'cancelpress': true,
            /**
             * @event ok
             * Fires when ok button was pressed
             * @param {Array} Selected records
             */
            'okpress': true
        };
        Axis.AddressWindow.superclass.constructor.call(this);

        this.form = new Axis.FormPanel({
            bodyStyle: 'padding: 10px;',
            defaults: {
                anchor: '-20',
                border: false
            },
            items: [{
                anchor: '-20',
                layout: 'column',
                defaults: {
                    border: false,
                    columnWidth: '.5',
                    layout: 'form'
                },
                items: [{
                    name: 'id',
                    xtype: 'hidden'
                }, {
                    items: [{
                        allowBlank: false,
                        anchor: '-5',
                        fieldLabel: 'Firstname'.l(),
                        name: 'firstname',
                        xtype: 'textfield'
                    }]
                }, {
                    items: [{
                        allowBlank: false,
                        anchor: '100%',
                        fieldLabel: 'Lastname'.l(),
                        name: 'lastname',
                        xtype: 'textfield'
                    }]
                }]
            }, {
                fieldLabel: 'Company'.l(),
                allowBlank: true,
                name: 'company',
                xtype: 'textfield'
            }, {
                anchor: '-20',
                layout: 'column',
                defaults: {
                    border: false,
                    columnWidth: '.5',
                    layout: 'form'
                },
                items: [{
                    items: [{
                        allowBlank: false,
                        anchor: '-5',
                        fieldLabel: 'Phone'.l(),
                        name: 'phone',
                        xtype: 'textfield'
                    }]
                }, {
                    items: [{
                        anchor: '100%',
                        fieldLabel: 'Fax'.l(),
                        name: 'fax',
                        xtype: 'textfield'
                    }]
                }]
            }, {
                fieldLabel: 'Street'.l(),
                allowBlank: true,
                name: 'street_address',
                xtype: 'textfield'
            }, {
                anchor: '-20',
                layout: 'column',
                defaults: {
                    border: false,
                    columnWidth: '.5',
                    layout: 'form'
                },
                items: [{
                    items: [{
                        allowBlank: false,
                        anchor: '-5',
                        fieldLabel: 'City'.l(),
                        name: 'city',
                        xtype: 'textfield'
                    }]
                }, {
                    items: [{
                        allowBlank: false,
                        id: 'country-id',
                        anchor: '100%',
                        fieldLabel: 'Country'.l(),
                        displayField: 'name',
                        valueField: 'id',
                        mode: 'local',
                        name: 'country_id',
                        hiddenName: 'country_id',
                        listWidth: 200,
                        lastQuery: '',
                        initialValue: 223,
                        store: new Ext.data.JsonStore({
                            autoLoad: true,
                            baseParams: {
                                start: 0,
                                limit: 300
                            },
                            url: Axis.getUrl('location/country/list/show_allcountry/0'),
                            root: 'data',
                            idProperty: 'id',
                            fields: [
                                {name: 'id', type: 'int'},
                                {name: 'name'},
                                {name: 'iso_code_2'},
                                {name: 'iso_code_3'},
                                {name: 'address_format_id', type: 'int'}
                            ]
                        }),
                        triggerAction: 'all',
                        xtype: 'combo'
                    }]
                }]
            }, {
                anchor: '-20',
                layout: 'column',
                defaults: {
                    border: false,
                    columnWidth: '.5',
                    layout: 'form'
                },
                items: [{
                    items: [{
                        allowBlank: false,
                        anchor: '-5',
                        fieldLabel: 'Zip'.l(),
                        name: 'postcode',
                        xtype: 'textfield'
                    }]
                }, {
                    items: [{
                        anchor: '100%',
                        id: 'zone-id',
                        fieldLabel: 'State'.l(),
                        displayField: 'name',
                        valueField: 'id',
                        mode: 'local',
                        name: 'zone_id',
                        hiddenName: 'zone_id',
                        listWidth: 200,
                        lastQuery: '',
                        store: new Ext.data.JsonStore({
                            autoLoad: true,
                            baseParams: {
                                start: 0,
                                limit: 10000
                            },
                            url: Axis.getUrl('location/zone/list/show_allzones/0'),
                            root: 'data',
                            idProperty: 'id',
                            fields: [
                                {name: 'id', type: 'int'},
                                {name: 'code'},
                                {name: 'name'},
                                {name: 'country_id', type: 'int'}
                            ],
                            listeners: {
                                load: function(store, records, options) {
                                    var countryId = Ext.getCmp('country-id').getValue();
                                    var zonesCombo = Ext.getCmp('zone-id');
                                    zonesCombo.setValue(zonesCombo.getValue());
                                    store.filterBy(function(zone) {
                                        return zone.get('country_id') === countryId
                                    });
                                }
                            }
                        }),
                        triggerAction: 'all',
                        xtype: 'combo'
                    }]
                }]
            }, {
                anchor: '-20',
                layout: 'column',
                defaults: {
                    border: false,
                    columnWidth: '.5',
                    layout: 'form',
                    labelWidth: 200
                },
                items: [{
                    items: [{
                        anchor: '-5',
                        fieldLabel: 'Use as default billing address'.l(),
                        name: 'default_billing',
                        xtype: 'checkbox'
                    }]
                }, {
                    items: [{
                        anchor: '100%',
                        fieldLabel: 'Use as default shipping address'.l(),
                        name: 'default_shipping',
                        xtype: 'checkbox'
                    }]
                }]
            }]
        });


        var countryCombo = this.form.getForm().findField('country_id');
        // setValue after store load
        countryCombo.setValue = countryCombo.setValue.createInterceptor(function(v) {
            if (!Ext.isDefined(this.store.totalLength)) {
                this.store.on('load', this.setValue.createDelegate(this, arguments), null, {single: true});
                if (this.store.lastOptions === null) {
                    this.store.load();
                }
                return false;
            }
        });

        // filter zones combo after country setValue
        countryCombo.setValue = countryCombo.setValue.createSequence(function(v) {
            var zonesCombo = Ext.getCmp('zone-id');
            zonesCombo.store.filterBy(function(zone) {
                return zone.get('country_id') === v;
            });
        });

        this.window = new Axis.Window({
            title: 'Create new Address'.l(),
            width: 550,
            height: 350,
            items: [
                this.form
            ],
            buttons: [{
                icon: Axis.skinUrl + '/images/icons/accept.png',
                text: 'Ok'.l(),
                scope: this,
                handler: this.okPress
            }, {
                icon: Axis.skinUrl + '/images/icons/cancel.png',
                text: 'Cancel'.l(),
                scope: this,
                handler: this.cancelPress
            }],
            listeners: {
                hide: {
                    scope: this,
                    fn: this.hide
                }
            }
        });
    },

    destroy: function() {
        if (this.form) {
            this.form.destroy();
        }
        if (this.window) {
            this.window.destroy();
        }
        this.purgeListeners();
    },

    hide: function() {
        this.window.hide();
    },

    show: function() {
        this.window.show();
    },

    okPress: function() {
        if (!this.form.getForm().isValid()) {
            return;
        }
        var form = this.form.getForm();
        var values = form.getFieldValues();
        values.country_name = form.findField('country_id').getValue();
        values.zone_name = form.findField('zone_id').getValue();
        if (false === this.fireEvent('okpress', values)) {
            return;
        }
        this.hide();
    },

    cancelPress: function() {
        this.fireEvent('cancelpress');
        this.hide();
    }

});
