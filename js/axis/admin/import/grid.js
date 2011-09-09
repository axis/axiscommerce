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
        'type',
        'name',
        'host',
        {name: 'created_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
        {name: 'updated_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
        'db_name',
        'db_user',
        'db_password',
        'table_prefix'
    ])

    var ds = new Ext.data.Store({
        url: Axis.getUrl('import/list'),
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
            header: 'Host'.l(),
            dataIndex: 'host',
            menuDisabled: true
        }, {
            header: 'Database'.l(),
            dataIndex: 'db_name',
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
        win.setTitle(selected.get('name') + ' [' + selected.get('type') + ']');
        win.show();

        Ext.getCmp('form_profile_edit').getForm().setValues({
            'profile[type]': selected.get('type'),
            'profile[name]': selected.get('name'),
            'profile[host]': selected.get('host'),
            'profile[db_name]': selected.get('db_name'),
            'profile[db_user]': selected.get('db_user'),
            'profile[db_password]': selected.get('db_password'),
            'profile[table_prefix]': selected.get('table_prefix'),
            'profile[id]': selected.get('id')
        });
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
            url: Axis.getUrl('import/remove'),
            params: {data: Ext.encode(data)},
            success: reload
        })
    }

    function addProfile(){
        var win = Ext.getCmp('window_profile');
        Ext.getCmp('form_profile_edit').getForm().clear();
        win.setTitle('Add'.l())
        win.show();
    }
})