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

var depRootId = 0;
var Dep = {};
Dep.rootId = Dep.id =  depRootId;

Ext.onReady(function(){
    var Contact = {
        record: Ext.data.Record.create([
            {name: 'id'},
            {name: 'email'},
            {name: 'subject'},
            {name: 'message'},
            {name: 'custom_info'},
            {name: 'department_name'},
            {name: 'created_at'},
            {name: 'department_id'},
            {name: 'datetime'},
            {name: 'message_status'}
        ]),
        
        getSelectedId: function() {
            var selModel = grid.getSelectionModel();
            var selectedItems = grid.getSelectionModel().selections.items;
            if (!selectedItems.length) {
                return false;
            }
            if (selectedItems[0]['data']['id'])
                return selectedItems[0].id;
            return false;
        },
        
        getSelectedDepartamentId: function() {
            var selModel = grid.getSelectionModel();
            var selectedItems = grid.getSelectionModel().selections.items;
            if (!selectedItems.length) {
                return false;
            }
            if (selectedItems[0]['data']['department_id'])
                return selectedItems[0].id;
            return false;
        },
        
        sendmail: function() {
            formMail.expand(); 
            formMail.getForm().submit({
                url: Axis.getUrl('contacts_index/send'),
                params: {depId: Contact.getSelectedDepartamentId() },
                    success: function() {
                        Contact.window.hide();
                        Ext.Ajax.request({
                         url: Axis.getUrl('contacts_index/set-status'),
                         params: {id: Contact.getSelectedId(), message_status: 'replied'},
                         callback: function() {
                            ds.reload();
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
            
            var mail    = grid.getSelectionModel().getSelected().data['email'];
            var subject = grid.getSelectionModel().getSelected().data['subject'];
            var message = grid.getSelectionModel().getSelected().data['message'];
            var custom  = grid.getSelectionModel().getSelected().data['custom_info'];
            var datetime  = grid.getSelectionModel().getSelected().data['datetime'];
            formMail.getForm().findField('email').setValue(mail);
            formMail.getForm().findField('subject').setValue('re: '+subject);
            var template = new Ext.Template.from('tpl-message');
            Contact.window.items.first().body.update(template.applyTemplate({
                'from':    mail,
                'subject': subject,
                'message': message,
                'custom':  custom.replace(/\n/, '<br />'),
                'datetime': datetime
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
                url: Axis.getUrl('contacts_index/set-status'),
                params: {id: Contact.getSelectedId(), message_status: 'read'},
                callback: function() {
                    ds.reload();
                }
            });
        },
        
        remove: function() {
            if (!confirm('Delete Contact?'))
                return;
            var data = {};
            var selectedItems = grid.getSelectionModel().selections.items;
            for (var i = 0; i < selectedItems.length; i++) {
                if (!selectedItems[i]['data']['id']) continue;
                data[i] = selectedItems[i]['data']['id'];
            }
                
            Ext.Ajax.request({
                url: Axis.getUrl('contacts_index/delete'),
                params: {data: Ext.encode(data)},
                callback: function() {
                    ds.reload();
                }
            });
       }
    };
    
    Ext.QuickTips.init();

    var ds = new Ext.data.Store({
        url: Axis.getUrl('contacts_index/list'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            totalProperty: 'count',
            id: 'id'
        }, Contact.record),
        remoteSort: true,
        pruneModifiedRecords: true
    });
    
    var cm = new Ext.grid.ColumnModel([{
        header: "Email".l(),
        dataIndex: 'email',
        width: 100,
        sortable: true
    }, {
        header: "Subject".l(),
        dataIndex: 'subject',
        width: 100,
        sortable: true
    }, {
        header: "Message".l(),
        dataIndex: 'message',
        width: 250,
        sortable: false,
        renderer: function(value) {
            return value.substr(0,50);
        }
    }, {
        header: "Data & Time".l(),
        dataIndex: 'created_at',
        width: 170,
        sortable: true
    }, {
        header: "Department".l(),
        dataIndex: 'department_name',
        width: 170,
        sortable: true,
        renderer: function(value) {
            return value;          
        }
    }, {
        header: "Status".l(),
        dataIndex: 'message_status',
        width: 70,
        sortable: true
    }]);
    
    formMail = new Ext.form.FormPanel({
        title: 'Reply form',
        labelWidth: 80,
        name: 'sendmail',
        autoScroll: true,
        bodyStyle: 'padding: 5px',
        defaults: {width: 300},
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
            allowBlank: true
        }, {
            fieldLabel: 'Message'.l(),
            name: 'message',
            xtype: 'textarea',
            height: 130,
            allowBlank: false
        }]
    });
    
    var mailStore = new Ext.data.Store({
        url:  Axis.getUrl('template_mail/list-mail'),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        },
        ['id', 'name']
        ),
        autoLoad: true
    });
    mailStore.load();
    
    var Department = {
        deleteDepartment: function () {
             if (Dep.id == 0) {
                 return false;
             }
             if (!confirm("Are you sure?".l())) {
                 return false;
             }
             Ext.Ajax.request({
                 url: Axis.getUrl('contacts_index/delete-department'),
                 method: 'post',
                 params: {id: Dep.id},
                 callback: function() {
                     tree.getNodeById(Dep.id).parentNode.reload();
                     Dep.id = 0;
                 }
             });
        },
        
        saveDepartment: function() {
             formDepart.getForm().submit({   
               url: Axis.getUrl('contacts_index/save-department'),
               params : {id :Dep.id },
               success:  function() {
                 winDepart.hide();
                 tree.getRootNode().reload();
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
                 'url': Axis.getUrl('contacts_index/get-department'),
                 'method': 'post',
                 'params': {id: Dep.id}
             });
         }
    };
    
    formDepart = new Ext.form.FormPanel({
        labelWidth: 80,
        name : 'fdepart',
        autoScroll: true,
        defaults: {anchor: '95%'},
        border: false,
        autoHeight: true,
        reader: new Ext.data.JsonReader({
                root: 'data' 
            },
            ['name', 'email']     
        ),
        items: [new Ext.form.ComboBox({
            triggerAction: 'all',
            displayField: 'name',
            typeAhead: true,
            mode: 'local',
            valueField: 'id',
            fieldLabel: 'Email'.l(),
            name: 'email',
            store: mailStore
        }), {
            fieldLabel: 'Name'.l(),
            name: 'name',
            xtype: 'textfield',
            allowBlank: true
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
        },{
            text: 'Cancel'.l(),
            handler: function(){
                winDepart.hide();
            }
        } ]
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
    
    var tree = new Ext.tree.TreePanel({
        root : root,
        loader: new Ext.tree.TreeLoader({
            dataUrl: Axis.getUrl('contacts_index/get-departments')
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
        },{
            text: 'Edit'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            handler: Department.editDepartment
        },{
            text: 'Delete'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: Department.deleteDepartment
        }]
    });
    
    new Ext.tree.TreeSorter(tree, {folderSort:true});
    
    tree.on('click', function (node, e) {
        ds.baseParams = {depId : node.id};
        ds.load({params:{start: 0, limit: 21}});
        Dep.id = node.id;
    });
    root.expand();
    
    var grid = new Axis.grid.GridPanel({
        ds: ds,
        cm: cm,
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
                grid.getStore().reload();
            }
        }]
    });
    
    new Axis.Panel({
        items: [
            tree,
            grid
        ]
    });
    
    grid.on('rowdblclick', Contact.view);
    
    ds.load({params:{start:0, limit:25}});
}, this);