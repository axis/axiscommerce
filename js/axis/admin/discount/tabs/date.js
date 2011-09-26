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

//Ext.onReady(function() {
    var fields = [
        {name: 'condition', type: 'string'},
        {name: 'value',     type: 'string'}
    ];

    var record = Ext.data.Record.create(fields);

    var ds = new Ext.data.Store();
    
    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true
        },
        columns: [{
            header: 'Condition'.l(),
            dataIndex: 'condition',
            id: 'condition',
            editor: new Ext.form.ComboBox({
                triggerAction: 'all',
                displayField: 'value',
                typeAhead: true,
                mode: 'local',
                valueField: 'id',
                store: new Ext.data.SimpleStore({
                    fields: ['id', 'value'],
                    data: [
                        ['equals', 'equals'.l()], 
                        ['greate', 'greate'.l()], 
                        ['less', 'less'.l()]
                    ]
                })
            })
        }, {
            header: 'Value'.l(),
            dataIndex: 'value',
            id: 'value',
            editor: new Ext.form.DateField({
                allowBlank: false
//                maxLength: 128
            })
        }]
    });
    
    var grid = new Axis.grid.EditorGridPanel({
        title: 'Data'.l(),
        cm: cm,
        store: ds,
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler : function() {
                discountWindowFormDateTab.add('equals', 0);
            }
        }],
        massAction: false
    });
    
    discountWindowFormDateTab = {
        el: grid,
        onLoad: function(data) {
            this.el.getStore().removeAll();
            Ext.each(data.equals, function(value) {
                this.add('equals', value);
            }, this);
            Ext.each(data.greate, function(value) {
                this.add('greate', value);
            }, this);
            Ext.each(data.less, function(value) {
                this.add('less', value);
            }, this);
        },
        add: function(condition, value) {
            condition = condition || 'equals';
            value = value || 0;
            
            var grid = this.el;
            grid.stopEditing();
            var row = new record({
               'condition' : condition,
               'value'     : value
            });
            grid.getStore().insert(0, row);
            grid.startEditing(0, 1);
        }  
    }
//});