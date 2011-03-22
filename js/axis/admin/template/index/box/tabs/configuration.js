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

var ConfigurationFields = {

    el: null,

    fieldset: null,

    clearData: function() {
        var fields = ConfigurationFields.el.findByType('field');
        Ext.each(fields, function(field) {
            field.setValue('');
        });
    },

    addFields: function(data) {
        var fields      = data.configuration_fields,
            fieldset    = ConfigurationFields.fieldset;

        for (var i in fields) {
            if (typeof(fields[i]) != 'object') {
                continue;
            }

            var field = fields[i];
            if (field.data) {
                if (!field.xtype) {
                    field.xtype = 'combo';
                }
                if ('combo' === field.xtype) {
                    Ext.applyIf(field, {
                        'editable'      : false,
                        'triggerAction' : 'all',
                        'displayField'  : 'value',
                        'valueField'    : 'id',
                        'hiddenName'    : 'configuration[' + i + ']'
                    });
                    field.mode = 'local';
                    var arrayData = [];
                    Ext.each(field.data, function(item, index) {
                        arrayData.push([index, item]);
                    });
                    field.store = new Ext.data.ArrayStore({
                        data    : arrayData,
                        fields  : ['id', 'value']
                    });
                }
            }
            fieldset.add(
                Ext.applyIf(field, {
                    'name'  : 'configuration[' + i + ']',
                    'value' : field.initialValue,
                    'xtype' : 'textfield'
                })
            );
        }

        if (!fieldset.items.length) {
            fieldset.hide();
        } else {
            fieldset.show();
        }

        fieldset.doLayout();
    },

    loadData: function(data) {
        ConfigurationFields.clearData();
        ConfigurationFields.fieldset.removeAll();
        ConfigurationFields.addFields(data);

        // fill configuration fields with database and default data
        panel = ConfigurationFields.el;
        panel.doLayout();

        var config = Ext.applyIf(
            Ext.decode(data.config),
            data.configuration_values
        );

        var additionalConfiguration = {};
        for (var key in config) {
            var field = panel.find('name', 'configuration[' + key + ']')[0];
            if (field && field.setValue) {
                field.setValue(config[key]);
            } else if (field) {
                field.value = config[key];
            } else {
                additionalConfiguration[key] = config[key];
            }
        }

        var field = panel.find('name', 'additional_configuration')[0];
        additionalConfiguration = Ext.encode(additionalConfiguration);
        if (field && field.setValue) {
            field.setValue(additionalConfiguration);
        } else if (field) {
            field.value = additionalConfiguration;
        }
    },

    getData: function() {
        //
    }

};

Ext.onReady(function() {

    ConfigurationFields.fieldset = new Ext.form.FieldSet({
        title: 'Box Specific Configuration'.l(),
        xtype: 'fieldset',
        border: true,
        defaults: {
            allowBlank: true,
            anchor: '100%'
        }
    });

    ConfigurationFields.el = new Ext.Panel({
        title: 'Configuration'.l(),
        id: 'panel-configuration',
        bodyStyle: 'padding: 10px 10px 0 10px',
        layout: 'form',
        defaults: {
            allowBlank: true,
            xtype: 'textfield',
            anchor: '100%'
        },
        items: [ConfigurationFields.fieldset, {
            title: 'Basic Configuration'.l(),
            xtype: 'fieldset',
            // collapsible: true,
            // collapsed: true,
            border: true,
            items: [{
                layout: 'column',
                border: false,
                defaults: {
                    layout: 'form',
                    border: false
                },
                items: [{
                    columnWidth: 0.5,
                    defaults: {
                        allowBlank: true,
                        xtype: 'textfield',
                        anchor: '-10'
                    },
                    items: [{
                        fieldLabel: 'Title'.l(),
                        name: 'configuration[title]'
                    }, {
                        fieldLabel: 'Url'.l(),
                        name: 'configuration[url]'
                    }, {
                        fieldLabel: 'Class'.l(),
                        name: 'configuration[class]'
                    }]
                }, {
                    columnWidth: 0.5,
                    defaults: {
                        allowBlank: true,
                        xtype: 'textfield',
                        anchor: '100%'
                    },
                    items: [{
                        fieldLabel: 'Template'.l(),
                        name: 'configuration[template]'
                    }, {
                        fieldLabel: 'Tab'.l(),
                        name: 'configuration[tab_container]'
                    }, {
                        editable: false,
                        triggerAction: 'all',
                        fieldLabel: 'Disable Wrapper'.l(),
                        name: 'configuration[disable_wrapper]',
                        hiddenName: 'configuration[disable_wrapper]',
                        displayField: 'value',
                        valueField: 'id',
                        mode: 'local',
                        store: new Ext.data.ArrayStore({
                            data: [[0, 'No'.l()], [1, 'Yes'.l()]],
                            fields: ['id', 'value']
                        }),
                        initialValue: 0,
                        xtype: 'combo'
                    }]
                }]
            }, {
                anchor: '100%',
                fieldLabel: 'Additional Configuration'.l(),
                name: 'additional_configuration',
                xtype: 'textfield'
            }]
        }]
    });

    Box.Window.addTab(ConfigurationFields.el, 20);
    Box.Window.dataObjects.push(ConfigurationFields);
});
