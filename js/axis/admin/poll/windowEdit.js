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

Ext.onReady(function () {

    var tabs = new Ext.TabPanel({
        id: 'question-language-tabs',
        layoutOnTabChange: true,
        plain: true,
        enableTabScroll: true,
        deferredRender: false,
        defaults: {
            hideMode: 'offsets'
        },
        activeTab: 0,
        border: false
    });

    for (var languageId in Axis.languages) {

       Ext.getCmp('question-language-tabs').add({
            title: Axis.languages[languageId],
            bodyStyle: {padding:'11px'},
            autoHeight: true,
            items:[{
                layout: 'form',
                border: false,
                items: [{
                        xtype: 'textarea',
                        fieldLabel: 'Question'.l(),
                        name: 'description[' + languageId + ']',
                        id: 'description[' + languageId + ']',
                        anchor: '95%'
//                        ,allowBlank: false
                    }, {
                        xtype: 'button',
                        width: 70,
                        bodyStyle:{padding:'10px'},
                        text: 'Add'.l(),
                        name: 'newAnswer[' + languageId + ']',
                        icon: Axis.skinUrl + '/images/icons/add.png',
                        handler: function (){ Poll().addAnswerRow();}

                    },{
                        height: 10,
                        border: false
                    }, new Ext.Container({
                        anchor:'95%',
                        autoEl: 'div',
                        id: 'answers-rowset-' + languageId,
                        layout: 'column'
                    })
                ]
            }]
        });
    }

    var statusQuestionCombobox = new Ext.form.ComboBox({
        fieldLabel: 'Status'.l(),
        anchor: '90%',
        editable: false,
        triggerAction : 'all',
        name: 'status',
        hiddenName: 'status',
        mode: 'local',
        store: new Ext.data.ArrayStore({
            id: 0,
            fields: ['id','label'],
            data: [[0, 'Disabled'.l()], [1, 'Enabled'.l()]]
        }),
        initialValue: 1,
        valueField: 'id',
        displayField: 'label'
    });

    var typeQuestionCombobox = new Ext.form.ComboBox({
        fieldLabel: 'Type'.l(),
        anchor: '90%',
        allowBlank: false,
        editable: false,
        triggerAction : 'all',
        name: 'type',
        hiddenName: 'type',
        mode: 'local',
        store: new Ext.data.ArrayStore({
            id: 0,
            fields: ['id','label'],
            data: [[0, 'Singleselect'.l()], [1, 'Multiselect'.l()]]
        }),
        initialValue: 0,
        valueField: 'id',
        displayField: 'label'
    });

    var questionRecord = [
        {name: 'id'},
        {name: 'status'},
        {name: 'type'},
        {name: 'sites'}
    ];
    for (var languageId in Axis.languages) {
        questionRecord.push({
            name: 'description[' + languageId + ']',
            mapping:'description[' + languageId + ']'
        });
    }

    var editQuestionForm = new Ext.form.FormPanel({
        labelAlign: 'side',
        labelWidth: 75,
        autoScroll: true,
        id: 'form-question',
        border: false,
        width: 600,
        reader: new Ext.data.JsonReader({
                root: 'data'
            }, questionRecord
        ),
        items: [{
            xtype: 'hidden',
            name: 'id'
        },  {
            layout: 'column',
            border: false,
            bodyStyle: {padding:'10px 10px 0'},
            items: [{
                columnWidth: .5,
                layout: 'form',
                border: false,
                items: [statusQuestionCombobox]
            }, {
                columnWidth: .5,
                layout: 'form',
                border: false,
                items: [typeQuestionCombobox]
            }]
        }, {
            layout: 'form',
            border: false,
            anchor: '95%',
            bodyStyle: {padding:'0 10px'},
            items: [{
                xtype: 'multiselect',
                fieldLabel: 'Sites'.l(),
                name: 'sites',
                anchor:'100%',
                height: 60,
                border: false,
                allowBlank: true,
                displayField: 'name',
                valueField: 'id',
                id: 'sites',
                store: Ext.StoreMgr.lookup('storeSites')
            }]
        },
            tabs
        ]
    });

    var editQuestionWindow = new Ext.Window({
        id: 'window-question',
        constrainHeader: true,
        closeAction: 'hide',
        width: 700,
        height: 480,
        layout: 'fit',
        items: [editQuestionForm],
        buttons: [{
            text: 'Save'.l(),
            handler: function(){Poll().saveQuestion();}
        },{
            text: 'Cancel'.l(),
            handler: function(){Ext.getCmp('window-question').hide();}
        }]

    });

});//end onReady

// FIX multiselect anchor
Ext.ux.form.MultiSelect.override({
    onRender: function(ct, position){
        Ext.ux.form.MultiSelect.superclass.onRender.call(this, ct, position);

        var cfg = {
            renderTo: this.el,
            title: this.legend,
            //one more fix remove scroll
            style: "padding:0; background:white; margin-bottom:0px;",
            //
            tbar: this.tbar,
            bodyStyle: 'overflow: auto;',
            height: this.height,
            width: this.width

        };

        // do not apply width if anchor specified
        if (this.anchor){
            cfg.anchor = this.anchor;
            delete cfg.width;
        }

        var fs = this.fs = new Ext.form.FieldSet(cfg);

        fs.body.addClass('ux-mselect');

        this.view = new Ext.ListView({
            multiSelect: true,
            store: this.store,
            columns: [{header: 'Value', width: 1, dataIndex: this.displayField}],
            hideHeaders: true
        });

        fs.add(this.view);

        this.view.on('click', this.onViewClick, this);
        this.view.on('beforeclick', this.onViewBeforeClick, this);
        this.view.on('dblclick', this.onViewDblClick, this);

        this.hiddenName = this.name || Ext.id();
        var hiddenTag = {tag: "input", type: "hidden", value: "", name: this.hiddenName};
        this.hiddenField = this.el.createChild(hiddenTag);
        this.hiddenField.dom.disabled = this.hiddenName != this.name;
        fs.doLayout();
    }
});
//end FIX