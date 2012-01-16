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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

Ext.onReady(function(){

//    var panelMain =
    new Ext.TabPanel({
        id: 'panel-form-order',
        anchor: Ext.isWebKit ? 'undefined 100%' : '100% 100%',
        border: false,
        defaults: {
            autoScroll: true,
            bodyStyle: 'padding: 10px',
            hideMode: 'offsets',
            layout: 'form'
        },
        deferredRender: false,
        plain: true,
        activeTab: 0,
        items:[Ext.getCmp('tab-order-info'), Ext.getCmp('tab-status-history')]
    });
    
}, this);