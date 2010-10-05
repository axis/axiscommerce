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
    
    var filters = new Ext.ux.grid.GridFilters({
        filters: [
            {type: 'numeric', dataIndex: 'id'},
            {type: 'string', dataIndex: 'customer_email'},
            {type: 'string', dataIndex: 'product_name'},
            {type: 'string', dataIndex: 'tag'}
        ]
    });
    
    var storeTag = new Ext.data.GroupingStore({
        url: Axis.getUrl('tag_index/list'),
        reader: new Ext.data.JsonReader({
                idProperty: '',
                root : 'data',
                totalProperty: 'count'
            },
            ['id', 'product_id', 'product_name', 'tag', 'customer_email', 'customer_id', 'status']
        ),
        sortInfo: {field: 'tag', direction: "ASC"},
        remoteSort: true
    });
    
    function renderCustomer(value, meta, record) {
        if (record.data.customer_id) {
            meta.attr = 'ext:qtip="Open in new window ' + value + '"';
            return String.format(
                '<a href="{1}" class="grid-link-icon user">{0}</a>',
                value,
                Axis.getUrl('customer_index/index/customerId/' + record.data.customer_id)
            );
        } else if (!value) {
            var title = 'Undefined'.l();
            meta.attr = 'ext:qtip="' + title + '"';
            return title;
        }
        return value;
    }
    
    function renderProduct(value, meta, record) {
        meta.attr = 'ext:qtip="Open in new window ' + value + '"';
        var productAction =  Axis.getUrl('catalog_index/index/productId/.productId.');
        return String.format(
            '<a href="{1}" class="grid-link-icon product" target="_blank">{0} </a>',
            value, productAction.replace(/\.productId\./, record.data.product_id));
    }
    function renderStatus(value){
        return statuses[value] ? statuses[value] : value;  
    };
    
    var columnsTag = new Ext.grid.ColumnModel([{
        header: "Id".l(),
        width: 30,
        dataIndex: 'id',
        groupable:false,
        sortable: true
    }, {
        header: "Tag".l(),
        width: 170,
        dataIndex: 'tag',
        groupable:true,
        sortable: true
    }, {
        header: "Product Name".l(),
        id:'product_name',
        width: 145,
        sortable: true,
        dataIndex: 'product_name',
        renderer: renderProduct
    }, {
        header: "Customer".l(),
        width: 170,
        sortable: true,
        dataIndex: 'customer_email',
        renderer: renderCustomer
    }, {
        header: "Status".l(),
        width: 170,
        sortable: true,
        dataIndex: 'status',
        editor: new Ext.grid.GridEditor(new Ext.form.ComboBox({
            triggerAction: 'all',
            transform: 'status-combo',
            lazyRender: true,
            typeAhead: true,
            forceSelection: false,
            editable: false
        })),
        renderer: renderStatus
    }]);
    var changeStatusMenu = new Ext.menu.Menu({
        id: changeStatusMenu,
        items: menuStatus
    })
    gridTag = new Axis.grid.EditorGridPanel({
        id: 'gridTag',
        autoExpandColumn: 'product_name',
        store: storeTag,
        cm: columnsTag,
        view: new Ext.grid.GroupingView({
            emptyText: 'No records found'.l(),
            groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
        }),
        tbar: [{
            text: 'Delete',
            icon: Axis.skinUrl + '/images/icons/delete.png',
            cls: 'x-btn-text-icon',
            handler : remove
        }, {
            text: 'Change Status to'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/menu_action.png',
            tooltip: {text: 'Change status of selected comments', title: 'Change Status'},
            menu: changeStatusMenu
        }, {
            text: 'Save'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler: save
        }, '->', {
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            cls: 'x-btn-icon',
            handler: function() {
                gridTag.getStore().reload();
            }
        }],
        bbar: new Axis.PagingToolbar({
            store: storeTag
        }),
        plugins:[
            filters,
            new Ext.ux.grid.Search({
                mode: 'local',
                iconCls: false,
                dateFormat: 'Y-m-d',
                width: 150,
                minLength: 2
            })
        ]
    });
    
    new Axis.Panel({
        items: [
            gridTag
        ]
    });
    
    function setTag(id) {
       var store = gridWishlist.getStore();
       store.lastOptions = {params:{start:0, limit:25}};
       gridTag.filters.filters.get('id').setValue({'eq': id});
    }
     
    if (typeof(tagId) !== "undefined") {
        setTag(tagId);
    } else {
        storeTag.load({params:{start:0, limit:25}});
    }
     
});


function save() {
    var data = {};
    var modified = Ext.getCmp('gridTag').getStore().getModifiedRecords();
    var length = modified.length;
    if (length < 1) return;

    for (var i = 0; i < length; i++) {
        data[modified[i]['id']] = modified[i]['data'];
    }
    Ext.Ajax.request({
        url: Axis.getUrl('tag_index/save'),
        params: {data: Ext.encode(data)},
        callback: function() {
            Ext.getCmp('gridTag').getStore().commitChanges();
            Ext.getCmp('gridTag').getStore().reload();
        }
    });
}

function remove() {
    var selectedItems = Ext.getCmp('gridTag').getSelectionModel().selections.items;
    if (!selectedItems.length || !confirm('Are you sure?'.l())) {
        return;
    }
    
    var data = {};
    
    for (var i = 0; i < selectedItems.length; i++) {
        data[i] = selectedItems[i].get('id');
    }

    Ext.Ajax.request({
        url: Axis.getUrl('tag_index/delete'),
        params: {data:  Ext.encode(data)},
        callback: function() {
            Ext.getCmp('gridTag').getStore().reload();
        }
    });
}
function setStatus(status) {
    var selected = gridTag.getSelectionModel().getSelections();
    for (var i = 0; i < selected.length; i++) {
        selected[i].set('status', status);
    }
}