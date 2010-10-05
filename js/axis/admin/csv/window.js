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

    var supportedTypes = new Ext.form.ComboBox({
        store: new Ext.data.Store({
            url: Axis.getUrl('csv/get-supported-types'),
            reader: new Ext.data.ArrayReader({
            }, [
               'name', 'identifier'
            ]),
            autoLoad: true
        }),
        triggerAction: 'all',
        fieldLabel: 'Type'.l(),
        mode: 'local',
        editable: false,
        name: 'general[type]',
        hiddenName: 'general[type]',
        displayField: 'name',
        valueField: 'identifier',
        initialValue: 'products',
        allowBlank: false
    });

    var csvDirections = new Ext.form.ComboBox({
        fieldLabel: 'Direction'.l(),
        id: 'csv_direction',
        name: 'general[direction]',
        hiddenName: 'general[direction]',
        store: new Ext.data.SimpleStore({
            data: [['import', 'Import'.l()], ['export', 'Export'.l()]],
            fields: ['identifier', 'name']
        }),
        triggerAction: 'all',
        mode: 'local',
        displayField: 'name',
        valueField: 'identifier',
        initialValue: 'import',
        allowBlank: false,
        editable: false
    });

    var general_fields = [{
        fieldLabel: 'Name'.l(),
        xtype: 'textfield',
        name: 'general[name]',
        allowBlank: false,
        maxLength: 45
    }, supportedTypes,
       csvDirections, {
        fieldLabel: 'File path'.l(),
        xtype: 'textfield',
        name: 'general[file_path]',
        allowBlank: true,
        qtipText: 'Path Relative to ECART_ROOT'.l(),
        initialValue: 'var/export',
        maxLength: 255
    }, {
        fieldLabel: 'File name'.l(),
        xtype: 'textfield',
        name: 'general[file_name]',
        allowBlank: false,
        initialValue: 'axis.csv',
        maxLength: 255
    }, {
        fieldLabel: 'Id'.l(),
        xtype: 'hidden',
        name: 'general[id]'
    }, {
        xtype: 'fieldset',
        autoHeight: true,
        id: 'export_from',
        title: 'Export from'.l(),
        defaults: {
            anchor: '98%'
        },
        items: [{
            fieldLabel: 'Site'.l(),
            xtype: 'select',
            multiSelect: true,
            transform: 'sites',
            triggerAction: 'all',
            lazyRender: true,
            editable: false,
            name: 'filter[site]',
            hiddenName: 'filter[site]',
            allowBlank: false
        }]
    }];

    /* add event to fields on setValue function */
    Ext.form.Field.prototype.setValue = Ext.form.Field.prototype.setValue.createSequence(function() {
        this.fireEvent('valueset', this, this.value);
    });

    Ext.getCmp('csv_direction').on('valueset', function(combo, value){
        var index = combo.store.find('identifier', value);
        if (-1 === index) {
            return;
        }
        if (combo.store.getAt(index).get('identifier') == 'export') {
            Ext.getCmp('tabpanel').unhideTabStripItem('product_filters');
            Ext.getCmp('export_from').setTitle('Export from'.l());
        } else {
            Ext.getCmp('general_information').show();
            Ext.getCmp('tabpanel').hideTabStripItem('product_filters');
            Ext.getCmp('export_from').setTitle('Import to'.l());
        }
    });

    var product_filter_fields = [{
        fieldLabel: 'Language'.l(),
        xtype: 'select',
        multiSelect: true,
        transform: 'language',
        triggerAction: 'all',
        lazyRender: true,
        editable: false,
        name: 'filter[language_ids]',
        hiddenName: 'filter[language_ids]',
        allowBlank: true,
        id: 'language_combo'
    }, {
        fieldLabel: 'Product Name'.l(),
        xtype: 'textfield',
        name: 'filter[name]',
        maxLength: 32,
        qtipText: 'Begin with'.l()
    }, {
        fieldLabel: 'Product SKU'.l(),
        xtype: 'textfield',
        name: 'filter[sku]',
        maxLength: 32,
        qtipText: 'Begin with'.l()
    }, {
        fieldLabel: 'Stock status'.l(),
        xtype: 'combo',
        name: 'filter[stock]',
        hiddenName: 'filter[stock]',
        store: new Ext.data.SimpleStore({
            data: [['2', 'Any status'.l()], ['0', 'Out of stock'.l()], ['1', 'In stock'.l()]],
            fields: ['id', 'name']
        }),
        triggerAction: 'all',
        mode: 'local',
        displayField: 'name',
        valueField: 'id',
        initialValue: '2',
        allowBlank: true,
        editable: false
    }, {
        fieldLabel: 'Status'.l(),
        xtype: 'combo',
        name: 'filter[status]',
        hiddenName: 'filter[status]',
        store: new Ext.data.SimpleStore({
            data: [['2', 'Any status'.l()], ['0', 'Disabled'.l()], ['1', 'Enabled'.l()]],
            fields: ['id', 'name']
        }),
        triggerAction: 'all',
        mode: 'local',
        displayField: 'name',
        valueField: 'id',
        initialValue: '2',
        allowBlank: true,
        editable: false
    }, {
        layout: 'column',
        border: false,
        defaults: {
            columnWidth: '.5',
            border: false
        },
        items: [{
            layout: 'form',
            items: [{
                xtype: 'textfield',
                fieldLabel: 'Price from'.l(),
                name: 'filter[price_from]',
                anchor: '98%'
            }, {
                xtype: 'textfield',
                fieldLabel: 'Qty from'.l(),
                name: 'filter[qty_from]',
                anchor: '98%'
            }]
        }, {
            layout: 'form',
            items: [{
                xtype: 'textfield',
                fieldLabel: 'to'.l(),
                name: 'filter[price_to]',
                labelStyle: 'width: 20px;',
                style: 'margin-left: -85px;',
                anchor: '98%'
            }, {
                xtype: 'textfield',
                fieldLabel: 'to'.l(),
                name: 'filter[qty_to]',
                labelStyle: 'width: 20px;',
                style: 'margin-left: -85px;',
                anchor: '98%'
            }]
        }]
    }]

    var form = new Ext.form.FormPanel({
        border: false,
        labelAlign: 'left',
        id: 'form_profile_edit',
        defaults: {
            anchor: '100%'
        },
        items: [{
            xtype: 'tabpanel',
            id: 'tabpanel',
            deferredRender: false,
            layoutOnTabChange: true,
            activeTab: 0,
            border: false,
            plain: true,
            defaults: {
                autoHeight: true,
                bodyStyle:'padding:10px',
                border: false,
                layout:'form'
            },
            items: [{
                title: 'General'.l(),
                defaults: {
                    anchor: '98%'
                },
                id: 'general_information',
                items: general_fields
            }, {
                title: 'Export Filters'.l(),
                defaults: {
                    anchor: '98%'
                },
                id: 'product_filters',
                items: product_filter_fields
            }, {
                title: 'Log'.l(),
                id: 'process',
                disabled: true,
                defaults: {
                    anchor: '98%'
                },
                items: [{
                    xtype:'box',
                    anchor:'',
                    autoEl:{
                        tag:'div',
                        id: 'process_log',
                        html: ''
                    }
                }]
            }]
        }]
    })

    var window = new Ext.Window({
        title: 'Profile'.l(),
        items: form,
        closeAction: 'hide',
        resizable: true,
        maximizable: true,
        id: 'window_profile',
        constrainHeader: true,
        autoScroll: true,
        bodyStyle: 'background: white; padding-top: 10px;',
        width: 450,
        height: 400,
        minWidth: 260,
        buttons: [{
            text: 'Run'.l(),
            handler: runProfile
        },  {
            text: 'Save'.l(),
            handler: save
        }, {
            text: 'Close'.l(),
            handler: close
        }]
    })

    window.on('resize', function(){
        form.doLayout();
    })
    window.on('hide', function(){
        form.getForm().reset();
        var processTab = Ext.getCmp('process');
        processTab.disable();
    })
})

function save(){
//    validateForm();
    Ext.getCmp('form_profile_edit').getForm().submit({
        url: Axis.getUrl('csv/save'),
        success: function(){
            Ext.getCmp('window_profile').hide();
            Ext.getCmp('grid-profile').store.reload();
        }
    })
}

function close(){
    Ext.getCmp('window_profile').hide();
}

function runProfile(){
//    validateForm();
    $('#process_log').empty();
    var win = Ext.getCmp('window_profile');
    win.buttons[1].disable();
    win.buttons[2].disable();

    Ext.getCmp('form_profile_edit').getForm().submit({
        url: Axis.getUrl('csv/run'),
        success: function(form, action){
            win.buttons[1].enable();
            win.buttons[2].enable();
            var obj = action.result;
            if (obj.messages && form.findField('general[direction]').getValue() == 'import') {
                var processTab = Ext.getCmp('process');
                processTab.enable();
                Ext.getCmp('tabpanel').activate(processTab);
                addMessage(obj);
            }
        },
        failure: function(form, action){
            if (action.failureType != 'client')
                if (!action.result) {
                    runProfile();
                }
            win.buttons[1].enable();
            win.buttons[2].enable();
        }
    })
}

function addMessage(obj){

    var messages = "";
    var expandable = '';

    for (i in obj.messages.skipped) {
        if (typeof obj.messages.skipped[i] != 'object')
            continue;
        if (limit = obj.messages.skipped[i].length) {
            expandable = 'expandable';
            messages += '<h6>' + i + '</h6>';
            messages += '<ul>';

            for (var j = 0; j < limit; j++)
                messages += '<li>' + obj.messages.skipped[i][j] + '</li>';

            messages += '</ul>';
        }
    }

    Ext.get('process_log').insertHtml(
        'beforeEnd',
        '<div class="message">' +
        '<h5 onclick="toggleDetails(this)" class="'+expandable+'">' +
            obj.messages.imported.count +
            ' entry(s) imported and ' +
            obj.messages.skipped.count +
            ' was skipped</h5>' +
        '<div class="details">' + messages + '</div>' +
        '</div>'
    )

    Ext.get('process_log').insertHtml(
        'beforeEnd',
        '<div class="message"><h5>Completed</h5></div>'
    )
}

function toggleDetails(e){
    if ($(e).next().children().length)
        $(e).next().toggle();
}

//function validateForm(){
//    if (!Ext.getCmp('form_profile_edit').getForm().isValid()) {
//        var error_tab = Ext.getCmp(Ext.query('.x-form-invalid')[0].id).findParentByType('panel');
//        Ext.getCmp('tabpanel').activate(error_tab);
//    }
//}