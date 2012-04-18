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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

Ext.onReady(function () {

    Ext.QuickTips.init();

    var dataStore = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('poll/list')
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, [{name: 'id'},
            {name: 'changed_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
            {name: 'created_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
            {name: 'question'},
            {name: 'status'},
            {name: 'sites'},
            {name: 'type'},
            {name: 'cnt'}]
        ),
        pruneModifiedRecords: true
    });

    var actions = new Ext.ux.grid.RowActions({
        header:'Actions'.l(),
        actions:[{
            iconCls:'icon-details'
            ,tooltip:'Results'.l()
        },{
            iconCls:'icon-edit'
            ,tooltip:'Edit'.l()
        },{
            iconCls:'icon-delete'
            ,tooltip:'Delete'.l()
        }]
        ,callbacks: {
            'icon-details':function(grid, record, action, row, col) {
                Poll().getResults(record.json);
            },
            'icon-edit':function(grid, record, action, row, col) {
                Poll().editQuestion(record.json.id);
            },
            'icon-delete':function(grid, record, action, row, col) {
                Poll().remove(record.json.question, record.json.id);
            }
        }
    });

    var status = new Axis.grid.CheckColumn({
        header: "Status".l(),
        dataIndex: 'status',
        width: 60
    });

    var storeSites = new Ext.data.JsonStore({
        storeId: 'storeSites',
        fields: ['id', 'name'],
        data: sites
    });

    var columnsModel = new Ext.grid.ColumnModel([
        {
            header: 'Id'.l(),
            dataIndex: 'id',
            width: 40
        }, {
            header: "Question".l(),
            sortable: true,
            dataIndex: 'question',
            id: 'question'
        },
        status,
        {
            header: "Sites".l(),
            sortable: true,
            dataIndex: 'sites',
            width:200,
            editor: new Ext.ux.Andrie.Select({
                fieldLabel:  'Field',
                multiSelect: true,
                store: storeSites,
                valueField: 'id',
                displayField: 'name',
                triggerAction: 'all',
                mode: 'local',
                beforeBlur : Ext.emptyFn
            }),
            renderer: function(value, meta) {
                if (typeof(value) == 'undefined' || value == '') {
                    return 'None';
                }
                var ret = new Array();
                value = value.split(',');
                for (var i = 0, n = value.length; i < n; i++) {
                    if (value[i] != '') {

                        ret.push(storeSites.getById(value[i]).data.name);
                    }
                }
                ret = ret.join(', ');
                return ret;
            }
        }, {
            header: "Type".l(),
            sortable: true,
            dataIndex: 'type',
            editor: new Ext.form.ComboBox({
                triggerAction: 'all',
                displayField: 'value',
                typeAhead: true,
                mode: 'local',
                valueField: 'id',
                store: new Ext.data.SimpleStore({
                    fields: ['id', 'value'],
                    data: [['0', 'Singleselect'.l()], ['1', 'Multiselect'.l()]]
                })
            }),
            renderer: function(value, meta) {
                return value == '1' ? 'Multiselect'.l() : 'Singleselect'.l();
            },
            width: 150
        }, {
            header: "Created".l(),
            sortable: true,
            dataIndex: 'created_at',
            renderer: function(v) {
                return Ext.util.Format.date(v);
            },
            width: 100
        }, {
            header: "Modified".l(),
            dataIndex: 'changed_at',
            renderer: function(v) {
                return Ext.util.Format.date(v);
            },
            width: 100
        }, {
            header: "Votes".l(),
            dataIndex: 'cnt',
            width: 80
        }, actions
    ]);

    var gridQuestion = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'question',
        ds: dataStore,
        cm: columnsModel,
        id: 'grid-poll',
        plugins:[status, actions],
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
            handler : Poll().addQuestion
        },{
            text: 'Save'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler: Poll().batchSave
        },{
            text: 'Delete'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: Poll().removeBatch
        },{
            text: 'Clear Voted '.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/drive_delete.png',
            handler: Poll().clearVoted
        },'->',{
            text: 'Reload'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: function() { Ext.getCmp('grid-poll').getStore().reload();}
        }]
    });

    gridQuestion.on('rowdblclick', function(grid, index){
        Poll().editQuestion(grid.getStore().getAt(index).id);
    });

    new Axis.Panel({
        items: [
            gridQuestion
        ]
    })

    dataStore.load();
});