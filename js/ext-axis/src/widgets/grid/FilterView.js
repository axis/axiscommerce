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

Axis.grid.FilterView = Ext.extend(Ext.grid.GridView, {

    initTemplates: function() {
        var ts = this.templates || {};
        ts.master = new Ext.Template(
            '<div class="x-grid3 x-grid3-filter" hidefocus="true">',
                '<div class="x-grid3-viewport">',
                    '<div class="x-grid3-header"><div class="x-grid3-header-inner"><div class="x-grid3-header-offset" style="{ostyle}">{header}</div></div><div class="x-clear"></div></div>',
                    '<div class="x-grid3-scroller"><div class="x-grid3-body" style="{bstyle}">{body}</div><a href="#" class="x-grid3-focus" tabIndex="-1"></a></div>',
                '</div>',
                '<div class="x-grid3-resize-marker">&#160;</div>',
                '<div class="x-grid3-resize-proxy">&#160;</div>',
            '</div>'
        );
        ts.header = new Ext.Template(
            '<table border="0" cellspacing="0" cellpadding="0" style="{tstyle}">',
            '<thead><tr class="x-grid3-hd-row">{cells}</tr><tr class="x-grid3-hd-row x-grid3-filter-row">{filters}</tr></thead>',
            '</table>'
        );
        ts.fcell = new Ext.Template(
            '<td class="x-grid3-cell-filter x-grid3-cell x-grid3-td-{id} {css}" style="{style}"><div {attr} class="x-grid3-cell-filter-inner x-grid3-filter-{id}" unselectable="off" style="{istyle}">',
            '</div></td>'
        );

        this.templates = ts;
        Axis.grid.FilterView.superclass.initTemplates.call(this);
    },

    initElements: function() {
        Axis.grid.FilterView.superclass.initElements.call(this);
        this.filters = new Ext.util.MixedCollection();
        this.filters.getKey = function (o) {
            return o ? o.id : null;
        };

        Ext.each(this.cm.columns, function(col) {
            if (!col.filterable || !col.dataIndex) {
                return true;
            }

            var cnt = new Ext.Container({
                id: 'x-grid3-filter-panel-' + col.dataIndex,
                layout: 'form',
                labelWidth: 30,
                border: false,
                defaults: {
                    anchor: '100%'
                },
                items: this.getFilterElements(col)
            });
            this.filters.add({
                id: col.dataIndex,
                cnt: cnt
            });
        }, this);
    },

    /**
     * @private
     * Renders the header row using the 'header' template. Does not inject the HTML into the DOM, just
     * returns a string.
     * @return {String} Rendered header row
     */
    renderHeaders : function() {
        var cm   = this.cm,
            ts   = this.templates,
            ct   = ts.hcell,
            fct  = ts.fcell,
            cb   = [],
            fcb   = [],
            p    = {},
            f    = {},
            len  = cm.getColumnCount(),
            last = len - 1;

        for (var i = 0; i < len; i++) {
            p.id = cm.getColumnId(i);
            f.id = cm.getDataIndex(i);
            f.attr = 'id="x-grid3-filter-' + f.id + '"';
            p.value = cm.getColumnHeader(i) || '';
            p.style = f.style = this.getColumnStyle(i, true);
            p.tooltip = this.getColumnTooltip(i);
            p.css = f.css = i === 0 ? 'x-grid3-cell-first ' : (i == last ? 'x-grid3-cell-last ' : '');

            if (cm.config[i].align == 'right') {
                p.istyle = 'padding-right:16px';
            } else {
                delete p.istyle;
                delete f.istyle;
            }
            cb[cb.length] = ct.apply(p);
            fcb[fcb.length] = fct.apply(f);
        }
        return ts.header.apply({
            cells: cb.join(''),
            filters: fcb.join(''),
            tstyle:'width:'+this.getTotalWidth()+';'
        });
    },

    updateHeaders: function() {
        Axis.grid.FilterView.superclass.updateHeaders.call(this);
        this.renderFilters();
    },

    /**
     * @privatrenderFilters
     * Renders each of the UI elements in turn. This is called internally, once, by this.render. It does not
     * render rows from the store, just the surrounding UI elements. It also sets up listeners on the UI elements
     * and sets up options like column menus, moving and resizing.
     */
    renderUI: function() {
        Axis.grid.FilterView.superclass.renderUI.call(this);
        this.renderFilters();
    },

    updateColumnWidth: function(col, width) {
        Axis.grid.FilterView.superclass.updateColumnWidth.call(this, col, width);
        var p = Ext.getCmp('x-grid3-filter-panel-' + this.cm.getDataIndex(col));
        p && p.doLayout();
    },

    updateAllColumnWidths: function() {
        Axis.grid.FilterView.superclass.updateAllColumnWidths.call(this);
        Ext.each(this.cm.columns, function(col) {
            var p = Ext.getCmp('x-grid3-filter-panel-' + col.dataIndex);
            p && p.doLayout();
        });
    },

    getHeaderCell: function(index) {
        return this.mainHd.dom.getElementsByTagName('td')[index];
    },

    // custom functions
    renderFilters: function() {
        this.filters.each(function(filter) {
            if (!filter.cnt.rendered) {
                filter.cnt.render(Ext.fly('x-grid3-filter-' + filter.id));
            } else {
                // @TODO its wrong
                Ext.fly('x-grid3-filter-' + filter.id).dom.innerHTML = filter.cnt.el.dom.innerHTML;
            }
        });
    },

    getFilterElements: function(column) {
        switch (this.getFilterType(column)) {
            case 'int':
            case 'float':
                return [
                    new Ext.form.NumberField({
                        fieldLabel: 'From'.l()
                    }),
                    new Ext.form.NumberField({
                        fieldLabel: 'To'.l()
                    })
                ];
            case 'date':
                return [
                    new Ext.form.DateField({
                        fieldLabel: 'From'.l()
                    }),
                    new Ext.form.DateField({
                        fieldLabel: 'To'.l()
                    })
                ];
            case 'list':
                var ds = column.filter.store,
                    resetData = {},
                    vF  = column.filter.valueField   ? column.filter.valueField   : 'id',
                    dF  = column.filter.displayField ? column.filter.displayField : 'name',
                    vFt = column.filter.resetValue   ? column.filter.resetValue   : ' ',
                    dFt = column.filter.resetText    ? column.filter.resetText    : '&nbsp;';

                // resetData.id = vF;
                resetData[vF] = vFt;
                resetData[dF] = dFt;
                if (ds.data.length) {
                    ds.insert(0, new ds.recordType(resetData));
                }
                ds.on('load', function(store, records, options) {
                    store.insert(0, new store.recordType(resetData));
                });
                return [
                    new Ext.form.ComboBox({
                        displayField    : dF,
                        hideLabel       : true,
                        mode            : column.filter.mode ?
                            column.filter.mode : 'local',
                        store           : ds,
                        triggerAction   : 'all',
                        valueField      : vF,
                        listeners       : {
                            scope: this,
                            select: function(combo, record, index) {
                                if ('&nbsp;' === record.get(combo.displayField)) {
                                    combo.getEl().dom.value = ' ';
                                }
                            }
                        }
                    })
                ];
            default:
                return [
                    new Ext.form.TextField({
                        hideLabel: true
                    })
                ];
        }
    },

    getFilterType: function(column) {
        if (column.filter) {
            if (column.filter.store) {
                return 'list';
            } else if (column.filter.type) {
                return column.filter.type;
            }
        } else {
            var ds = this.grid.getStore();
            return ds.fields.get(column.dataIndex).type.type;
        }
    }
});
