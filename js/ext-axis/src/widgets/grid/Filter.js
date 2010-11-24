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

Axis.grid.Filter = Ext.extend(Ext.util.Observable, {

    constructor: function(config) {
        this.templates = {
            master: new Ext.Template(
                '<div class="x-grid3-filter"><div class="x-grid3-filter-inner"><div class="x-grid3-filter-offset" style="{ostyle}">{filter}</div></div><div class="x-clear"></div></div></div>'
            ),
            filter: new Ext.Template(
                '<table border="0" cellspacing="0" cellpadding="0" style="{tstyle}">',
                '<thead><tr class="x-grid3-hd-row x-grid3-filter-row">{cells}</tr></thead>',
                '</table>'
            ),
            fcell: new Ext.Template(
                '<td class="x-grid3-cell-filter x-grid3-cell x-grid3-td-{id} {css}" style="{style}">',
                '<div {attr} id="x-grid3-filter-{dataIndex}" class="x-grid3-cell-filter-inner x-grid3-filter-{dataIndex}" unselectable="off" style="{istyle}"></div>',
                '</td>'
            )
        }

        Ext.apply(this, config);
    },

    init: function(grid) {
        this.grid = grid;
        this.cm = grid.getColumnModel();
        this.view = grid.getView();
        this.initFilters();
        this.grid.on({
            scope           : this,
            viewready       : this.doRender,
            columnresize    : this.onColumnResize,
            columnmove      : this.onColumnMove,
            bodyscroll      : this.onScroll,
            resize          : function() {
                // console.log(arguments);
            }
        });
        this.view.onLayout = this.view.onLayout.createSequence(function(vw, vh) {
            if (!this.filterDom) {
                return;
            }
            this.filterDom.style.width = vw + 'px';
            Ext.each(Ext.get(this.filterDom.firstChild.firstChild).query('td'), function(td, i) {
                td.style.width = this.view.getColumnWidth(i);
                var dataIndex = this.cm.getDataIndex(i);
                if (dataIndex) {
                    this.filters
                        .get('x-grid3-filter-cnt-' + dataIndex)
                        .doLayout();
                }
            }, this);
            this.view.scroller.setHeight(vh - Ext.get(this.filterDom).getHeight());
        }, this);
    },

    initFilters: function() {
        this.filters = new Ext.util.MixedCollection();
        Ext.each(this.cm.columns, function(col) {
            if (false === col.filterable || !col.dataIndex) {
                return true;
            }

            this.filters.add(new Ext.Container({
                id: 'x-grid3-filter-cnt-' + col.dataIndex,
                layout: 'form',
                labelWidth: 30,
                border: false,
                defaults: {
                    anchor: '100%'
                },
                items: this.getFilterItems(col)
            }));
        }, this);
    },

    getFilterItems: function(column) {
        var items = [];
        var cfg = {
            //id              : 'combo-' + column.dataIndex,
            displayField    : 'name',
            hideLabel       : true,
            mode            : 'local',
            triggerAction   : 'all',
            valueField      : 'id'
        };

        if (column.filter) {
            cfg = Ext.applyIf(column.filter, cfg);
            if (!cfg.xtype && cfg.store) {
                cfg.xtype = 'combo';
            }
        }

        if (!cfg.xtype) {
            switch (this.grid.getStore().fields.get(column.dataIndex).type.type) {
                case 'int':
                case 'float':
                    cfg.xtype = 'numberfield';
                    break;
                // case 'boolean':
                //     cfg.xtype = 'combo';
                //     break;
                case 'date':
                    cfg.xtype = 'datefield';
                    break;
                default:
                    cfg.xtype = 'textfield';
            }
        }

        if ('datefield' === cfg.xtype || 'numberfield' === cfg.xtype) {
            cfg.hideLabel = false;
            cfg.fieldLabel = 'From';
            items.push(cfg);
            items.push(Ext.copyTo(
                {fieldLabel: 'To'}, cfg, 'anchor,displayField,hideLabel,valueField,xtype'
            ));
            return items;
        } else if ('combo' === cfg.xtype) {
            var emptyRecord = {},
                vF  = cfg.valueField   ? cfg.valueField   : 'id',
                dF  = cfg.displayField ? cfg.displayField : 'name';

            emptyRecord[vF] = cfg.resetValue ? cfg.resetValue : '';
            emptyRecord[dF] = cfg.resetText ? cfg.resetText : '';
            if (cfg.store.data.length) {
                cfg.store.insert(0, new cfg.store.recordType(emptyRecord));
            }
            cfg.store.on('load', function(store, records, options) {
                store.insert(0, new store.recordType(emptyRecord));
            });

            cfg.initList = function() {
                if (this.tpl) {
                    return;
                }
                this.tpl = new Ext.XTemplate(
                    '<tpl for="."><div class="x-combo-list-item">{',
                    this.displayField,
                    ':this.blank}</div></tpl>',
                    {
                        blank: function(value) {
                            return value === '' ? '&nbsp;' : value;
                        }
                    }
                );
            }.createSequence(Ext.form.ComboBox.prototype.initList)
        }
        return [cfg];
    },

    renderMarkup: function() {
        var cm      = this.cm,
            view    = this.view,
            ts      = this.templates,
            fct     = ts.fcell,
            fcb     = [],
            f       = {},
            len     = cm.getColumnCount(),
            last    = len - 1;

        for (var i = 0; i < len; i++) {
            f.id = cm.getColumnId(i);
            f.dataIndex = cm.getDataIndex(i);
            f.style = view.getColumnStyle(i, true);
            f.css = i === 0 ? 'x-grid3-cell-first ' : (i == last ? 'x-grid3-cell-last ' : '');
            fcb[fcb.length] = fct.apply(f);
        }
        return ts.filter.apply({
            cells: fcb.join(''),
            tstyle: 'width:' + view.getTotalWidth() + ';'
        });
    },

    doRender: function() {
        if (!this.filters.length) {
            return;
        }

        var html = this.templates.master.apply({
            filter: this.renderMarkup(),
            ostyle: 'width:' + this.grid.getView().getOffsetWidth() + ';'
        });

        this.filterDom = Ext.DomHelper.insertAfter(this.view.mainHd, html).firstChild;

        this.filters.each(function(filter) {
            filter.render(Ext.fly('x-grid3-filter-' + filter.id.replace('x-grid3-filter-cnt-', '')));
        }, this);
    },

    onColumnResize: function(i, width) {
        this.filterDom.firstChild.style.width = this.view.getOffsetWidth();
        this.filterDom.firstChild.firstChild.style.width = this.view.getTotalWidth();

        var dataIndex = this.cm.getDataIndex(i);
        var cnt = this.filters.get('x-grid3-filter-cnt-' + dataIndex);
        cnt.el.parent('td').setWidth(width);
        cnt.doLayout();
        console.log('resize');
    },

    onColumnMove: function(oldIndex, newIndex) {
        var tds     = Ext.get(this.filterDom).query('td'),
            start   = oldIndex,
            end     = newIndex;

        if (newIndex > oldIndex) {
            Ext.get(tds[oldIndex]).insertAfter(tds[newIndex]);
        } else {
            start   = newIndex;
            end     = oldIndex;
            Ext.get(tds[oldIndex]).insertBefore(tds[newIndex]);
        }

        tds = Ext.get(this.filterDom).query('td');

        for (var i = start; i <= end; i++) {
            tds[i].style.width = this.view.getColumnWidth(i);
        }
        for (var i = start; i <= end; i++) {
            var dataIndex = this.cm.getDataIndex(i);
            if (dataIndex) {
                this.filters.get('x-grid3-filter-cnt-' + dataIndex).doLayout();
            }
        }
    },

    onScroll: function(left, top) {
        this.filterDom.scrollLeft = left;
        this.filterDom.scrollLeft = left; // second time for IE (1/2 time first fails, other browsers ignore)
    }
});
