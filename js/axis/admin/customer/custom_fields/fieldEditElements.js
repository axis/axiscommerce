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
    
    validatorStore = new Ext.data.Store({
        url: Axis.getUrl('account/field/get-validator'),
        reader: new Ext.data.ArrayReader({
            id: 1
        }, [{
            name: 'key',
            mapping: 1
        }, {
            name: 'value',
            mapping: 2
        }]),
        autoLoad: true
    })
    
    var validatatorCombo = new Ext.form.ComboBox({
        id: 'validator-combo',
        name: 'validator',
        typeAhead: true,
        triggerAction: 'all',
        lazyRender: true,
        mode: 'local',
        valueField: 'key',
        displayField: 'value',
        forceSelection: false,
        triggerAction: 'all',
        store: validatorStore
    });
    
    var value = Ext.data.Record.create([
        {name: 'id'},
        {name: 'text'}
    ]);
    var emptyRecord = new value({
        id: '',
        text: 'None'.l()
    });
    
    vss = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            method: 'get',
            url: Axis.getUrl('account/field/get-value-sets')
        }),
        reader: new Ext.data.JsonReader({id: 'id'}, value),
        autoLoad: true
    });
    vss.on('load', function(){
        vss.add(emptyRecord);
    })
    
    valueSetCombo = new Ext.form.ComboBox({
        id: 'value-set-combo',
        triggerAction: 'all',
        displayField: 'text',
        store: vss,
        forceSelection: false,
        lazyRender: true,
        valueField: 'id'   
    })
    
    gs = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            method: 'get',
            url: Axis.getUrl('account/field-group/list')
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
            },[
               {name: 'id'},{name: 'title'}
            ]
        ),
        autoLoad: true
    });
    
    var groupCombo = new Ext.form.ComboBox({
        id: 'group-combo',
        triggerAction: 'all',
        displayField: 'title',
        typeAhead: true,
        store: gs,
        mode: 'local',
        lazyRender: true,
        valueField: 'id'   
    })
    
    typeStore = new Ext.data.Store({
        url: Axis.getUrl('account/field/get-type'),
        reader: new Ext.data.ArrayReader({
            id: 1
        }, [{
            name: 'key',
            mapping: 1
        }, {
            name: 'value',
            mapping: 2
        }]),
        autoLoad: true
    })
    
    var typeCombo = new Ext.form.ComboBox({
        id: 'type-combo',
        typeAhead: true,
        triggerAction: 'all',
        lazyRender: true,
        mode: 'local',
        valueField: 'key',
        displayField: 'value',
        forceSelection: false,
        store: typeStore
    })
})