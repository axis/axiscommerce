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

var discountWindowFormOptionTab = {
    el: null,
    onLoad: function(data){
        var store = this.el.store;
            
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
                
                r.set('checked', 1);
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
            {name: 'checked'}
        ]),
        leaf_field_name: 'leaf',
        parent_id_field_name: 'parent',
        url: Axis.getUrl('catalog/product-option/nlist')
    });

    var columnChecked = new Axis.grid.CheckColumn({
        dataIndex: 'checked',
        header: 'Checked'.l(),
        width: 100
    });

    var cm = new Ext.grid.ColumnModel({
        columns: [{
            dataIndex: 'text',
            header: 'Name'.l(),
            id: 'text'
        }, columnChecked]
    });

    discountWindowFormOptionTab.el = new Axis.grid.GridTree({
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