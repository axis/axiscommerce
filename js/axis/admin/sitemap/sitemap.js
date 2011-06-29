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
//http://sitemaps.org/protocol.php#location
Ext.onReady(function (){

    Ext.QuickTips.init();
    var sitemap = {
        record: Ext.data.Record.create([
           'id',
           'filename',
           {name: 'created_on', type: 'date', dateFormat: 'Y-m-d H:i:s'},
           {name: 'site_id', type: 'int'},
           {name: 'status', type: 'int'},
           {name: 'modified_on', type: 'date', dateFormat: 'Y-m-d H:i:s'},
           'crawlers'
        ]),
        generate: function () {
            if (!Ext.getCmp('form').getForm().isValid()) {
                return;
            }
            var form = Ext.getCmp('form').getForm();
            var filename = form.findField('filename').getValue() + '.xml';

            Ext.Msg.show({
                msg: 'the file ({filename}) save it for later use in your site root (AXIS_ROOT)'.l('sitemap', filename),
                buttons: Ext.MessageBox.OK,
                fn: function() {
                    window.location = Axis.getUrl('sitemap_file/create')
                        + '?' + form.getValues(true);
                },
                icon: Ext.MessageBox.INFO
            });
        },
        add : function () {
            storeFile.reload();
            gridSitemap.stopEditing();
            gridSitemap.getStore().insert(0, new sitemap.record({
                'site_id': sites[0].id
            }));
            gridSitemap.startEditing(0, 2);
        },
        save: function () {
            var store = gridSitemap.getStore();
            var rowset = store.getModifiedRecords();
            if (!rowset.length) {
                return;
            }

            var data = {};
            for (var i = 0; i < rowset.length; i++) {
                data[rowset[i]['id']] = rowset[i]['data'];
            }
            Ext.Ajax.request({
                url: Axis.getUrl('sitemap_index/save'),
                params: {
                    data: Ext.encode(data)
                },
                callback: function() {
                    store.commitChanges();
                    store.reload();
                }
            });
        },
        ping: function () {
            var data = {};
            var selectedItems = gridSitemap.getSelectionModel().getSelections();
            if (selectedItems.length < 1) {
                return;
            }
            for (var i = 0; i < selectedItems.length; i++) {
                data[i] = selectedItems[i].id;
            }
            var store = gridSitemap.getStore();
            
            Ext.Ajax.request({
                url: Axis.getUrl('sitemap_index/ping'),
                params: {
                    data: Ext.encode(data)
                },
                success: function(response, opts) {
                    var data = Ext.decode(response.responseText).data;
                    var html= '';

                    var templateTr = new Ext.Template([
                        '<tr>',
                            '<td>{id}</td>',
                            '<td>{crawler}</td>',
                            '<td>{body}</td>',
                            '<td>{code}</td>',
                        '</tr>'
                    ]);

                    templateTr.compile();
                    _html = '';
                    for (var i in data) {
                        if (typeof data[i] == 'function') {
                            continue;
                        }
                        __html = '';
                        for (var j in data[i]) {
                            if (typeof data[i][j] == 'function') {
                                continue;
                            }
                            __html = __html.concat(templateTr.apply(data[i][j]));
                        }
                        _html = _html.concat(__html);
                    }
                    html = html.concat(
                    '<table id="sitemap-ping">',
                       '<thead>',
                           '<tr>',
                               '<th>id</th>',
                               '<th>crawler</th>',
                               '<th>message</th>',
                               '<th>code</th>',
                           '</tr>',
                       '</thead>',
                       '<tbody>',
                           _html,
                       '<tbody>',
                   '</table>');
                    var window = Ext.getCmp('ping-window');
                    window.show();
                    window.update(html);
                    store.reload();
                }
            });
        },
        remove: function () {
            var data = {};
            var selectedItems = gridSitemap.getSelectionModel().getSelections();
            if (selectedItems.length < 1) {
                return;
            }
            for (var i = 0; i < selectedItems.length; i++) {
                data[i] = selectedItems[i].id;
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
        }
    };
    
    var storeSitemap = new Ext.data.GroupingStore({
        storeId: 'storeSitemap',
        url: Axis.getUrl('sitemap_index/list'),
        reader: new Ext.data.JsonReader({
                root : 'data',
                totalProperty: 'count',
                id: 'id'
            },
            sitemap.record
        ),
        sortInfo: {field: 'id', direction: "ASC"},
        remoteSort: false
        //,groupField:'site_id'
    });

    var storeSites = new Ext.data.JsonStore({
        storeId: 'storeSites',
        id: 'id',
        fields: [{name:'id', type: 'int'}, 'name', 'base', 'secure'],
        data: sites
    });

    var cmpSiteId = new Ext.form.ComboBox({
        store: storeSites,
        editable: false,
        valueField: 'id',
        displayField: 'name',
        triggerAction: 'all',
        mode: 'local'
    });

    var storeFile = new Ext.data.JsonStore({
        storeId: 'storeFile',
        url: Axis.getUrl('sitemap_file/list'),
        root : 'data',
        id: 'filename',
        fields: ['filename']
    });

    var cmpFile = new Ext.form.ComboBox({
        store: storeFile,
        editable: false,
        valueField: 'filename',
        displayField: 'filename',
        triggerAction: 'all'
    });

    var storeCrawlers = new Ext.data.JsonStore({
        id: 'id',
        fields: ['id', 'url', 'name'],
        data: crawlers
    });

    var cmpCrawlers = new Ext.ux.Andrie.Select({
        fieldLabel:  'Field',
        multiSelect: true,
        store: storeCrawlers,
        valueField: 'id',
        displayField: 'name',
        triggerAction: 'all',
        mode: 'local',
        beforeBlur : Ext.emptyFn
    })

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
            allowBlank: false,
            renderer: function(value) {
                siteRecord = storeSites.getById(value);
                if (siteRecord) {
                    return siteRecord.data.name;
                }
                return value;
            },
            width: 150
        }, {
            header: "Crawlers".l(),
            sortable: true,
            dataIndex: 'crawlers',
            width: 150,
            allowBlank: false,
            editor: cmpCrawlers,
            renderer: function(value, meta) {
                if (typeof(value) == 'undefined' || value == '') {
                    return 'None'.l();
                }
                var ret = new Array();
                value = value.split(',');
                for (var i = 0, n = value.length; i < n; i++) {
                    var crawler = storeCrawlers.getById(value[i]);
                    if (typeof(crawler) != 'undefined') {
                        ret.push(crawler.data.name);
                    }
                }
                ret = ret.join(', ');
                return ret;
            }
        }, {
            header: "Filename".l(),
            width: 150,
            sortable: true,
            dataIndex: 'filename',
            editor: cmpFile,
            allowBlank: false
        }, {
            header: "Link".l(),
            width: 150,
            dataIndex: 'filename',
            id: 'filename',
            renderer: function(value, meta, record) {
                if (value == '' || !value) {
                    return "None".l();
                } else {
                    value = Axis.baseUrl + '/' + value;
                }
                return String.format('<a href="{0}" target="_blank">{0}</a>', value);
            }
        }, {
            header: "Status".l(),
            width: 90,
            sortable: true,
            dataIndex: 'status',
            renderer: function(value, meta) {
                return parseInt(value) ? 'Used'.l() : 'Unused'.l();
            }
        }, {
            header: 'Modified'.l(),
            width: 150,
            sortable: true,
            dataIndex: 'modified_on',
            groupable: false,
            renderer: function(v) {
                return Ext.util.Format.date(v) + ' ' + Ext.util.Format.date(v, 'H:i:s');
            }
        }, {
            header: 'Created'.l(),
            width: 150,
            sortable: true,
            dataIndex: 'created_on',
            groupable: false,
            renderer: function(v) {
                return Ext.util.Format.date(v) + ' ' + Ext.util.Format.date(v, 'H:i:s');
            }
        }
    ]);

    gridSitemap = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'filename',
        ds: storeSitemap,
        cm: columnsSitemap,
        tbar: [{
            text: 'Generate'.l(),
            icon: Axis.skinUrl + '/images/icons/script_add.png',
            cls: 'x-btn-text-icon',
            handler : function(){
                Ext.getCmp('form').getForm().reset();
                Ext.getCmp('window').show();
            }
        }, {
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            cls: 'x-btn-text-icon',
            handler : function(){
                sitemap.add();
            }
        }, {
            text: 'Save'.l(),
            cls: 'x-btn-text-icon',
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler: function(){
                sitemap.save();
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
    
    storeSitemap.load({
        params: {start: 0, limit: 21}
    });

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
            })
        ]
    });

    new Axis.Window({
        id: 'window',
        maximizable: true,
        width: 310,
        height: 175,
        title: 'Generate Sitemap'.l(),
        items: form,
        buttons: [{
            text: 'Save'.l(),
            handler: function() {
                sitemap.generate();
                Ext.getCmp('window').hide();
            }
        }, {
            text: 'Cancel'.l(),
            handler: function(){
                Ext.getCmp('window').hide();
            }
        }]
    });

    new Axis.Window({
        id: 'ping-window',
        width: 700,
        height: 300,
        title: 'Ping Results'.l(),
        buttons: [{
            text: 'Close'.l(),
            handler: function(){
                Ext.getCmp('ping-window').hide();
            }
        }]
    });

});