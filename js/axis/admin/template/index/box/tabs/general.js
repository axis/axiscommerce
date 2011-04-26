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

var OutputGrid = {

    el: null,

    initialData: [],

    reload: function() {
        OutputGrid.el.store.loadData(OutputGrid.initialData);
    },

    add: function() {
        OutputGrid.el.stopEditing();
        var record = new OutputGrid.el.store.recordType({
            'page_id'       : 1,
            'block'         : '',
            'box_show'      : 1,
            'sort_order'    : '',
            'tab_container' : '',
            'template'      : '',
            'remove'        : 0
        });
        record.markDirty();
        OutputGrid.el.store.insert(0, record);
        OutputGrid.el.startEditing(0, 0);
    },

    clearData: function() {
        OutputGrid.initialData = [];
        OutputGrid.el.store.loadData([]);
    },

    loadData: function(data) {
        OutputGrid.initialData = data.assignments;
        OutputGrid.el.store.loadData(data.assignments);
    },

    getData: function() {
        var records = OutputGrid.el.store.data.items;

        var data = {};
        for (var i = records.length - 1; i >= 0; i--) {
            data[records[i].id] = records[i]['data'];
        }

        return {
            'assignments': data
        };
    }
};

Ext.onReady(function() {

    Box.Window.formFields.push(
        {name: 'box[class]', mapping: 'class'},
        {name: 'box[block]', mapping: 'block'},
        {name: 'box[sort_order]', mapping: 'sort_order'},
        {name: 'box[box_status]', mapping: 'box_status'},
        {name: 'box[id]', mapping: 'id', type: 'int'},
        {name: 'box[template_id]', mapping: 'template_id', type: 'int'}
        //,{name: 'box[config]', mapping: 'config'}
    );

    var ds = new Ext.data.Store({
        mode: 'local',
        pruneModifiedRecords: true,
        reader: new Ext.data.JsonReader({
            idProperty: 'page_id',
            fields: [
                {name: 'page_id', type: 'int'},
                {name: 'box_id', type: 'int'},
                {name: 'block', type: 'string'},
                {name: 'box_show', type: 'int'},
                {name: 'sort_order'},
                {name: 'tab_container', type: 'string'},
                {name: 'template', type: 'string'},
                {name: 'remove', type: 'int'}
            ]
        })
    });

    var status = new Axis.grid.CheckColumn({
        dataIndex: 'box_show',
        header: 'Status'.l(),
        width: 60
    });

    var remove = new Axis.grid.CheckColumn({
        dataIndex: 'remove',
        header: 'Delete'.l(),
        width: 60
    });

    var dsPages = new Ext.data.Store({
        data: Axis.pages,
        reader: new Ext.data.JsonReader({
            idProperty: 'id',
            fields: [
                {name: 'id', type: 'int'},
                {name: 'name'}
            ]
        })
    });

    var useConfigValueRenderer = function(v) {
        if ('' == v || undefined == v) {
            return 'Use Config Value'.l();
        }
        return v;
    };

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true,
            editable: true,
            menuDisabled: true
        },
        columns: [{
            dataIndex: 'page_id',
            header: 'Page'.l(),
            editor: new Ext.form.ComboBox({
                store: dsPages,
                valueField: 'id',
                displayField: 'name',
                triggerAction: 'all',
                mode: 'local',
                listWidth: 220
            }),
            renderer: function(v) {
                var record = dsPages.getById(v);
                if (record) {
                    return record.get('name');
                }
                return v;
            },
            width: 130
        }, status, {
            dataIndex: 'block',
            header: 'Container'.l(),
            editor: new Ext.form.TextField({
                allowBlank: true
            }),
            renderer: useConfigValueRenderer,
            width: 100
        }, {
            dataIndex: 'tab_container',
            header: 'Tab'.l(),
            editor: new Ext.form.TextField({
                allowBlank: true
            }),
            renderer: useConfigValueRenderer,
            width: 100
        }, {
            id: 'template',
            dataIndex: 'template',
            header: 'Template'.l(),
            editor: new Ext.form.TextField({
                allowBlank: true
            }),
            renderer: useConfigValueRenderer
        }, {
            align: 'right',
            dataIndex: 'sort_order',
            header: 'Sort Order'.l(),
            editor: new Ext.form.NumberField({
                allowBlank: true,
                maxValue: 127,
                minValue: -128
            }),
            renderer: useConfigValueRenderer,
            width: 80
        }, remove]
    });

    OutputGrid.el = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'template',
        autoHeight: true,
        //title: 'Output Rules'.l(),
        cm: cm,
        ds: ds,
        sm: new Ext.grid.RowSelectionModel(),
        plugins: [
            remove,
            status
        ],
        tbar: [{
            icon: Axis.skinUrl + '/images/icons/add.png',
            text: 'Add Output Rule'.l(),
            handler: function() {
                OutputGrid.add();
            }
        }, '->', {
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: OutputGrid.reload
        }]
    });

    //Box.Window.addTab(OutputGrid.el, 30);
    Box.Window.dataObjects.push(OutputGrid);

    Box.Window.addTab({
        title: 'General'.l(),
        bodyStyle: 'padding: 10px 0 0 10px',
        defaults: {
            anchor: '-20'
        },
        labelWidth: 150,
        items: [{
            allowBlank: false,
            editable: false,
            fieldLabel: 'Class'.l(),
            typeAhead: true,
            triggerAction: 'all',
            name: 'box[class]',
            hiddenName: 'box[class]',
            store: Axis.boxClasses,
            mode: 'local',
            xtype: 'combo'
        }, {
            allowBlank: false,
            name: 'box[block]',
            fieldLabel: 'Output Container'.l(),
            xtype: 'textfield'
        }, {
            allowBlank: false,
            name: 'box[sort_order]',
            fieldLabel: 'Sort Order'.l(),
            initialValue: 70,
            xtype: 'textfield'
        }, {
            allowBlank: false,
            columns: [100, 100],
            fieldLabel: 'Status'.l(),
            xtype: 'radiogroup',
            value: 1,
            initialValue: 1,
            items: [{
                boxLabel: 'Enabled'.l(),
                checked: true,
                name: 'box[box_status]',
                inputValue: 1
            }, {
                boxLabel: 'Disabled'.l(),
                name: 'box[box_status]',
                inputValue: 0
            }]
        }, {
            allowBlank: true,
            initialValue: 0,
            name: 'box[id]',
            xtype: 'hidden'
        }, {
            allowBlank: false,
            name: 'box[template_id]',
            xtype: 'hidden'
        }/*, {
            name: 'box[config]',
            xtype: 'textfield'
        }*/, OutputGrid.el]
    }, 10);
});
