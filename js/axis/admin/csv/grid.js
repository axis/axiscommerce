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
 */

Ext.onReady(function(){

    Ext.QuickTips.init();

    var profile = new Ext.data.Record.create([
        'id',
        'name',
        'site',
        'type',
        'direction',
        'file_name',
        'file_path',
        {name: 'created_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
        {name: 'updated_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
        'language_ids',
        'product_name',
        'sku',
        'stock',
        'status',
        'price_from',
        'price_to',
        'qty_from',
        'qty_to'
    ])

    var ds = new Ext.data.Store({
        url: Axis.getUrl('csv/list'),
        reader: new Ext.data.JsonReader({
            id: 'id',
            root: 'data'
        }, profile),
        autoLoad: true
    })

    var cm = new Ext.grid.ColumnModel([
        {
            header: 'Name'.l(),
            dataIndex: 'name',
            menuDisabled: true
        }, {
            header: 'Type'.l(),
            dataIndex: 'type',
            menuDisabled: true
        }, {
            header: 'Direction'.l(),
            dataIndex: 'direction',
            menuDisabled: true
        }, {
            header: 'File'.l(),
            dataIndex: 'file_name',
            menuDisabled: true
        }, {
            header: 'Date created'.l(),
            dataIndex: 'created_at',
            menuDisabled: true,
            renderer: function(v) {
                return Ext.util.Format.date(v);
            }
        }, {
            header: 'Date updated'.l(),
            dataIndex: 'updated_at',
            menuDisabled: true,
            renderer: function(v) {
                return Ext.util.Format.date(v);
            }
        }
    ])
    cm.defaultSortable = true;

    var grid = new Axis.grid.GridPanel({
        cm: cm,
        ds: ds,
        id: 'grid-profile',
        viewConfig: {
            forceFit: true,
            emptyText: 'No records found'.l()
        },
        tbar: [{
            text: 'Add'.l(),
            iconCls: 'x-btn-text',
            icon: Axis.skinUrl + '/images/icons/add.png',
            handler: addProfile
        }, {
            text: 'Edit'.l(),
            iconCls: 'x-btn-text',
            icon: Axis.skinUrl + '/images/icons/page_edit.png',
            handler: editProfile
        }, {
            text: 'Delete'.l(),
            iconCls: 'x-btn-text',
            icon: Axis.skinUrl + '/images/icons/delete.png',
            handler: removeProfile
        }, '->', {
            text: 'Reload'.l(),
            iconCls: 'x-btn-text',
            icon: Axis.skinUrl + '/images/icons/refresh.png',
            handler: reload
        }]
    });

    new Axis.Panel({
        items: [
            grid
        ]
    });

    function reload(){
        ds.reload();
    }

    grid.on('rowdblclick', function(grid, rowIndex, e){
        editProfile();
    })

    function editProfile(){
        var selected = grid.getSelectionModel().getSelected();

        if (!selected)
            return;

        var win = Ext.getCmp('window_profile');
        win.setTitle(selected.get('name'));
        win.show();

        var form = Ext.getCmp('form_profile_edit').getForm();

        form.setValues({
            'general[type]': selected.get('type'),
            'general[name]': selected.get('name'),
            'general[direction]': selected.get('direction'),
            'general[file_path]': selected.get('file_path'),
            'general[file_name]': selected.get('file_name'),
            'general[id]': selected.get('id'),
            'filter[site]': selected.get('site')
        });

        if (selected.get('direction') == 'export') {
            form.setValues({
                'filter[language_ids]': selected.get('language_ids'),
                'filter[name]': selected.get('product_name'),
                'filter[sku]': selected.get('sku'),
                'filter[stock]': selected.get('stock'),
                'filter[status]': selected.get('status'),
                'filter[price_from]': selected.get('price_from'),
                'filter[price_to]': selected.get('price_to'),
                'filter[qty_from]': selected.get('qty_from'),
                'filter[qty_to]': selected.get('qty_to')
            });
        }

        var directionCombo = Ext.getCmp('csv_direction');
        directionCombo.fireEvent(
            'beforeselect',
            directionCombo,
            directionCombo.getStore().getById(selected.get('direction'))
        );
    }

    function removeProfile(){
        var selected = grid.getSelectionModel().getSelections();

        if (!selected.length || !confirm('Are you sure?'.l())) {
            return;
        }

        var data = {};
        for (var i = 0, len = selected.length; i < len; i++){
            data[i] = selected[i].id;
        }
        Ext.Ajax.request({
            url: Axis.getUrl('csv/remove'),
            params: {data: Ext.encode(data)},
            success: reload
        })
    }

    function addProfile(){
        var win = Ext.getCmp('window_profile');
        win.setTitle('Add'.l())
        win.show();
        Ext.getCmp('tabpanel').activate(0);
        Ext.getCmp('form_profile_edit').getForm().clear();

        var directionCombo = Ext.getCmp('csv_direction');
        directionCombo.fireEvent(
            'beforeselect',
            directionCombo,
            directionCombo.getStore().getAt(0)
        );
    }
})