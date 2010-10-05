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

var editWin, storeSitemap, resultWin;
//http://sitemaps.org/protocol.php#location
Ext.onReady(function (){
     
    Ext.QuickTips.init();  
    var sitemap = {
        addnew: function () {
            jQuery('#sitemap-form')[0].reset();
            sitemapId = -1;
            getWindow().show();
            jQuery('#sitemap').show();
        },
        edit: function (id) {
             var selectedItems = gridSitemap.getSelectionModel().selections.items;
             if (typeof(id) !== "undefined") {
                 sitemapId = id;
             }
             for (var i = 0; i < selectedItems.length; i++) {
                 sitemapId = selectedItems[i].id;
                 getWindow();   
                 editWin.show();
                 jQuery('#sitemap-form-site').val(selectedItems[i].data.site_id);
                 jQuery('#sitemap-form-engine').val(selectedItems[i].data.engines.split(','));
                 jQuery('#sitemap-form-filename').val(selectedItems[i].data.filename);
                 jQuery('#sitemap-form-savepath').val(selectedItems[i].data.savepath); 
                 jQuery('#sitemap').show();
             }
        },
        remove: function (id) {
            var data = {};
            if (typeof(id) !== "undefined") {
                data[0] = id;
            } else {
                var selectedItems = gridSitemap.getSelectionModel().selections.items;
                if (selectedItems.length < 1) {
                    return;
                }
                for (var i = 0; i < selectedItems.length; i++) {
                    data[i] = selectedItems[i].id;
                }
            }
            if (!confirm('Are you sure?'.l())) {
                return;
            }
            Ext.Ajax.request({
                url: Axis.getUrl('sitemap_index/remove'),
                params: {data: Ext.encode(data)},
                callback: function() {
                    gridSitemap.getStore().reload();
                }
            });
        },
        ping: function (id) {
            var data = {};
            if (typeof(id) !== "undefined") {
                data[0] = id;
            } else {
                var selectedItems = gridSitemap.getSelectionModel().selections.items;
                if (selectedItems.length < 1) {
                    return;
                }
                for (var i = 0; i < selectedItems.length; i++) {
                    data[i] = selectedItems[i].id;
                }
            }
            for (var i = 0; i < engines.length; i++){
                jQuery('#result-' + engines[i].name).html('Not used ') ;
            }
            Ext.Ajax.request({
                url: Axis.getUrl('sitemap_index/ping'),
                params: {
                    ids: Ext.encode(data)
                },
                callback: function(options, success, response) {
                    var oRet = Ext.decode(response.responseText);
                    if (true == oRet.success ) {
                        for (id in oRet.info) {
                            for (var i = 0; i < engines.length; i++){
                                 var value = 'Not used';
                                 if (typeof(oRet.info[id][engines[i].name]) !== "undefined")
                                    value = oRet.info[id][engines[i].name]['html_page'];
                                jQuery('#result-' + engines[i].name).html(value);
                            }
                        }
                        getResults();   
                        resultWin.setTitle('Results informing ');
                        resultWin.show();
                        jQuery('#result').show();
                        resultWin.center();
                        storeSitemap.reload();
                    } else {
                        Ext.Msg.show({
                           title:'Error',
                           msg: oRet.error,
                           buttons: Ext.Msg.OK,
                           //fn: processResult,
                           //animEl: 'elId',
                           icon: Ext.MessageBox.ERROR
                        });
                    }
                }
            });
        },
        save: function () {
            Ext.Ajax.request({
                url : Axis.getUrl('sitemap_index/save'),
                form: 'sitemap-form',
                params: {id: sitemapId},
                callback: function(options, success, response) {
                    var oRet = Ext.decode(response.responseText);
                    if (true == oRet.success) {
                        editWin.hide();
                        jQuery('#sitemap-form-site').get(0).checked = false;
                        jQuery('#sitemap-form-filename').val('');
                        jQuery('#sitemap-form-savepath').val(''); 
                        storeSitemap.reload();
                    } 
                }
            });
        },
        quicksave: function () {
            var modified = gridSitemap.getStore().getModifiedRecords();
            if (modified.length < 1) {
                return;
            }
            var data = {};
            for (var i = 0; i < modified.length; i++) {
                data[modified[i]['data'].id] = {
                    'site': modified[i]['data'].site_id,
                    'engines': modified[i]['data'].engines
                };
            }
            Ext.Ajax.request({
                url: Axis.getUrl('sitemap_index/quick-save'),
                params: {data: Ext.encode(data)},
                callback: function() {
                    gridSitemap.getStore().commitChanges();
                    gridSitemap.getStore().reload();
                }
            })
        }
        
    };
   
    storeSitemap = new Ext.data.GroupingStore({
        url: Axis.getUrl('sitemap_index/list'),
        reader: new Ext.data.JsonReader({
                root : 'sitemap',
                totalProperty: 'count',
                id: 'id'
            },
            ['id', 
            'filename', 
            {name: 'generated_at', type: 'date', dateFormat: 'Y-m-d'},
            {name: 'usage_at', type: 'date', dateFormat: 'Y-m-d'},
            'site_id', 
            'status', 
            'baseUrl', 
            'engines']
        ),
        sortInfo: {field: 'id', direction: "ASC"},
        remoteSort: false
        //,groupField:'site_id'
    });
    
    var actions = new Ext.ux.grid.RowActions({
        header:'Actions'.l(),
        actions:[{
            iconCls:'icon-exec',
            tooltip:'Ping'.l()
        }, {
            iconCls:'icon-edit',
            tooltip:'Edit'.l()
        }, {
            iconCls:'icon-delete',
            tooltip:'Delete'.l()
        }],
        callbacks: {
            'icon-exec': function(grid, record, action, row, col) {
                sitemap.ping(record.json.id);
            },
            'icon-edit': function(grid, record, action, row, col) {
                sitemap.edit(record.json.id);
            },
            'icon-delete': function(grid, record, action, row, col) {
                sitemap.remove(record.json.id);
            }
        }
    });
    
    var storeSites = new Ext.data.JsonStore({
        id: 'id',
        fields: ['id', 'name','base'],
        data: sites
    });
    
    var storeEngines = new Ext.data.JsonStore({
        id: 'id',
        fields: ['id', 'url','name'],
        data: engines
    });
    
    var columnsSitemap = new Ext.grid.ColumnModel([
        {
            header: "Id".l(), 
            width: 40, 
            sortable: true, 
            dataIndex: 'id',
            groupable:false
        }, {
            header: "Sites".l(), 
            sortable: true,
            dataIndex: 'site_id', 
            editor: new Ext.form.ComboBox({
                store:storeSites,
                valueField:'id',
                displayField:'name',
                triggerAction:'all',
                mode:'local'
            }),
            renderer: function(value) {
                siteRecord = storeSites.getById(value);
                if (siteRecord) {
                    return siteRecord.data.name;
                }
                return value;
            },
            width: 200
        }, {
            header: "Engines".l(), 
            sortable: true,
            width: 180, 
            dataIndex: 'engines', //must return from action.list type string, example 'google, ask'
            editor: new Ext.ux.Andrie.Select(Ext.applyIf({
                fieldLabel:  'Field',
                multiSelect: true,
                minLength: 1
            }, {
                store:storeEngines,
                valueField:'id',
                displayField:'name',
                triggerAction:'all',
                mode:'local'
            })),
            renderer: function(value, meta) {
                var ret = new Array();
                value = value.split(',');
                for (var i = 0, n = value.length; i < n; i++) {
                    if (value[i] != '') {
                        ret.push(storeEngines.getById(value[i]).data.name);
                    }
                }
                ret = ret.join(', ');
                return ret ;
            }
        }, {
            header: "Filename".l(), 
            width: 250, 
            sortable: true,
            dataIndex: 'filename',
            renderer: function(value, meta) {
                var ret = Axis.baseUrl + '/' + value + '.xml';
                return ret;
            }
        }, {
            header: "Link for Search Engines".l(), 
            width: 250, 
            dataIndex: 'filename',
            id: 'filename',
            renderer: function(value, meta, record) {
                if (value == '' || !value) {
                    return "None".l();
                } else {
                    value = Axis.baseUrl + '/' + value + '.xml';
                }
                return String.format(
                    '<a href="{0}" class="grid-link-icon url" target="_blank" >{0}</a>',
                    value);
            }
        }, {
            header: 'Created'.l(),
            width: 100, 
            sortable: true,
            dataIndex: 'generated_at',
            groupable:false,
            renderer: function(v) {
                return Ext.util.Format.date(v);
            }
        }, {
            header: "Status".l(), 
            width: 90, 
            sortable: true,
            dataIndex: 'status',
            renderer: function(value, meta) {
                return parseInt(value) ? 'Used'.l() : 'Unused'.l();
            }
        },
        actions
    ]);
    
    gridSitemap = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'filename',
        ds: storeSitemap,
        cm: columnsSitemap,
        plugins:[actions],
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
            handler : function(){
                sitemap.addnew();
            }
        }, {
            text: 'Save'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler: function(){
                sitemap.quicksave();
            }
        }, {
            text: 'Edit'.l(),
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            cls: 'x-btn-text-icon',
            handler : function(){
                sitemap.edit();
            }
        }, {
            text: 'Ping'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/menu_action.png',
            handler: function(){
                sitemap.ping();
            }
        }, {
            text: 'Delete'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: function(){
                sitemap.remove();
            }
        },'->',{
            text: 'Reload'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: function(){
                gridSitemap.getStore().reload();
            }
        }]
    });
    
    new Axis.Panel({
        items: [
            gridSitemap
        ]
    });
    
    function setSitemap(id) {
       var store = gridSitemap.getStore();
       store.lastOptions = {params:{start:0, limit:21}};
       gridSitemap.filters.filters.get('id').setValue({'eq': id});
    }
    
    if (typeof(sitemapId) !== "undefined") {
        setSitemap(sitemapId);
    } else {
        storeSitemap.load({
            params: {
                start: 0,
                limit: 21
            }
        })
    }
        
    function getWindow() {
        if (!editWin) {
            editWin = new Ext.Window({
                title: 'Sitemap'.l(),
                contentEl: 'sitemap',
                layout: 'fit',
                width: 310,
                closeAction: 'hide',
                plain: true,
                autoScroll:true,
                buttons: [{
                    text: 'Save'.l(),
                    handler: function() {
                        sitemap.save();
                    }
                },{
                    text: 'Cancel'.l(),
                    handler: function(){
                        editWin.hide();
                    }
                }]
               
            });
        }
        return editWin;
    }
    
    function getResults() {
        if (!resultWin) {
            resultWin = new Ext.Window({
                contentEl: 'result',
                layout: 'fit',
                width: 600,
                constrainHeader: true,
                bodyStyle:{'background': '#fff'},
                closeAction: 'hide',
                plain: true,
                autoScroll:true,
                buttons: [{
                    text: 'Close'.l(),
                    handler: function(){
                        resultWin.hide();
                    }
                }]
               
            });
        }
        return resultWin;
    }
});