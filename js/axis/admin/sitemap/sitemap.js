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
            Ext.getCmp('form').getForm().reset();
            Ext.getCmp('window').show();
        },
        edit: function (id) {
             var selectedItems = gridSitemap.getSelectionModel().selections.items;
             if (typeof(id) !== "undefined") {
                 sitemapId = id;
             }
             Ext.getCmp('window').show();
             
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
        getXml: function () {
            if (!Ext.getCmp('form').getForm().isValid()) {
                return;
            }
            var form = Ext.getCmp('form').getForm();
            var filename = form.findField('filename').getValue() + '.xml';

            Ext.Msg.show({
                msg: 'the file ({filename}) save it for later use in your site root (AXIS_ROOT)'.l('sitemap', filename),
                buttons: Ext.MessageBox.OK,
                fn: function() {
                    window.location = Axis.getUrl('sitemap_index/get-xml')
                        + '?' + form.getValues(true);
                },
                icon: Ext.MessageBox.INFO
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
        fields: [{name:'id', type: 'int'}, 'name', 'base', 'secure'],
        data: sites
    });

    var storeEngines = new Ext.data.JsonStore({
        id: 'id',
        fields: ['id', 'url','name'],
        data: engines
    });

    var cmpSiteId = new Ext.form.ComboBox({
        store: storeSites,
        editable: false,
        valueField: 'id',
        displayField: 'name',
        triggerAction: 'all',
        mode: 'local'
    });

    var cmpEngines = new Ext.ux.Andrie.Select(Ext.applyIf({
        fieldLabel:  'Field',
        multiSelect: true,
        minLength: 1
    }, {
        store:storeEngines,
        valueField:'id',
        displayField:'name',
        triggerAction:'all',
        mode:'local'
    }));

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
            editor: cmpSiteId,
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
            editor: cmpEngines,
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
                return String.format('<a href="{0}" target="_blank">{0}</a>', value);
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

    var form = new Axis.FormPanel({
        id: 'form',
        bodyStyle: 'padding: 10px 10px 0px 10px;',
        defaults: {
            anchor: '100%'
        },
        items: [{
            fieldLabel: 'Filename'.l(),
            maxLength: 55,
            xtype: 'textfield',
            allowBlank: false,
            name: 'filename'
        }, cmpSiteId.cloneConfig({
            fieldLabel: 'Site'.l(),
            name: 'site_id',
            hiddenName: 'site_id',
            allowBlank: false
        }), cmpEngines.cloneConfig({
            fieldLabel: 'Engines'.l(),
            name: 'engine',
            hiddenName: 'engine',
            originalValue: 1 //default engine Google
        }), {
            fieldLabel: 'id',
            xtype: 'hidden',
            name: 'id',
            allowBlank: true
        }]
    });

//    var window =
        new Axis.Window({
        id: 'window',
        maximizable: true,
        width: 310,
        height: 175,
        title: 'Sitemap'.l(),
        items: form,
        buttons: [{
            text: 'Generate'.l(),
            handler: function() {
                sitemap.getXml();
            }
        }, {
            text: 'Save'.l(),
            handler: function() {
                sitemap.save();
            }
        }, {
            text: 'Cancel'.l(),
            handler: function(){
                editWin.hide();
            }
        }]
    });

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