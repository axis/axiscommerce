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

var depRootId = 0;
var Dep = {};
Dep.rootId = Dep.id =  depRootId;

var Contact = {

    grid: null,

    record: null,

    getSelectedId: function() {
        var selectedItems = Contact.grid.getSelectionModel().getSelections();
        if (!selectedItems.length) {
            return false;
        }
        if (selectedItems[0]['data']['id']) {
            return selectedItems[0].id;
        }
        return false;
    },

    getSelectedDepartamentId: function() {
        var selectedItems = Contact.grid.getSelectionModel().getSelections();
        if (!selectedItems.length) {
            return false;
        }
        if (selectedItems[0]['data']['department_id']) {
            return selectedItems[0]['data']['department_id'];
        }
        return false;
    },

    sendmail: function() {
        formMail.expand();
        formMail.getForm().submit({
            url: Axis.getUrl('contacts/index/send'),
            success: function() {
                Contact.window.hide();
                Ext.Ajax.request({
                    url: Axis.getUrl('contacts/index/save'),
                    params: {
                        id: Contact.getSelectedId(),
                        message_status: 'replied'
                    },
                    callback: function() {
                        Contact.grid.getStore().reload();
                    }
                })
            }
        });
    },

    mail: function() {
        cId = Contact.getSelectedId();
        if (!cId) {
             return false;
        }
        formMail.getForm().clear();
        Contact.window.show();
        var selected = Contact.grid.getSelectionModel().getSelected();
        var mail    = selected.data['email'];
        var subject = selected.data['subject'];
        var message = selected.data['message'];
        var custom  = selected.data['custom_info'];
        var datetime = selected.data['datetime'];
        formMail.getForm().findField('email').setValue(mail);
        formMail.getForm().findField('subject').setValue('re: '+subject);
        formMail.getForm().findField('department_id').setValue(Contact.getSelectedDepartamentId());
        var template = new Ext.Template.from('tpl-message');
        Contact.window.items.first().body.update(template.applyTemplate({
            'from'      : mail,
            'subject'   : Axis.escape(subject),
            'message'   : Axis.escape(message),
            'custom'    : Axis.escape(custom).replace(/\n/, '<br />'),
            'datetime'  : datetime
        }));

        formMail.expand();
        return true;
    },

    view: function (){
        if (!Contact.mail()) {
            return;
        }
        Contact.window.items.first().expand();

        Ext.Ajax.request({
            url: Axis.getUrl('contacts/index/save'),
            params: {
                id: Contact.getSelectedId(),
                message_status: 'read'
            },
            callback: function() {
                Contact.grid.getStore().reload();
            }
        });
    },

    remove: function() {
        var selectedItems = Contact.grid.getSelectionModel().getSelections();
        if (!selectedItems.length || !confirm('Are you sure?'.l())) {
            return;
        }

        var data = {};
        for (var i = 0, len = selectedItems.length; i < len; i++) {
            if (!selectedItems[i]['data']['id']) {
                continue;
            }
            data[i] = selectedItems[i]['data']['id'];
        }

        Ext.Ajax.request({
            url: Axis.getUrl('contacts/index/remove'),
            params: {
                data: Ext.encode(data)
            },
            callback: function() {
                Contact.grid.getStore().reload();
            }
        });
   }
};

var Department = {

    tree: null,

    deleteDepartment: function () {
         if (!Dep.id || !confirm("Are you sure?".l())) {
             return false;
         }
         Ext.Ajax.request({
             url: Axis.getUrl('contacts/department/remove'),
             method: 'post',
             params: {
                 id: Dep.id
             },
             callback: function() {
                 Department.tree.getNodeById(Dep.id).parentNode.reload();
                 Dep.id = 0;
             }
         });
    },

    saveDepartment: function() {
        formDepart.getForm().submit({
            url: Axis.getUrl('contacts/department/save'),
            params: {
                id: Dep.id
            },
            success:  function() {
                winDepart.hide();
                Department.tree.getRootNode().reload();
            }
         });
     },

     addDepartment: function () {
         formDepart.getForm().clear();
         Dep.id = 0;
         winDepart.show();
     },

     editDepartment: function () {
         if (Dep.id == 0) {
             return false;
         }
         winDepart.show();
         formDepart.getForm().load({
             'url': Axis.getUrl('contacts/department/load'),
             'method': 'post',
             'params': {id: Dep.id}
         });
     }
};

Ext.onReady(function() {

    Ext.QuickTips.init();

    Contact.record = Ext.data.Record.create([
        {name: 'id', type: 'int'},
        {name: 'email'},
        {name: 'subject'},
        {name: 'message'},
        {name: 'custom_info'},
        {name: 'created_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
        {name: 'department_id', type: 'int'},
        {name: 'message_status'},
        {name: 'site_id', type: 'int'}
    ]);

    var ds = new Ext.data.Store({
        autoLoad: true,
        baseParams: {
            limit: 25
        },
        url: Axis.getUrl('contacts/index/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Contact.record),
        remoteSort: true,
        sortInfo: {
            field: 'id',
            direction: 'DESC'
        }
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            dataIndex: 'id',
            header: 'Id'.l(),
            width: 90
        }, {
            header: "Email".l(),
            dataIndex: 'email',
            width: 190
        }, {
            header: "Subject".l(),
            dataIndex: 'subject',
            renderer: Axis.escape,
            width: 190
        }, {
            header: "Message".l(),
            dataIndex: 'message',
            id: 'message',
            renderer: Axis.escape,
            width: 250
        }, {
            header: "Created On".l(),
            dataIndex: 'created_at',
            width: 130,
            renderer: function(v) {
                return Ext.util.Format.date(v) + ' ' + Ext.util.Format.date(v, 'H:i:s');
            }
        }, {
            header: "Department".l(),
            dataIndex: 'department_id',
            width: 170,
            renderer: function(v) {
                var i = 0;
                while (departaments[i]) {
                    if (v == departaments[i][0]) {
                        return departaments[i][1];
                    }
                    i++;
                }
                return v;
            },
            filter: {
                editable: false,
                store: new Ext.data.ArrayStore({
                    data: departaments,
                    fields: ['id', 'name']
                })
            }
        }, {
            header: "Site".l(),
            dataIndex: 'site_id',
            width: 150,
            renderer: function(v) {
                var i = 0;
                while (sites[i]) {
                    if (v == sites[i][0]) {
                        return sites[i][1];
                    }
                    i++;
                }
                return v;
            },
            filter: {
                editable: false,
                resetValue: 'reset',
                store: new Ext.data.ArrayStore({
                    data: sites, // see index.phtml
                    fields: ['id', 'name']
                })
            }
        }, {
            header: "Status".l(),
            dataIndex: 'message_status',
            width: 120,
            renderer: function(v) {
                var i = 0;
                while (statuses[i]) {
                    if (v == statuses[i][0]) {
                        return statuses[i][1];
                    }
                    i++;
                }
                return v;
            },
            filter: {
                editable: false,
                store: new Ext.data.ArrayStore({
                    data: statuses,
                    fields: ['id', 'name']
                })
            }
        }]
    });

    Contact.grid = new Axis.grid.GridPanel({
        autoExpandColumn: 'message',
        ds: ds,
        cm: cm,
        plugins: [new Axis.grid.Filter()],
        bbar: new Axis.PagingToolbar({
            store: ds
        }),
        tbar: [{
            text: 'Send email'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/email.png',
            handler: Contact.mail
        }, {
            text: 'View'.l(),
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            cls: 'x-btn-text-icon',
            handler: Contact.view
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler: Contact.remove
        }, '->', {
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-icon',
            handler: function() {
                Contact.grid.getStore().reload();
            }
        }]
    });

    formMail = new Ext.form.FormPanel({
        title: 'Reply form',
        labelWidth: 80,
        name: 'sendmail',
        autoScroll: true,
        bodyStyle: 'padding: 5px',
        defaults: {
            anchor: '100%'
        },
        border: false,
        items: [{
            fieldLabel: 'Email'.l(),
            name: 'email',
            readOnly: true,
            xtype: 'textfield',
            vtype: 'email',
            allowBlank:false
        }, {
            fieldLabel: 'Subject'.l(),
            name: 'subject',
            xtype: 'textfield',
            allowBlank: false
        }, {
            fieldLabel: 'Message'.l(),
            name: 'message',
            xtype: 'textarea',
            height: 130,
            allowBlank: false
        }, {
            name: 'department_id',
            xtype: 'hidden'
        }]
    });

    var mailStore = new Ext.data.Store({
        url:  Axis.getUrl('core/mail/list-mail'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, ['id', 'name']),
        autoLoad: true
    });

    formDepart = new Ext.form.FormPanel({
        labelWidth: 80,
        name : 'fdepart',
        autoScroll: true,
        defaults: {
            anchor: '100%'
        },
        border: false,
        autoHeight: true,
        reader: new Ext.data.JsonReader({
                root: 'data'
            },
            ['name', 'email']
        ),
        items: [new Ext.form.ComboBox({
            allowBlank: false,
            triggerAction: 'all',
            displayField: 'name',
            typeAhead: true,
            mode: 'local',
            valueField: 'id',
            fieldLabel: 'Email'.l(),
            name: 'email',
            store: mailStore
        }), {
            allowBlank: false,
            fieldLabel: 'Name'.l(),
            name: 'name',
            xtype: 'textfield'
        }]
    });

    winDepart = new Ext.Window({
        closeAction: 'hide',
        title: 'Department'.l(),
        width: 380,
        name : 'department',
        autoScroll: true,
        bodyStyle: 'padding: 5px; background: white',
        items: formDepart,
        buttons: [{
            text: 'Save'.l(),
            handler: Department.saveDepartment
        }, {
            text: 'Cancel'.l(),
            handler: function(){
                winDepart.hide();
            }
        }]
    });

    //main send  && read windows
    Contact.window = new Ext.Window({
        layout: 'accordion',
        layoutConfig: {
            animate:false
        },
        width: 550,
        height: 500,
        modal: true,
        closeAction: 'hide',
        plain: true,
        title: 'Mail',
        items: [{
            title: 'Read mail',
            autoScroll:true,
            bodyStyle: 'padding: 5px;'
        }, formMail],
        buttons: [{
            text: 'Send'.l(),
            handler: Contact.sendmail
        }, {
            text: 'Close'.l(),
            handler: function(){
                Contact.window.hide();
            }
        }]
    });

    var root = new Ext.tree.AsyncTreeNode({
        text: 'All'.l(),
        draggable: false,
        id: '0'
    });

    Department.tree = new Ext.tree.TreePanel({
        root : root,
        loader: new Ext.tree.TreeLoader({
            dataUrl: Axis.getUrl('contacts/department/list')
        }),
        autoScroll:true,
        width: 230,
        autoScroll: true,
        region: 'west',
        collapsible: true,
        collapseMode: 'mini',
        header: false,
        split: true,
        tbar: [{
            text: 'Add'.l(),
            tooltip: {text: 'Add new Department ', title: 'Add '},
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler: Department.addDepartment
        }, {
            text: 'Edit'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            handler: Department.editDepartment
        }, {
            text: 'Delete'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: Department.deleteDepartment
        }]
    });

    Department.tree.on('click', function (node, e) {
        Dep.id = node.id;
        ds.baseParams['departmentId'] = Dep.id;
        ds.load();
    });
    root.expand();

    new Axis.Panel({
        items: [
            Department.tree,
            Contact.grid
        ]
    });

    Contact.grid.on('rowdblclick', Contact.view);

}, this);