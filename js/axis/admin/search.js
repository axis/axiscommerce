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

Ext.onReady(function (){
    Ext.QuickTips.init(); 
    var menuTime = today;   
    
    var filters = new Ext.ux.grid.GridFilters({
        filters: [
            {type: 'numeric', dataIndex: 'id'},
            {type: 'numeric', dataIndex: 'customer_id'},
            {type: 'numeric', dataIndex: 'num_results'},
            {type: 'numeric', dataIndex: 'hit'},
            {type: 'string', dataIndex: 'customer_email'},
            {type: 'string', dataIndex: 'query'},
            {type: 'string', dataIndex: 'session_id'},
            {type: 'date',   dataIndex: 'created_at', dateFormat: 'Y-m-d'}
        ]
    });
    
    var storeSearch = new Ext.data.GroupingStore({
        proxy: new Ext.data.HttpProxy({
            url: Axis.getUrl('search/list')
        }),
        reader: new Ext.data.JsonReader({
                root : 'data',
                totalProperty: 'count',
                id: 'id'
            },
            ['id', 'num_results', 'hit', 'session_id', 'created_at', 'query', 'customer_email', 'customer_id']
        ),
        sortInfo: {field: 'created_at', direction: "DESC"},
        remoteSort: true,
        groupField:'query'
    });
    
    function renderCustomer(value, meta, record) {
        var link = (Axis.getUrl('customer_index/index/customerId/.customerId.'))
            .replace(/\.customerId\./, record.data.customer_id);
        if (value == null) {
            value = ' Guest';
            link = '#';
        }
        
        meta.attr = 'ext:qtip="goto ' + value + '"';
        return String.format(
                '<a href="{1}" class="grid-link-icon user" target="_blank" >{0}</a>',
                value, link);
    } 
    var selectModel = new Ext.grid.CheckboxSelectionModel();
   
    var columnsSearch = new Ext.grid.ColumnModel([{
        header: "Id".l(), 
        width: 30, 
        sortable: true, 
        dataIndex: 'id',
        groupable:false
    }, {
        header: "Query".l(), 
        width: 300,
        sortable: true, 
        dataIndex: 'query'
    }, {
        header: "Results".l(), 
        id:'product_name',
        sortable: true,
        dataIndex: 'num_results'
    }, {
        header: "Customer".l(), 
        width: 170, 
        sortable: true,
        dataIndex: 'customer_email',
        renderer: renderCustomer
    }, {
        header: "Hit".l(), 
        width: 30, 
        dataIndex: 'hit',
        sortable: true, 
        groupable:false
    }, {
        header: "Created On".l(), 
        width: 145, 
        sortable: true,
        dataIndex: 'created_at',
        groupable:false
    }]); 
       
    gridSearch = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'product_name',
        store: storeSearch,
        cm: columnsSearch,
        view: new Ext.grid.GroupingView({
            emptyText: 'No records found'.l(),
            groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
        }),
        plugins:[
            filters,
            new Ext.ux.grid.Search({
                mode:'local',
                iconCls:false,
                dateFormat:'Y-m-d',
                width: 150,
                minLength:2
            })
        ],
        tbar: [{
            text: 'Fast Filter'.l(),
            menu : {
                items: [{
                    text: 'Time Options'.l(),
                    checked: false,
                    checkHandler: function(menuItem, chekced) {
                        setFilterDate('created_at', 'gt', menuTime, chekced);
                    },
                    menu: {
                        items:[{
                            text: 'Today ({count})'.l('core', countToday), 
                            group: 'time', 
                            checked: false, 
                            checkHandler: function(menuItem, cheked) { 
                                if (cheked) menuTime = today;
                            }
                        }, {
                            text: 'Last week ({count})'.l('core', countWeek), 
                            group: 'time', 
                            checked: false, 
                            checkHandler: function(menuItem, cheked) { 
                                 if (cheked) menuTime = week;
                             }
                        }, {
                            text: 'Last month ({count})'.l('core', countMonth), 
                            group: 'time',  
                            checked: false ,
                            checkHandler: function(menuItem, cheked){
                                if (cheked) menuTime = month;
                            }
                        }]
                    }
                }, {
                    text: 'Empty results ({count})'.l('core', countNull),
                    checked: false,
                    checkHandler: function(menuItem, checked) {
                        setFilterNumeric('num_results', 'eq', 0, checked);
                    }
                }, '-', {
                    text: 'Reset filter'.l(),
                    handler:resetFilters
                }
            ]}
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler :function() {
                if (!confirm('Are you sure?'.l())) {
                    return;
                }
                var data = {};
                var selectedItems = gridSearch.getSelectionModel().selections.items;
                for (var i = 0; i < selectedItems.length; i++) {
                    if (!selectedItems[i]['data']['id']) continue;
                    data[i] = selectedItems[i]['data']['id'];
                }
                    
                Ext.Ajax.request({
                    url: Axis.getUrl('search/delete'),
                    params: {data: Ext.encode(data)},
                    callback: function() {
                        storeSearch.reload();
                    }
                });
               } 
        }, '->', {
            text: 'Reload'.l(),
            handler: function() {
                gridSearch.getStore().reload();
            },
            iconCls: 'btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/refresh.png'
        }],
        bbar: new Axis.PagingToolbar({
            store: storeSearch
        })
    });
    
    new Axis.Panel({
        items: [
            gridSearch
        ]
    });
    
    function setSearchQuery(id) {
        var store = gridSearch.getStore();
        store.lastOptions = {params:{start:0, limit:25}};
        gridSearch.filters.filters.get('id').setActive(false);
        gridSearch.filters.filters.get('id').setValue({'eq': id});
        gridSearch.filters.filters.get('id').setActive(true);
    }
    
    function setCustomer(id) {
        var store = gridSearch.getStore();
        store.lastOptions = {params:{start:0, limit:25}};
        gridSearch.filters.filters.get('customer_id').setActive(false);
        gridSearch.filters.filters.get('customer_id').setValue({'eq': id});
        gridSearch.filters.filters.get('customer_id').setActive(true);
    }
    
    gridSearch.render();
    
    if (typeof(searchId) !== "undefined")
        setSearchQuery(searchId);
    else { 
        if (typeof(customerId) !== "undefined")
            setCustomer(customerId);
        else {
            storeSearch.load({params:{start:0, limit:25}});
        }
    }
});
function resetFilters() {
     gridSearch.filters.clearFilters();
}
// only for filter with type numeric
function setFilterNumeric(field, condition, val, checked) {
    var store = gridSearch.getStore();
    store.lastOptions = {params:{start:0, limit:25}};
    gridSearch.filters.filters.get(field).setActive(false);  
    switch(condition) {
         case 'lt':
             gridSearch.filters.filters.get(field).setValue({'lt': val});
             break;    
         case 'gt':
             gridSearch.filters.filters.get(field).setValue({'gt': val});
             break;
         default:
             gridSearch.filters.filters.get(field).setValue({'eq': val}); 
    }
    gridSearch.filters.filters.get(field).setActive(checked);
}
// only for filter with type date
//@params field name of colunm see dataIndex, 
function setFilterDate(field, condition, val, checked) {
    var store = gridSearch.getStore();
    store.lastOptions = {params:{start:0, limit:25}};
    gridSearch.filters.filters.get(field).setActive(false);
    var dt = new Date();
    dt = Date.parseDate(val, "Y-m-d");
    switch(condition) {
        case 'lt':
            gridSearch.filters.filters.get(field).setValue({'before': dt});
            break;    
        case 'gt':
            gridSearch.filters.filters.get(field).setValue({'after': dt});
            break;
        default:
            gridSearch.filters.filters.get(field).setValue({'on': dt}); 
    }
    gridSearch.filters.filters.get(field).setActive(checked);
}
// only for filter with type string
function setFilterString(field, val, checked) {
    var store = gridSearch.getStore();
    store.lastOptions = {params:{start:0, limit:25}};
    gridSearch.filters.filters.get(field).setActive(false);
    gridSearch.filters.filters.get(field).setValue(val); // val is a string 
    gridSearch.filters.filters.get(field).setActive(checked);
}


