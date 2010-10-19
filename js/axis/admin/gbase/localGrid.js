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

Ext.onReady(function(){
    
    var countryTip = new Ext.ToolTip({
        title: 'Attention',
        html: 'Be sure that selected country accept selected language & currency',
        target: 'gCountry',
        trackMouse: true,
        dismissDelay: 0
    });
    var langTip = new Ext.ToolTip({
        title: 'Attention',
        html: 'Be sure that selected language is accepted by Google',
        target: 'language',
        trackMouse: true,
        dismissDelay: 0 
    });
    var curTip = new Ext.ToolTip({
        title: 'Attention',
        html: 'Be sure that selected currency is accepted by Google',
        target: 'currency',
        trackMouse: true,
        dismissDelay: 0 
    });
    
    var localStore = new Ext.data.Store({
        url: Axis.getUrl('catalog_index/list-products/'),
        reader: new Ext.data.JsonReader({
               root: 'data',
               totalProperty: 'count',
               id: 'id'
            }, [
                {name: 'id'},
                {name: 'is_active'}, 
                {name: 'name'}, 
                {name: 'date_available', type: 'date', dateFormat: 'Y-m-d'},
                {name: 'price', type: 'float'}
            ]
        ),
        remoteSort: true
    });
    
    localStore.setDefaultSort('name', 'asc');
    
    var sm = new Ext.grid.CheckboxSelectionModel();
    
    var cm = new Ext.grid.ColumnModel([
        sm, {
            header: "Id".l(),
            dataIndex: 'id',
            width: 30
        }, {
            header: "Name".l(), 
            dataIndex: 'name', 
            width: 200, 
            css: 'white-space:normal;' 
        }, {
            header: "Price".l(),
            dataIndex: 'price',
            width: 80
        }
    ]);
    cm.defaultSortable = true;
    
    localGrid = new Ext.grid.GridPanel({
        id: 'grid-local-list',
        trackMouseOver: false,
        border: false,
        ds: localStore,
        stripeRows: true,
        cm: cm,
        sm: sm,
        title: 'Local Data'.l(),
        viewConfig: {
            forceFit: true,
            emptyText: 'No records found'.l()
        },
        loadMask: false,
        autoScroll: true,
        bbar: new Axis.PagingToolbar({
            id: 'paging-toolbar-local',
            store: localStore
        }),
        tbar: [
            new Ext.Toolbar.TextItem('Target country  '.l()),
            new Ext.Toolbar.Item('gCountry'),
            new Ext.Toolbar.Separator(),
            new Ext.Toolbar.TextItem('Import language  '.l()),
            new Ext.Toolbar.Item('language'),
            new Ext.Toolbar.Separator(),
            new Ext.Toolbar.TextItem('Currency  '.l()),
            new Ext.Toolbar.Item('currency'),
            new Ext.Toolbar.Separator(),
            new Ext.Toolbar.Button({
                iconCls: 'x-btn-text-icon',
                icon: Axis.skinUrl + '/images/icons/move.png',
                text: 'Export'.l(),
                handler: exportSelectedItems
            }),
            new Ext.Toolbar.Fill(),
            new Ext.Toolbar.Button({
                iconCls: 'x-btn-icon',
                icon: Axis.skinUrl + '/images/icons/refresh.png',
                handler: function(){
                    localGrid.getStore().reload();
                }
            })
        ]
    });
    
})

function exportSelectedItems(){
    var selected = localGrid.getSelectionModel().getSelections();
    
    if (!selected.length) return;
    
    if (isNaN(siteId)) {
        alert('Site is undefined');
        return false;
    }
    
    var data = {};
    var params = {};
    
    for (var i = 0; i < selected.length; i++){
        data[i] = selected[i].id;
    }
    
    params['items'] = Ext.encode(data);
    params['site'] = siteId;
    params['language'] = Ext.getDom('language').value;
    params['country']  = Ext.getDom('gCountry').value;
    params['currency'] = Ext.getDom('currency').value;
    
    ajaxExportItems(params, 1);
}

function ajaxExportItems(params, clearSession){
    if (clearSession) {
        Ext.getCmp('extProgressBar').clear();
        Ext.getCmp('extProgressBar').updateText('Initializing...');
        Ext.get('lightbox-info').show();
    }
    
    Ext.Ajax.request({
        url: Axis.getUrl('gbase_index/export'),
        method: 'post',
        params: {
            items:    params['items'],
            language: params['language'],
            country : params['country'],
            currency: params['currency'],
            site    : params['site'],
            clearSession: clearSession
        },
        callback: function(options, success, response){
            if (success) {
                var obj = Ext.util.JSON.decode(response.responseText);
                Ext.getCmp('extProgressBar').updateProgress(obj.processed/obj.count, 'Exported ' + obj.processed + ' of ' + obj.count);
                if (!obj.finalize) {
                    ajaxExportItems(params, 0);
                } else {
                    Ext.get('lightbox-info').hide();
                }
            } else {
                Ext.getCmp('extProgressBar').updateText('An error has been occured. Connecting...');
                ajaxExportItems(params, 0);
            }
        }
    })
}