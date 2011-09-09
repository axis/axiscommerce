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
            url: Axis.getUrl('import/list-type'),
            reader: new Ext.data.ArrayReader({
                id: 0
            }, Ext.data.Record.create([
                {name: 'name', mapping: 1}
            ])),
            autoLoad: true
        }),
        triggerAction: 'all',
        fieldLabel: 'Application type'.l(),
        mode: 'local',
        editable: false,
        name: 'profile[type]',
        displayField: 'name',
        valueField: 'name',
        allowBlank: false
    });

    var connection_fields = [
        supportedTypes, {
        fieldLabel: 'Name'.l(),
        xtype: 'textfield',
        name: 'profile[name]',
        allowBlank: false,
        maxLength: 45
    }, {
        fieldLabel: 'Hostname'.l(),
        xtype: 'textfield',
        initialValue: 'localhost',
        name: 'profile[host]',
        allowBlank: true,
        maxLength: 255
    }, {
        fieldLabel: 'Database'.l(),
        xtype: 'textfield',
        name: 'profile[db_name]',
        allowBlank: true,
        maxLength: 255
    }, {
        fieldLabel: 'Username'.l(),
        xtype: 'textfield',
        initialValue: 'root',
        name: 'profile[db_user]',
        allowBlank: true,
        maxLength: 255
    }, {
        fieldLabel: 'Password'.l(),
        xtype: 'textfield',
        inputType: 'password',
        name: 'profile[db_password]',
        allowBlank: true,
        maxLength: 255
    }, {
        fieldLabel: 'Table prefix'.l(),
        xtype: 'textfield',
        name: 'profile[table_prefix]',
        allowBlank: true,
        maxLength: 45
    }, {
        fieldLabel: 'Id'.l(),
        xtype: 'hidden',
        name: 'profile[id]'
    }];

    var advice = new Ext.form.FieldSet({
        title: 'Notice'.l(),
        autoHeight: true,
        defaults: {
            anchor: '98%'
        },
        items: [{
            xtype:'box',
            anchor:'',
            autoEl:{
                tag:'div',
                html: 'Please place all images under AXIS_ROOT/media/import/ with writable permission to all files including sub folders.'.l()
            }
        }]
    });

    var general_fields = [{
        title: 'General'.l(),
        autoHeight: true,
        xtype: 'fieldset',
        id: 'general_fields',
        defaults: {
            anchor: '100%'
        },
        items: [{
            fieldLabel: 'Site'.l(),
            xtype: 'combo',
            transform: 'sites',
            triggerAction: 'all',
            lazyRender: true,
            editable: false,
            name: 'general[site]',
            hiddenName: 'general[site]'
        }, Ext.getCmp('import_language_combo').cloneConfig({
            fieldLabel: 'Primary language'.l(),
            name: 'primary_language',
            hiddenName: 'primary_language',
            qtipText: 'This language will be used for links'.l()
        })]
    }, {
        title: 'Import preferences'.l(),
        autoHeight: true,
        id: 'import_options',
        xtype: 'fieldset',
        labelWidth: 120,
        defaults: {
            anchor: '98%'
        }
    }, advice];

    var form = new Axis.FormPanel({
        id: 'form_profile_edit',
        bodyStyle: 'padding: 5px 0 0 0;',
        defaults: {
            anchor: '100%'
        },
        items: [{
            xtype: 'tabpanel',
            id: 'tabpanel',
            anchor: Ext.isWebKit ? 'undefined 100%' : '100% 100%',
            deferredRender: false,
            layoutOnTabChange: true,
            activeTab: 0,
            border: false,
            plain: true,
            defaults: {
                bodyStyle:'padding:10px',
                autoScroll: true,
                hideMode: 'offsets',
                layout: 'form'
            },
            items: [{
                title: 'Connection'.l(),
                defaults: {
                    anchor: '100%'
                },
                id: 'connect_information',
                items: connection_fields
            }, {
                title: 'General'.l(),
                disabled: true,
                id: 'general_information',
                defaults: {
                    anchor: '100%'
                },
                items: general_fields
            }, {
                title: 'Locale'.l(),
                id: 'locale',
                disabled: true,
                defaults: {
                    anchor: '100%'
                }
            }, {
                title: 'Log'.l(),
                id: 'process',
                disabled: true,
                defaults: {
                    anchor: '100%'
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
    });

    var window = new Axis.Window({
        title: 'Profile'.l(),
        items: form,
        maximizable: true,
        id: 'window_profile',
        width: 560,
        height: 505,
        buttons: [{
            text: 'Connect'.l(),
            id: 'connectButton',
            handler: connect
        }, {
            text: 'Import'.l(),
            handler: importData,
            disabled: true
        },  {
            text: 'Save'.l(),
            handler: save
        }, {
            text: 'Close'.l(),
            handler: close
        }]
    });

    window.on('resize', function(){
        form.doLayout();
    });
    window.on('hide', function(){
        removeImportReady();
    });

    function importData() {
        if (!Ext.getCmp('form_profile_edit').getForm().isValid()) {
            return;
        }
        Ext.getCmp('window_profile').buttons[1].disable();
        Ext.getCmp('window_profile').buttons[2].disable();
        Ext.getCmp('window_profile').buttons[3].disable();
        ajaxImportData(1);
    }

    function save(){
        if (!form.getForm().isValid()) {
            var error_tab = Ext.getCmp(Ext.query('.x-form-invalid')[0].id).findParentByType('panel');
            Ext.getCmp('tabpanel').activate(error_tab);
        }
        form.getForm().submit({
            url: Axis.getUrl('import/save'),
            success: function(){
                Ext.getCmp('window_profile').hide();
                Ext.getCmp('grid-profile').store.reload();
            }
        })
    }

    function connect(){
        Ext.getCmp('connectButton').disable();
        Ext.Ajax.request({
            url: Axis.getUrl('import/connect'),
            params: {
                'profile[type]':    form.getForm().findField('profile[type]').getValue(),
                'profile[host]':    form.getForm().findField('profile[host]').getValue(),
                'profile[db_name]': form.getForm().findField('profile[db_name]').getValue(),
                'profile[db_user]': form.getForm().findField('profile[db_user]').getValue(),
                'profile[db_password]': form.getForm().findField('profile[db_password]').getValue(),
                'profile[table_prefix]': form.getForm().findField('profile[table_prefix]').getValue()
            },
            callback: function(form, success, response){
                var obj = Ext.decode(response.responseText);
                if (obj.success) {
                    setImportReady(obj.data);
                    return;
                }
                Ext.getCmp('connectButton').enable();
            }
        })
    }

    function close(){
        window.hide();
    }
})

function ajaxImportData(clearSession){
    if (clearSession) {
        $('#process_log').empty();
    }

    Ext.getCmp('form_profile_edit').getForm().submit({
        url: Axis.getUrl('import/import'),
        params: {
            'clearSession': clearSession
        },
        success: function(form, action){
            var processTab = Ext.getCmp('process');
            processTab.enable();
            Ext.getCmp('tabpanel').activate(processTab);
            var obj = action.result;
            addMessage(obj);
        },
        failure: function(form, action) {
            var obj = action.result;
            if (obj.success && (!obj || !obj.finalize)) {
                ajaxImportData(0);
            }
            Ext.getCmp('window_profile').buttons[1].enable();
            Ext.getCmp('window_profile').buttons[2].enable();
            Ext.getCmp('window_profile').buttons[3].enable();
        }
    })
}

function addMessage(obj){

    var messages = "";
    var expandable = '';

    for (i in obj.messages) {
        if ((limit = obj.messages[i].length)) {
            expandable = 'expandable';
            messages += '<h6>' + i + '</h6>';
            messages += '<ul>';

            for (var j = 0; j < limit; j++)
                messages += '<li>' + obj.messages[i][j] + '</li>';

            messages += '</ul>';
        }
    }

    Ext.get('process_log').insertHtml(
        'beforeEnd',
        '<div class="message">' +
        '<h5 onclick="toggleDetails(this)" class="'+expandable+'">' + obj.group + ': ' + obj.imported + ' entry(s) of ' + obj.processed + ' imported</h5>' +
        '<div class="details">' + messages + '</div>' +
        '</div>'
    )

    if (!obj.finalize)
        ajaxImportData(0);
    else {
        $('#mask').remove();
        Ext.get('process_log').insertHtml(
            'beforeEnd',
            '<div class="message"><h5>Completed</h5></div>'
        )
        Ext.getCmp('window_profile').buttons[2].enable();
        Ext.getCmp('window_profile').buttons[3].enable();
    }
}

function toggleDetails(e){
    if ($(e).next().children().length)
        $(e).next().toggle();
}

function setImportReady(data){
    updateImportOptions(data.queue);
    updateLanguages(data.languages);
    var generalTab = Ext.getCmp('general_information');
    generalTab.enable();
    Ext.getCmp('locale').enable();
    Ext.getCmp('tabpanel').activate(generalTab);
    Ext.getCmp('window_profile').buttons[0].disable();
    Ext.getCmp('window_profile').buttons[1].enable();
}

function removeImportReady() {
    if (Ext.getCmp('window_profile').buttons[0].disabled)
        Ext.Ajax.request({
            url: Axis.getUrl('import/disconnect'),
            params: {
                'profile[type]':    Ext.getCmp('form_profile_edit').getForm().findField('profile[type]').getValue(),
                'profile[host]':    Ext.getCmp('form_profile_edit').getForm().findField('profile[host]').getValue(),
                'profile[db_name]': Ext.getCmp('form_profile_edit').getForm().findField('profile[db_name]').getValue(),
                'profile[db_user]': Ext.getCmp('form_profile_edit').getForm().findField('profile[db_user]').getValue(),
                'profile[db_password]': Ext.getCmp('form_profile_edit').getForm().findField('profile[db_password]').getValue(),
                'profile[table_prefix]': Ext.getCmp('form_profile_edit').getForm().findField('profile[table_prefix]').getValue()
            },
            callback: function(){
                clearOptions();
                clearLanguages();
                $('#process_log').empty();
                Ext.getCmp('general_information').disable();
                Ext.getCmp('locale').disable();
                Ext.getCmp('process').disable();
                Ext.getCmp('tabpanel').activate(Ext.getCmp('connect_information'));
                Ext.getCmp('window_profile').buttons[0].enable();
                Ext.getCmp('window_profile').buttons[3].enable();
                Ext.getCmp('window_profile').buttons[1].disable();
            }
        });
}
