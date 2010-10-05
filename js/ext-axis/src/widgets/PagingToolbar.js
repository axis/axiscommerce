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

/**
 * Added ability to set pageSize
 */
Axis.PagingToolbar = Ext.extend(Ext.PagingToolbar, {

    pageSize: 25,

    displayInfo: true,

    enableOverflow: true,

    beforePageSizeText: 'Show'.l(),

    afterPageSizeText: 'per page'.l(),

    initComponent: function() {
        var pagingItems = [this.first = new Ext.Toolbar.Button({
            tooltip: this.firstText,
            overflowText: this.firstText,
            iconCls: 'x-tbar-page-first',
            disabled: true,
            handler: this.moveFirst,
            scope: this
        }), this.prev = new Ext.Toolbar.Button({
            tooltip: this.prevText,
            overflowText: this.prevText,
            iconCls: 'x-tbar-page-prev',
            disabled: true,
            handler: this.movePrevious,
            scope: this
        }), '-', this.beforePageText,
        this.inputItem = new Ext.form.NumberField({
            cls: 'x-tbar-page-number',
            allowDecimals: false,
            allowNegative: false,
            enableKeyEvents: true,
            selectOnFocus: true,
            submitValue: false,
            listeners: {
                scope: this,
                keydown: this.onPagingKeyDown,
                blur: this.onPagingBlur
            }
        }), this.afterTextItem = new Ext.Toolbar.TextItem({
            text: String.format(this.afterPageText, 1)
        }), '-', this.next = new Ext.Toolbar.Button({
            tooltip: this.nextText,
            overflowText: this.nextText,
            iconCls: 'x-tbar-page-next',
            disabled: true,
            handler: this.moveNext,
            scope: this
        }), this.last = new Ext.Toolbar.Button({
            tooltip: this.lastText,
            overflowText: this.lastText,
            iconCls: 'x-tbar-page-last',
            disabled: true,
            handler: this.moveLast,
            scope: this
        }), '-', this.beforePageSizeText,
        this.pageSizeItem = new Ext.form.ComboBox({
            displayField: 'paging',
            mode: 'local',
            store: new Ext.data.ArrayStore({
                data: [[1, '10'], [2, '25'], [3, '50'], [4, '250']],
                fields: ['id', 'paging']
            }),
            triggerAction: 'all',
            value: this.store.baseParams.limit || this.pageSize,
            width: 40,
            enableKeyEvents: true,
            listeners: {
                scope: this,
                keydown: this.onPagingKeyDown,
                blur: this.onPagingBlur,
                beforeselect: this.onPagingSizeSelect
            }
        }), this.afterPageSizeText, '-',
        this.refresh = new Ext.Toolbar.Button({
            tooltip: this.refreshText,
            overflowText: this.refreshText,
            iconCls: 'x-tbar-loading',
            handler: this.doRefresh,
            scope: this
        })];


        var userItems = this.items || this.buttons || [];
        if (this.prependButtons) {
            this.items = userItems.concat(pagingItems);
        }else{
            this.items = pagingItems.concat(userItems);
        }
        delete this.buttons;
        if(this.displayInfo){
            this.items.push('->');
            this.items.push(this.displayItem = new Ext.Toolbar.TextItem({}));
        }
        Ext.PagingToolbar.superclass.initComponent.call(this);
        this.addEvents(
            'change',
            'beforechange'
        );
        this.on('afterlayout', this.onFirstLayout, this, {single: true});
        this.cursor = 0;
        this.bindStore(this.store, true);
    },

    // private
    getPageData: function() {
        var total = this.store.getTotalCount();
        return {
            total: total,
            activePage: Math.ceil((this.cursor+this.pageSize)/this.pageSize),
            pages: total < this.pageSize ? 1 : Math.ceil(total/this.pageSize),
            pageSize: this.pageSize
        };
    },

    // private
    readPageSize: function(d) {
        var v = this.pageSizeItem.getValue(), pageSize;
        if (!v || isNaN(pageSize = parseInt(v, 10))) {
            this.pageSizeItem.setValue(d.pageSize);
            return false;
        }
        return pageSize;
    },

    // private
    onPagingSizeSelect: function(c, r, i) {
        var d = this.getPageData(), pageNum = 1, pageSize;
        this.inputItem.setValue(pageNum);

        if (r.data.paging == d.pageSize) {
            return;
        }

        if (isNaN(r.data.paging)) { // all records
            pageSize = 1000000;
        } else {
            pageSize = parseInt(r.data.paging, 10);
        }

        this.pageSize = pageSize;
        pageNum = Math.min(Math.max(1, pageNum), d.pages) - 1;
        this.doLoad(pageNum * this.pageSize);
    },

    // private
    onPagingBlur: function(e) {
        var d = this.getPageData();
        this.inputItem.setValue(d.activePage);
        this.pageSizeItem.setValue(d.pageSize);
    },

    // private
    onPagingKeyDown: function(field, e) {
        var k = e.getKey(), d = this.getPageData(), pageNum, pageSize;
        if (k == e.RETURN) {
            e.stopEvent();
            pageNum = this.readPage(d);
            pageSize = this.readPageSize(d);
            if (pageNum !== false || pageSize !== false) {
                if (pageSize !== false) {
                    this.pageSize = pageSize;
                }
                pageNum = Math.min(Math.max(1, pageNum), d.pages) - 1;
                this.doLoad(pageNum * this.pageSize);
            }
        } else if (k == e.HOME || k == e.END) {
            e.stopEvent();
            pageNum = k == e.HOME ? 1 : d.pages;
            field.setValue(pageNum);
        } else if (k == e.UP || k == e.PAGEUP || k == e.DOWN || k == e.PAGEDOWN) {
            e.stopEvent();
            if ((pageNum = this.readPage(d))) {
                var increment = e.shiftKey ? 10 : 1;
                if (k == e.DOWN || k == e.PAGEDOWN) {
                    increment *= -1;
                }
                pageNum += increment;
                if (pageNum >= 1 & pageNum <= d.pages) {
                    field.setValue(pageNum);
                }
            }
        }
    },

    // private
    doLoad : function(start){
        var o = {}, pn = this.getParams();
        o[pn.start] = start;
        this.store.baseParams.limit = this.pageSize;
        if(this.fireEvent('beforechange', this, o) !== false){
            this.store.load({params:o});
        }
    }

});

Ext.reg('paging', Axis.PagingToolbar);
