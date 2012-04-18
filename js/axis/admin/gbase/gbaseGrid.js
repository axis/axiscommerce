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

Ext.onReady(function(){

    Ext.DomHelper.insertFirst(Ext.get('targetCountry'), '<option value="">All</option>')

    var gbaseStore = new Ext.data.GroupingStore({
        url: Axis.getUrl('google-base/load'),
        reader: new Ext.data.JsonReader({
            id: 'id',
            totalProperty: 'total_count',
            root: 'feed'
            }, [
                {name: 'local_id', type: 'int'},
                {name: 'title', type: 'string'},
                {name: 'quantity', type: 'int'},
                {name: 'price', type: 'string'},
                {name: 'status', type: 'string'},
                {name: 'impressions', type: 'int'},
                {name: 'clicks', type: 'int'},
                {name: 'page_views', type: 'int'},
                {name: 'expiration_date', type: 'date', dateFormat: 'Y-m-d'},
                {name: 'modification_time', type: 'date', dateFormat: 'Y-m-d'},
                {name: 'language'},
                {name: 'site_id'},
                {name: 'currency', type: 'string'},
                {name: 'id'}
            ]
        ),
        sortInfo:{field: 'modification_time', direction: "DESC"},
        remoteSort: true
    })

    var sm = new Ext.grid.CheckboxSelectionModel();

    function statsRenderer(value) {
        return value ? value : '0';
    }

    var cm = new Ext.grid.ColumnModel([
        sm, {
            header: "Id".l(),
            dataIndex: 'local_id',
            width: 30,
            menuDisabled: true
        }, {
            header: "Title".l(),
            dataIndex: 'title',
            width: 200,
            sortable: false,
            menuDisabled: true
        }, {
            header: "Q-ty".l(),
            dataIndex: 'quantity',
            width: 60,
            sortable: false,
            menuDisabled: true
        },{
            header: "Price".l(),
            dataIndex: 'price',
            width: 80
        }, {
            header: 'Status'.l(),
            dataIndex: 'status',
            width: 60,
            sortable: false,
            menuDisabled: true
        }, {
            header: 'Impr'.l(),
            dataIndex: 'impressions',
            width: 40,
            renderer: statsRenderer,
            sortable: false,
            menuDisabled: true
        }, {
            header: 'Clicks'.l(),
            dataIndex: 'clicks',
            width: 50,
            renderer: statsRenderer,
            sortable: false,
            menuDisabled: true
        }, {
            header: 'Page views'.l(),
            dataIndex: 'page_views',
            width: 60,
            renderer: statsRenderer,
            sortable: false,
            menuDisabled: true
        }, {
            header: 'Expires'.l(),
            width: 80,
            renderer: Ext.util.Format.dateRenderer('Y-m-d'),
            dataIndex: 'expiration_date',
            sortable: false,
            menuDisabled: true
        }, {
            header: 'Modified'.l(),
            width: 80,
            renderer: Ext.util.Format.dateRenderer('Y-m-d'),
            dataIndex: 'modification_time'
        }
    ]);
    cm.defaultSortable = true;

    gbaseGrid = new Ext.grid.GridPanel({
        id: 'grid-gbase-list',
        trackMouseOver: false,
        border: false,
        ds: gbaseStore,
        stripeRows: true,
        cm: cm,
        sm: sm,
        title: 'Gbase Data'.l(),
        view: new Ext.grid.GroupingView({
            forceFit: true,
            emptyText: 'No records found'.l(),
            showGroupName: false,
            enableNoGroups: true,
            enableGroupingMenu: true,
            hideGroupedColumn: true,
            enableRowBody: false,
            groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
            getRowClass: function(row, index) {
                var cls = '';
                var data = row.data;
                switch (data.status) {
                    case 'published':
                        cls = 'gbase-item-published';
                        break;
                    case 'draft':
                        cls = 'gbase-item-draft';
                        break;
                    case 'disapproved':
                        cls = 'gbase-item-disapproved';
                        break;
                }
                return cls;
            }
        }),
        autoScroll: true,
        bbar: new Axis.PagingToolbar({
            id: 'paging-toolbar-gbase',
            store: gbaseStore
        }),
        tbar: [{
            iconCls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/accept.png',
            text: 'Update items'.l(),
            disabled: true,
            handler: updateItem
        }, {
            iconCls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/tick_inactive.png',
            text: 'Draft'.l(),
            handler: function(){
                setDraft('yes');
            }
        }, {
            iconCls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/tick.png',
            text: 'Publish'.l(),
            handler: function(){
                setDraft('no');
            }
        }, {
            iconCls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/delete.png',
            text: 'Delete'.l(),
            handler: deleteItem
        },'->',
        new Ext.Toolbar.TextItem('Target country  '),
        new Ext.Toolbar.Item('targetCountry'),
            {
            iconCls: 'x-btn-icon',
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: function(){
                gbaseGrid.getStore().reload();
            }
        }]
    });

    gbaseGrid.getStore().on('beforeload', function(store, options) {
        var field = store.getSortState().field;
        var index = gbaseGrid.getColumnModel().findColumnIndex(field);

        if (typeof(options.params) != 'undefined') {
            options.params.country = Ext.getDom('targetCountry').value;
            options.params.sortType = store.fields.items[index-1].type;
        } else {
            store.baseParams = {
                country: Ext.getDom('targetCountry').value,
                sortType: store.fields.items[index-1].type
            }
        }
    })

    Ext.EventManager.addListener(Ext.getDom('targetCountry'), 'change', function(){
        var country = this.selectedIndex;
        var index = gbaseGrid.getColumnModel().findColumnIndex('price');

        if (country == '0')
            gbaseGrid.getColumnModel().config[index].menuDisabled = true;
        else
            gbaseGrid.getColumnModel().config[index].menuDisabled = false;

        gbaseGrid.getStore().reload();
    })

})

function updateItem(){
    var selectedItems = gbaseGrid.getSelectionModel().selections.items;

    if (selectedItems.length < 1)
        return;

    var data = {};

    for (var i = 0; i < selectedItems.length; i++) {
        data[i] = {};
        data[i]['id'] = selectedItems[i].data.id;
        data[i]['local_id'] = selectedItems[i].data.local_id;
        data[i]['site'] = selectedItems[i].data.site_id;
        data[i]['language'] = selectedItems[i].data.language;
        data[i]['currency'] = selectedItems[i].data.currency;
    }
    var items = Ext.encode(data);

    ajaxUpdate(items, 1);
}

function deleteItem(){
    var selectedItems = gbaseGrid.getSelectionModel().selections.items;

    if (selectedItems.length < 1)
        return;

    if (!confirm('Delete Item(s)?'))
        return;

    var data = {};

    for (var i = 0; i < selectedItems.length; i++) {
        data[i] = selectedItems[i].id;
    }
    var items = Ext.encode(data);

    ajaxDelete(items, 1)
}

function setDraft(draft) {
    var selectedItems = gbaseGrid.getSelectionModel().selections.items;

    if (selectedItems.length < 1)
        return;

    var data = {};
    var params = {};

    for (var i = 0; i < selectedItems.length; i++) {
        data[i] = selectedItems[i].id;
    }

    params['items'] = Ext.encode(data);
    params['draft'] = draft;

    ajaxSetDraft(params, 1);
}

function ajaxUpdate(items, clearSession){

    if (clearSession) {
        Ext.getCmp('extProgressBar').clear();
        Ext.getCmp('extProgressBar').updateText('Initializing...');
        Ext.get('lightbox-info').show();
    }

    Ext.Ajax.request({
        url: Axis.getUrl('google-base/update'),
        method: 'post',
        params: {
            items: items,
            clearSession: clearSession
        },
        callback: function(options, success, response){
            if (success) {
                var obj = Ext.util.JSON.decode(response.responseText);
                Ext.getCmp('extProgressBar').updateProgress(obj.processed/obj.count, 'Updated ' + obj.processed + ' of ' + obj.count);
                if (!obj.finalize) {
                    ajaxUpdate(items, 0);
                } else {
                    Ext.get('lightbox-info').hide();
                    gbaseGrid.getStore().reload();
                }
            } else {
                Ext.getCmp('extProgressBar').updateText('An error has been occured. Connecting...');
                ajaxUpdate(items, 0);
            }
        }
    })
}

function ajaxDelete(items, clearSession){

    if (clearSession) {
        Ext.getCmp('extProgressBar').clear();
        Ext.getCmp('extProgressBar').updateText('Initializing...');
        Ext.get('lightbox-info').show();
    }

    Ext.Ajax.request({
        url: Axis.getUrl('google-base/remove'),
        method: 'post',
        params: {
            items: items,
            clearSession: clearSession
        },
        callback: function(options, success, response){
            if (success) {
                var obj = Ext.util.JSON.decode(response.responseText);
                Ext.getCmp('extProgressBar').updateProgress(obj.processed/obj.count, 'Deleted ' + obj.processed + ' of ' + obj.count);
                if (!obj.finalize) {
                    ajaxDelete(items, 0);
                } else {
                    Ext.get('lightbox-info').hide();
                    gbaseGrid.getStore().reload();
                }
            } else {
                Ext.getCmp('extProgressBar').updateText('An error has been occured. Connecting...');
                ajaxDelete(items, 0);
            }
        }
    })
}

function ajaxSetDraft(params, clearSession){

    if (clearSession) {
        Ext.getCmp('extProgressBar').clear();
        Ext.getCmp('extProgressBar').updateText('Initializing...');
        Ext.get('lightbox-info').show();
    }

    Ext.Ajax.request({
        url: Axis.getUrl('google-base/set-status'),
        method: 'post',
        params: {
            items: params['items'],
            draft: params['draft'],
            clearSession: clearSession
        },
        callback: function(options, success, response){
            if (success) {
                var obj = Ext.util.JSON.decode(response.responseText);
                Ext.getCmp('extProgressBar').updateProgress(obj.processed/obj.count, 'Updated ' + obj.processed + ' of ' + obj.count);
                if (!obj.finalize) {
                    ajaxSetDraft(params, 0);
                } else {
                    Ext.get('lightbox-info').hide();
                    gbaseGrid.getStore().reload();
                }
            } else {
                Ext.getCmp('extProgressBar').updateText('An error has been occured. Connecting...');
                ajaxSetDraft(params, 0);
            }
        }
    })
}