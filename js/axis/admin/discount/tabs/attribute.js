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

var attributeTab = {
    el: null,
    getSelected: function(data){
        data.optionId = [];
        
        this.el.store.each(function(r){
            if (1 !== r.get('check')) {
                return;
            }
            if (null == r.get('parent')) {
                return;
            }
            var optionId = r.get('option_id')
                
            var key = '', ret = true;
            for (key in data.optionId) {
                if (data.optionId[key] == optionId) {                
                    ret = false;
                }
            }
            if (ret) {
                data.optionId.push(optionId);
            }

            if ('undefined' == typeof data['option[' + optionId + ']']) {
                data['option[' + optionId + ']'] = [];
            }
            data['option[' + optionId + ']'].push(r.get('value_id'));
        });
        return data;
    },
    clear: function(){
        var store = this.el.store;
        store.each(function(r) {
            r.set('check', 0);
            r.commit();
        });
        store.collapseAll();
    },
    setData: function(data){
        var store = this.el.store;
        this.clear();
        if ('undefined' == typeof data.optionId) {
            return;
        }
        
        Ext.each(data.optionId, function(optionId) {
            var valueIds;
            if (!(valueIds = data['option[' + optionId + ']'])) {
                return;
            }
            Ext.each(valueIds, function(valueId) {
                
                var r;
                if (!(r = store.getById(optionId + '_' + valueId))) {
                    return;
                }
                
                r.set('check', 1);
                r.commit();
        
                while ((r = store.getNodeParent(r))) {
                    store.expandNode(r);
                }                
                
            });
        });
    }
}

Ext.onReady(function() {
    
    var ds = new Ext.ux.maximgb.tg.AdjacencyListStore({
        autoLoad: true,
        mode: 'local',
        reader: new Ext.data.JsonReader({
            idProperty: 'id'
        }, [
            {name: 'id'}, // this is not integer
            {name: 'leaf'},
            {name: 'text'},
            {name: 'code'},
            {name: 'option_code'},
            {name: 'option_name'},
            {name: 'value_name'},
            {name: 'input_type',  type: 'int'},
            {name: 'languagable', type: 'int'},
            {name: 'option_id',   type: 'int'},
            {name: 'value_id',    type: 'int'},
            {name: 'parent'},
            {name: 'check'}
        ]),
        leaf_field_name: 'leaf',
        parent_id_field_name: 'parent',
        url: Axis.getUrl('catalog/product-option/nlist')
    });

    var columnChecked = new Axis.grid.CheckColumn({
        dataIndex: 'check',
        width: 100
    });

    var cm = new Ext.grid.ColumnModel({
        columns: [{
            dataIndex: 'text',
            header: 'Name'.l(),
            id: 'text'
        }, columnChecked]
    });

    attributeTab.el = new Axis.grid.GridTree({
        title: 'Option'.l(),
        deferRowRender: false,
        autoExpandColumn: 'text',
        ds: ds,
        cm: cm,
        enableDragDrop: false,
        master_column_id: 'text',
        massAction: false,
        region: 'center',
        plugins: [columnChecked]
    });
});