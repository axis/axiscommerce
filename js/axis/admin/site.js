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

Ext.onReady(function(){

    Ext.QuickTips.init();

    var used_categories = new Array(); //categories, that are used by sites
    var category_values = new Array(); //set of all root categories

    var siteFields = Ext.data.Record.create([
       'id',
       'name',
       'base',
       'secure',
       'root_category'
    ]);

    var categoryFields = new Ext.data.Record.create([
        'id',
        'name'
    ])

    var rootCategoriesStore = new Ext.data.Store({
        url: Axis.getUrl('catalog_category/get-root-categories'),
        //data: categories,
        reader: new Ext.data.JsonReader({
            idProperty: 'id',
            root: 'data'
        }, categoryFields)
    })
    rootCategoriesStore.load();

    var rootCategoriesCombo = new Ext.form.ComboBox({
        store: rootCategoriesStore,
        displayField: 'name',
        valueField: 'id',
        triggerAction: 'all',
        lastQuery: '',
        mode: 'local',
        editable: false,
        lazyInit: true,
        forceSelection: true
    })

    rootCategoriesCombo.on('change', function(combo, newValue, oldValue){
        oldValue_position = used_categories.indexOf(oldValue);
        newValue_position = used_categories.indexOf(newValue);
        if (oldValue_position != -1)
            used_categories.splice(oldValue_position, 1);
        if (newValue_position == -1)
            used_categories[used_categories.length] = newValue;
    })

    rootCategoriesCombo.on('focus', function(combo){
        var value_to_skip = this.getValue();
        var str = "";
        var categories_clone = category_values.slice(0);
        for (var i = 0, len = used_categories.length; i < len; i++){
            if (used_categories[i] == value_to_skip/* || used_categories[i] == 0*/)
                continue;
            entry_to_del =  categories_clone.indexOf(used_categories[i]);
            categories_clone.splice(entry_to_del, 1);
        }
        for (var i = 0, len = categories_clone.length; i < len; i++) {
            str += '^' + categories_clone[i] + '$';
            if (i+1 != len)
                str += '|';
        }
        str = str == "" ? '^-10$' : str;
        rootCategoriesStore.filter('id', new RegExp(str));
    })

    rootCategoriesCombo.on('blur', function(combo){
        rootCategoriesStore.clearFilter();
    })

    var rootCategoriesRenderer = function(value){
        if (!rootCategoriesStore.getById(value)) {
            return '';
        }
        return rootCategoriesStore.getById(value).get('name');
    }

    var cm = new Ext.grid.ColumnModel([
        {
            header: 'Id'.l(),
            dataIndex: 'id',
            width: 60
        }, {
            header: 'Name'.l(),
            dataIndex: 'name',
            id: 'name',
            editor: new Ext.form.TextField({
                allowBlank: false
            })
        }, {
            header: 'Base Url'.l(),
            dataIndex: 'base',
            editor: new Ext.form.TextField({
                allowBlank: false
            }),
            width: 220
        },{
            header: 'Secure Url'.l(),
            dataIndex: 'secure',
            editor: new Ext.form.TextField({
                allowBlank: false
            }),
            width: 220
        }, {
            header: 'Root category'.l(),
            dataIndex: 'root_category',
            editor: rootCategoriesCombo,
            renderer: rootCategoriesRenderer,
            width: 180
        }
    ])
    cm.defaultSortable = true;

    var ds = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            method: 'get',
            url: Axis.getUrl('site/get-list')
        }),
        reader: new Ext.data.JsonReader({
            root: 'data',
            id: 'id'
        }, siteFields),
        pruneModifiedRecords: true
    })

    rootCategoriesStore.on('load', function(store, records, options){
        ds.load();
        category_values.length = 0;
        Ext.each(store.data.items, function(item, index, items){
            category_values[category_values.length] = item.id;
        })
    })

    var used_categories = new Array();

    ds.on('load', function(){
        Ext.each(this.data, function(item, index, items){
            if (items.items[index].get('root_category'))
                used_categories[used_categories.length] = items.items[index].get('root_category');
        })
    })

    ds.on('beforeload', function(store, records, options){
        used_categories.length = 0;
    })

    var grid = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'name',
        ds: ds,
        cm: cm,
        tbar: [{
            text: 'Add'.l(),
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler: addSite
        }, {
            text: 'Save'.l(),
            icon: Axis.skinUrl + '/images/icons/save_multiple.png',
            handler: saveSite
        }, {
            text: 'Delete'.l(),
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: deleteSite
        }, '->', {
            text: 'Reload'.l(),
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: function(){
                grid.getStore().reload();
            }
        }]
    });

    new Axis.Panel({
        items: [
            grid
        ]
    });

    function addSite(){
        var s = new siteFields({
            id: 'new',
            name: '',
            base: '',
            secure: '',
            root_category: 'new'
        });
        grid.stopEditing();
        grid.getStore().insert(0, s);
        grid.startEditing(0, 2);
    }
    function saveSite(){
        var modified = grid.getStore().getModifiedRecords();

        if (!modified.length)
            return false;

        var data = {};
        for (var i = 0; i < modified.length; i++) {
            data[modified[i]['id']] = modified[i]['data'];
        }

        var jsonData = Ext.encode(data);
        Ext.Ajax.request({
            url: Axis.getUrl('site/save'),
            params: {data: jsonData},
            callback: function() {
                grid.getStore().commitChanges();
                rootCategoriesStore.load();
            }
        });
    }
    function deleteSite(){
        var selectedItems = grid.getSelectionModel().selections.items;

        if (!selectedItems.length)
            return;

        if (!confirm('Delete site?'))
            return;

        var data = {};

        for (var i = 0; i < selectedItems.length; i++) {
            data[i] = selectedItems[i].id;
        }
        var jsonData = Ext.encode(data);
        Ext.Ajax.request({
            url: Axis.getUrl('site/delete'),
            params: {data: jsonData},
            callback: function() {
                rootCategoriesStore.load();
            }
        });
    }
})
