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

var AttributeGrid = {

    /**
     * @property {Axis.grid.GridPanel} el
     */
    el: null,

    /**
     * @param {Array} records Ext.data.Record
     */
    add: function(records) {

        if (!records.length) {
            return;
        }

        if (!ProductWindow.attributeDetailsWindow) {
            ProductWindow.attributeDetailsWindow = new Axis.AttributeDetails();
        }

        ProductWindow.attributeDetailsWindow.purgeListeners();
        ProductWindow.attributeDetailsWindow.on('okpress', function(defaults) {
            var attributes = [];
            Ext.each(records, function(r) {
                if (r.get('parent') === null
                    && r.get('input_type') != 1 // string
                    && r.get('input_type') != 4 // textarea
                    && r.get('input_type') != 5) { //file

                    return;
                }

                if ((null !== r.get('parent') && AttributeGrid.el.store
                        .find('option_value_id', r.get('value_id')) == -1)
                    || (null === r.get('parent') && AttributeGrid.el.store
                        .find('option_id', r.get('option_id')) == -1)) {

                    var record = new AttributeGrid.el.store.recordType({
                        option_name             : r.get('option_name'),
                        value_name              : r.get('value_name'),
                        option_id       : r.get('option_id'),
                        option_value_id : r.get('value_id'),
                        price_type              : defaults.price_type,
                        weight_type             : defaults.weight_type,
                        price                   : defaults.price,
                        weight                  : defaults.weight,
                        remove                  : 0
                    });
                    if (null === r.get('parent')) { // inputable type [file, string, textarea]
                        record.set('value_name', 'USER_INPUT');
                    }
                    record.markDirty();
                    AttributeGrid.el.store.modified.push(record);
                    attributes.push(record);
                }
            });
            AttributeGrid.el.store.add(attributes);
            var sortState = AttributeGrid.el.store.getSortState();
            AttributeGrid.el.store.sort(sortState.field, sortState.direction);
        });

        ProductWindow.attributeDetailsWindow
            .setSkuPrefix(ProductWindow.form.getForm().findField('product[sku]').getValue())
            .hideField('sku_prefix')
            .hideField('quantity')
            .hideField('cost')
            .show();
    },

    clearData: function() {
        AttributeGrid.el.store.loadData([]);
    },

    loadData: function(data) {
        AttributeGrid.el.store.loadData(data.modifiers);
    },

    getData: function() {
        var modified = AttributeGrid.el.store.getModifiedRecords();

        if (!modified.length) {
            return;
        }

        var data = {};
        for (var i = modified.length - 1; i >= 0; i--) {
            data[modified[i]['id']] = modified[i]['data'];
        }

        return {
            'modifier': data
        };
    }
}

Ext.onReady(function() {

    var ds = new Ext.data.GroupingStore({
        groupField: 'option_name',
        mode: 'local',
        pruneModifiedRecords: true,
        sortInfo: {
            field: 'id',
            direction: 'ASC'
        },
        reader: new Ext.data.JsonReader({
            idProperty: 'id',
            fields: [
                {name: 'id', type: 'int'},
                {name: 'value_name'},
                {name: 'option_name'},
                {name: 'option_id', type: 'int'},
                {name: 'option_value_id', type: 'int'},
                {name: 'price_type'},
                {name: 'price', type: 'float'},
                {name: 'weight_type'},
                {name: 'weight', type: 'float'},
                {name: 'remove', type:'int'}
            ]
        })
    });

    var remove = new Axis.grid.CheckColumn({
        dataIndex: 'remove',
        header: 'Delete'.l(),
        width: 60
    });

    var cm = new Ext.grid.ColumnModel({
        defaults: {
            groupable: false,
            sortable: true,
            menuDisabled: true
        },
        columns: [{
            dataIndex: 'id',
            header: 'Id'.l(),
            width: 40
        }, {
            dataIndex: 'option_name',
            header: 'Option'.l(),
            hidden: true
        }, {
            dataIndex: 'value_name',
            header: 'Name'.l(),
            id: 'value_name'
        }, {
            header: 'Price modifier'.l(),
            dataIndex: 'price_type',
            editor: ProductWindow.modifierType.cloneConfig(),
            renderer: ProductWindow.modifierRenderer,
            width: 130
        }, {
            align: 'right',
            header: 'Price'.l(),
            dataIndex: 'price',
            editor: new Ext.form.NumberField(),
            width: 60
        }, {
            header: 'Weight modifier'.l(),
            dataIndex: 'weight_type',
            editor: ProductWindow.modifierType.cloneConfig(),
            renderer: ProductWindow.modifierRenderer,
            width: 130
        }, {
            align: 'right',
            header: 'Weight'.l(),
            dataIndex: 'weight',
            editor: new Ext.form.NumberField(),
            width: 60
        }, remove]
    });

    AttributeGrid.el = ProductWindow.attributeGrid = new Axis.grid.EditorGridPanel({
        autoExpandColumn: 'value_name',
        border: false,
        cm: cm,
        ds: ds,
        sm: new Ext.grid.RowSelectionModel(),
        view: new Ext.grid.GroupingView({
            groupTextTpl: '[{[values.rs[0].get("option_id")]}] {text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
            showGroupName: false
        }),
        id: 'grid-product-window-attribute-list',
        massAction: false,
        plugins: [remove],
        title: 'Attributes'.l(),
        tbar: [{
            icon: Axis.skinUrl + '/images/icons/add.png',
            text: 'Add'.l(),
            handler: function() {
                if (!ProductWindow.attributeWindow) {
                    ProductWindow.attributeWindow = new Axis.AttributeWindow();
                }
                ProductWindow.attributeWindow.purgeListeners();
                ProductWindow.attributeWindow.on('okpress', AttributeGrid.add);
                ProductWindow.attributeWindow
                    .clearFilter()
                    .setSingleSelect(false)
                    .show();
            }
        }]
    });

    ProductWindow.addTab(AttributeGrid.el, 80);
    ProductWindow.dataObjects.push(AttributeGrid);

});
