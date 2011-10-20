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

/**
 * Modified version of Ext.ux.maximgb.treegrid.GridView
 * Refresh row method fixed.
 */
Axis.grid.NestedView = Ext.extend(Ext.ux.maximgb.tg.GridView, {

    initTemplates: function() {
        var ts = this.templates || {},
            rowBodyText = [
                '<tr class="x-grid3-row-body-tr" style="{bodyStyle}">',
                    '<td colspan="{cols}" class="x-grid3-body-cell" tabIndex="0" hidefocus="on">',
                        '<div class="x-grid3-row-body">{body}</div>',
                    '</td>',
                '</tr>'
            ].join(""),

            innerText = [
                '<table class="x-grid3-row-table" border="0" cellspacing="0" cellpadding="0" style="{tstyle}">',
                     '<tbody>',
                        '<tr>{cells}</tr>',
                        this.enableRowBody ? rowBodyText : '',
                     '</tbody>',
                '</table>'
            ].join("");

        if (!ts.row) {
            ts.row = new Ext.Template(
                '<div class="x-grid3-row ux-maximgb-tg-level-{level} {alt}" style="{tstyle} {display_style}">',
                    innerText,
                '</div>'
            );
        }

        if (!ts.rowInner) {
            ts.rowInner = new Ext.Template(innerText);
        }

        this.templates = ts;
        Axis.grid.NestedView.superclass.initTemplates.call(this);
    },

    refreshRow: function(record) {
        var store     = this.ds,
            colCount  = this.cm.getColumnCount(),
            columns   = this.getColumnData(),
            last      = colCount - 1,
            cls       = ['x-grid3-row'],
            rowParams = {
                tstyle: String.format("width: {0};", this.getTotalWidth())
            },
            colBuffer = [],
            cellTpl   = this.templates.cell,
            rowIndex, row, column, meta, css, i;

        if (Ext.isNumber(record)) {
            rowIndex = record;
            record   = store.getAt(rowIndex);
        } else {
            rowIndex = store.indexOf(record);
        }

        //the record could not be found
        if (!record || rowIndex < 0) {
            return;
        }

        //builds each column in this row
        for (i = 0; i < colCount; i++) {
            column = columns[i];

            if (i == 0) {
                css = 'x-grid3-cell-first';
            } else {
                css = (i == last) ? 'x-grid3-cell-last ' : '';
            }

            meta = {
                id      : column.id,
                style   : column.style,
                css     : css,
                attr    : "",
                cellAttr: ""
            };
            // Need to set this after, because we pass meta to the renderer
            meta.value = column.renderer.call(column.scope, record.data[column.name], meta, record, rowIndex, i, store);

            if (Ext.isEmpty(meta.value)) {
                meta.value = '&#160;';
            }

            if (this.markDirty && record.dirty && typeof record.modified[column.name] != 'undefined') {
                meta.css += ' x-grid3-dirty-cell';
            }

            // ----- Modification start
            if (meta.id == this.grid.master_column_id) {
                meta.treeui = this.renderCellTreeUI(record, store);
                cellTpl = this.templates.mastercell;
            }
            else {
                cellTpl = this.templates.cell;
            }
            // ----- End of modification

            colBuffer[i] = cellTpl.apply(meta);
        }

        row = this.getRow(rowIndex);
        row.className = '';

        if (this.grid.stripeRows && ((rowIndex + 1) % 2 === 0)) {
            cls.push('x-grid3-row-alt');
        }

        if (this.getRowClass) {
            rowParams.cols = colCount;
            cls.push(this.getRowClass(record, rowIndex, rowParams, store));
        }

        this.fly(row).addClass(cls).setStyle(rowParams.tstyle);
        rowParams.cells = colBuffer.join("");
        row.innerHTML = this.templates.rowInner.apply(rowParams);

        this.fireEvent('rowupdated', this, rowIndex, record);
    }
});
