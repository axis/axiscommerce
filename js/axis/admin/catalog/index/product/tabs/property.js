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

var PropertyGrid = {
    
    /**
     * @property {Axis.grid.GridPanel} el
     */
    el: null,
    
    /**
     * @param {Array} records Ext.data.Record
     */
    add: function(records) {
        Ext.each(records, function(r) {
            if (r.get('input_type') == 0
                || r.get('input_type') == 2
                || r.get('input_type') == 3) {
                
                return;
            }
            
            if (r.get('input_type') == 1 //string
                 || r.get('input_type') == 4 // textarea
                 || r.get('input_type') == 5) { // file
                
                var itemName = r.get('input_type') == 5 ? 'file' : 'text';
                itemName += r.get('languagable') ? '-l' : '';
                
                var propertyDetailsWindow = new Axis.PropertyDetails({
                    item: itemName
                });
                propertyDetailsWindow.on('okpress', function(value) {
                    if (typeof value === 'object') {
                        value = Ext.encode(value);
                    }
                    delete (r.data.id);
                    var record = PropertyGrid.el.store.getAt(
                        PropertyGrid.el.store.find('option_id', r.get('option_id'))
                    );
                    
                    if (record) {
                        record.set('option_value_id', 0);
                        record.set('value_name', value);
                        record.set('remove', 0);
                    } else {
                        var record = new PropertyGrid.el.store.recordType(r.data);
                        record.set('option_id', r.get('option_id'));
                        record.set('option_value_id', 0);
                        record.set('value_name', value);
                        record.set('remove', 0);
                        record.markDirty();
                        PropertyGrid.el.store.modified.push(record);
                        PropertyGrid.el.store.insert(0, record);
                    }
                });
                propertyDetailsWindow
                    .setTitle('Value of {property}'.l('core', r.get('option_name')))
                    .show();
            } else {
                delete (r.data.id);
                var record = PropertyGrid.el.store.getAt(
                    PropertyGrid.el.store.find('option_id', r.get('option_id'))
                );
                
                if (record) {
                    if (record.get('option_value_id') == r.get('value_id')) {
                        return;
                    }
                    record.set('option_value_id', r.get('value_id'));
                    record.set('value_name', r.get('value_name'));
                    record.set('remove', 0);
                } else {
                    var record = new PropertyGrid.el.store.recordType(r.data);
                    record.set('option_id', r.get('option_id'));
                    record.set('option_value_id', r.get('value_id'));
                    record.set('remove', 0);
                    record.markDirty();
                    PropertyGrid.el.store.modified.push(record);
                    PropertyGrid.el.store.insert(0, record);
                }
            }
        });
    },
    
    edit: function(record) {
        if (record.get('option_value_id')) {
            if (!ProductWindow.attributeWindow) {
                ProductWindow.attributeWindow = new Axis.AttributeWindow();
            }
            ProductWindow.attributeWindow.purgeListeners();
            ProductWindow.attributeWindow.on('okpress', PropertyGrid.add);
            var optionId = record.get('option_id');
            ProductWindow.attributeWindow
                .setFilter(function(r) {
                    return r.get('option_id') === optionId;
                })
                .setSingleSelect(true)
                .show();
        } else {
            var itemName = record.get('input_type') == 5 ? 'file' : 'text';
            itemName += record.get('languagable') ? '-l' : '';
            var propertyDetailsWindow = new Axis.PropertyDetails({
                item: itemName,
                value: record.get('languagable') ? 
                    Ext.decode(record.get('value_name')) : record.get('value_name')
            });
            propertyDetailsWindow.on('okpress', function(value) {
                if (typeof value === 'object') {
                    value = Ext.encode(value);
                }
                PropertyGrid.el.store
                    .getAt(PropertyGrid.el.store.find('option_id', record.get('option_id')))
                    .set('value_name', value);
            });
            propertyDetailsWindow
                .setTitle('Value of {property}'.l('core', record.get('option_name')))
                .show();
        }
    },
    
    clearData: function() {
        PropertyGrid.el.store.loadData([]);
    },
    
    loadData: function(data) {
        PropertyGrid.el.store.loadData(data.properties);
    },
    
    getData: function() {
        var modified = PropertyGrid.el.store.getModifiedRecords();
        
        if (!modified.length) {
            return;
        }
        
        var data = {};
        for (var i = modified.length - 1; i >= 0; i--) {
            data[modified[i]['id']] = modified[i]['data'];
        }
        
        return {
            'property': data
        };
    }
}

Ext.onReady(function() {
    
    var ds = new Ext.data.Store({
        mode: 'local',
        pruneModifiedRecords: true,
        reader: new Ext.data.JsonReader({
            idProperty: 'id',
            fields: [
                {name: 'id', type: 'int'},
                {name: 'input_type', type: 'int'},
                {name: 'languagable', type: 'int'},
                {name: 'value_name'},
                {name: 'option_name'},
                {name: 'option_id', type: 'int'},
                {name: 'option_value_id', type: 'int'},
                {name: 'remove', type:'int'}
            ]
        })
    });
    
    var remove = new Axis.grid.CheckColumn({
        dataIndex: 'remove',
        header: 'Delete'.l(),
        width: 60
    });
    
    var cm = new Ext.grid.ColumnModel({
        defaults: {
            sortable: true,
            menuDisabled: true
        },
        columns: [{
            dataIndex: 'id',
            header: 'Id'.l(),
            width: 40
        }, {
            dataIndex: 'option_name',
            header: 'Property'.l(),
            width: 200
        }, {
            id: 'value-name',
            dataIndex: 'value_name',
            header: 'Value'.l()
        }, remove]
    });
    
    PropertyGrid.el = ProductWindow.propertyGrid = new Axis.grid.GridPanel({
        autoExpandColumn: 'value-name',
        border: false,
        cm: cm,
        ds: ds,
        id: 'grid-product-window-property-list',
        massAction: false,
        plugins: [remove],
        title: 'Properties'.l(),
        listeners: {
            rowdblclick: function(grid, index, e) {
                PropertyGrid.edit(grid.store.getAt(index));
            }
        },
        tbar: [{
            icon: Axis.skinUrl + '/images/icons/add.png',
            text: 'Add'.l(),
            handler: function() {
                if (!ProductWindow.attributeWindow) {
                    ProductWindow.attributeWindow = new Axis.AttributeWindow();
                }
                ProductWindow.attributeWindow.purgeListeners();
                ProductWindow.attributeWindow.on('okpress', PropertyGrid.add);
                ProductWindow.attributeWindow
                    .clearFilter()
                    .setSingleSelect(true)
                    .show();
            }
        }]
    });
    
    ProductWindow.addTab(PropertyGrid.el, 70);
    ProductWindow.dataObjects.push(PropertyGrid);
    
});
