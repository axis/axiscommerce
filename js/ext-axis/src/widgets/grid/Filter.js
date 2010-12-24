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

    params: {
        filters : {},
        length  : 0
    },

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
                '<div {attr} id="x-grid3-filter-{id}" class="x-grid3-cell-filter-inner x-grid3-filter-{id}" unselectable="off" style="{istyle}"></div>',
                '</td>'
            )
        }

        Ext.apply(this, config);
    },

    init: function(grid) {
        this.grid   = grid;
        this.cm     = grid.getColumnModel();
        this.view   = grid.getView();
        this.store  = grid.getStore();
        this.initFilters();
        this.grid.on({
            beforedestroy   : this.destroy,
            bodyscroll      : this.onScroll,
            columnresize    : this.onColumnResize,
            columnmove      : this.onColumnMove,
            scope           : this,
            viewready       : this.doRender
        });
        this.cm.on({
            scope       : this,
            hiddenchange: this.onColumnHiddenChange
        });

        if (!this.store.mode || 'remote' === this.store.mode) {
            this.store.on('beforeload', this.onBeforeLoad, this);
        } else {
            // this.store.on('load', this.onLoad, this);
        }

        this.view.onLayout = this.view.onLayout.createSequence(function(vw, vh) {
            if (!this.filterDom) {
                return;
            }
            this.filterDom.style.width = vw + 'px';
            var gridId = this.grid.getId();
            Ext.each(Ext.get(this.filterDom.firstChild.firstChild).query('td'), function(td, i) {
                td.style.width = this.view.getColumnWidth(i);
                var filter = this.filters.get('x-grid3-filter-cnt-' + this.cm.getColumnId(i) + '-' + gridId)
                if (filter) {
                    filter.doLayout();
                }
            }, this);
            this.view.scroller.setHeight(vh - Ext.get(this.filterDom).getHeight());
        }, this);
    },

    initFilters: function() {
        this.filters = new Ext.util.MixedCollection();
        var gridId = this.grid.getId();
        Ext.each(this.cm.columns, function(col) {
            if (false === col.filterable || !col.dataIndex) {
                return true;
            }

            this.filters.add(new Ext.Container({
                id: 'x-grid3-filter-cnt-' + col.id + '-' + gridId,
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
        var items = [],
            cfg = {
                displayField    : 'name',
                editable        : true,
                hideLabel       : true,
                mode            : 'local',
                name            : column.dataIndex,
                triggerAction   : 'all',
                valueField      : 'id'
            };

        if (column.filter) {
            cfg = Ext.applyIf(column.filter, cfg);
            if (!cfg.xtype && cfg.store) {
                cfg.xtype = 'combo';
            }
        }

        if (undefined !== typeof column.table) {
            cfg.table = column.table;
        }

        if (!cfg.xtype) {
            switch (this.store.fields.get(column.dataIndex).type.type) {
                case 'int':
                case 'float':
                    cfg.xtype = 'numberfield';
                    break;
                case 'date':
                    cfg.xtype = 'datefield';
                    break;
                default:
                    cfg.xtype = 'textfield';
            }
        }

        if ('datefield' === cfg.xtype || 'numberfield' === cfg.xtype) {
            items = this.createRangefield(cfg);
        } else if ('combo' === cfg.xtype) {
            items = [this.createCombobox(cfg)];
        } else {
            items = [this.createTextfield(cfg)];
        }
        return items;
    },

    createTextfield: function(cfg) {
        cfg.listeners = {
            scope: this,
            specialkey: function(field, e) {
                if (e.getKey() != e.ENTER) {
                    return;
                }
                this.store.reload();
            }
        };
        return cfg;
    },

    createRangefield: function(cfg) {
        cfg.hideLabel = false;
        cfg.fieldLabel = 'From'.l();
        cfg.operator = '>=';

        if ('datefield' === cfg.type) {
            cfg.listeners = {
                scope: this,
                select: function(field, date) {
                    this.store.reload();
                }
            }
        } else {
            cfg.listeners = {
                scope: this,
                specialkey: function(field, e) {
                    if (e.getKey() != e.ENTER) {
                        return;
                    }
                    this.store.reload();
                }
            }
        }

        var cfgTo = {};
        for (var i in cfg) {
            cfgTo[i] = cfg[i];
        }
        cfgTo.fieldLabel = 'To'.l();
        cfgTo.operator = '<=';

        return [cfg, cfgTo];
    },

    createCombobox: function(cfg) {
        var emptyRecord = {},
            vF  = cfg.valueField   ? cfg.valueField   : 'id',
            dF  = cfg.displayField ? cfg.displayField : 'name';

        emptyRecord[vF] = cfg.resetValue ? cfg.resetValue : '';
        emptyRecord[dF] = cfg.resetText  ? cfg.resetText  : '';

        if (false !== cfg.prependResetValue) {
            if (cfg.store.data.length) {
                cfg.store.insert(0, new cfg.store.recordType(emptyRecord));
            }
            cfg.store.on('load', function(store, records, options) {
                store.insert(0, new store.recordType(emptyRecord));
            });
        }

        cfg.listeners = {
            scope: this,
            select: function(combo, record, i) {
                this.store.reload();
            }
        }

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
        }.createSequence(Ext.form.ComboBox.prototype.initList);

        return cfg;
    },

    renderMarkup: function() {
        var cm      = this.cm,
            view    = this.view,
            ts      = this.templates,
            fct     = ts.fcell,
            fcb     = [],
            f       = {},
            len     = cm.getColumnCount(),
            last    = len - 1,
            gridId  = this.grid.getId();

        for (var i = 0; i < len; i++) {
            f.id = cm.getColumnId(i) + '-' + gridId;
            //f.dataIndex = cm.getDataIndex(i);
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
        this.recalculateFilterWidth(i, i + 1);
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

        this.recalculateFilterWidth(start, end);
    },

    recalculateFilterWidth: function(from, to) {
        this.filterDom.firstChild.style.width = this.view.getOffsetWidth();
        this.filterDom.firstChild.firstChild.style.width = this.view.getTotalWidth();

        var tds = Ext.get(this.filterDom).query('td');
        from = from || 0;
        if (!to || to >= tds.length) {
            to = tds.length - 1;
        }
        for (var i = from; i <= to; i++) {
            tds[i].style.width = this.view.getColumnWidth(i);
        }
        var gridId = this.grid.getId();
        for (var i = from; i <= to; i++) {
            var filter = this.filters.get('x-grid3-filter-cnt-' + this.cm.getColumnId(i) + '-' + gridId);
            if (filter) {
                filter.doLayout();
            }
        }
    },

    onColumnHiddenChange: function(cm, i, hidden) {
        Ext.fly('x-grid3-filter-' + cm.getColumnId(i) + '-' + this.grid.getId())
            .parent('td').dom.style.display = (hidden ? 'none' : '');
        this.recalculateFilterWidth.defer(50, this, []);
    },

    onScroll: function(left, top) {
        this.filterDom.scrollLeft = left;
        this.filterDom.scrollLeft = left; // second time for IE (1/2 time first fails, other browsers ignore)
    },

    onBeforeLoad: function(store, options) {
        options.params = options.params || {};
        this.cleanParams(options.params);
        var params  = {},
            length  = 0;

        this.filters.each(function(cnt) {
            cnt.items.each(function(field) {
                if (!field.rendered
                    || 0 === field.getValue().length
                    || field.resetValue === field.getValue()) {

                    return true;
                }
                params['filter[' + length + '][field]'] = field.getName();
                params['filter[' + length + '][value]'] = field.getValue();
                if (field.operator) {
                    params['filter[' + length + '][operator]'] = field.operator;
                } else if ('textfield' == field.xtype) {
                    params['filter[' + length + '][operator]'] = 'LIKE';
                }
                if (undefined !== field.table) {
                    params['filter[' + length + '][table]'] = field.table;
                }
                length++;
            });
        });

        // reset start param if filters has been changed
        if (length != this.params.length) {
            this.resetPagination(options, store);
        } else {
            for (var key in params) {
                if (!this.isEqual(this.params.filters[key], params[key])) {
                    this.resetPagination(options, store);
                    break;
                }
            }
        }

        this.params.filters = params;
        this.params.length  = length;

        Ext.apply(options.params, params);
    },

    isEqual: function(v1, v2)
    {
        v1 = v1.toString ? v1.toString() : v1;
        v2 = v2.toString ? v2.toString() : v2;

        return v1 == v2;
    },

    resetPagination: function(o, s) {
        var start = s.paramNames.start;
        o.params[start] = 0;
        if (s.lastOptions && s.lastOptions.params && s.lastOptions.params[start]) {
            s.lastOptions.params[start] = 0;
        }
    },

    destroy: function() {
        this.removeAll();
        this.purgeListeners();
    },

    // functions from ux.GridFilters
    cleanParams: function(p) {
        var regex, key;
        regex = new RegExp('^filter\[[0-9]+\]');
        for (key in p) {
            if (regex.test(key)) {
                delete p[key];
            }
        }
    },

    removeAll : function () {
        if(this.filters){
            Ext.destroy.apply(Ext, this.filters.items);
            // remove all items from the collection
            this.filters.clear();
        }
    }
});
