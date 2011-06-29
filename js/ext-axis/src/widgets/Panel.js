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
 * Panel for standard Axis admin 2-columns interface
 */
Axis.Panel = Ext.extend(Ext.Panel, {

    border: false,

    frame: false,

    layout: 'border',

    plugins: [],

    renderTo: 'inside-box',

    listeners: {
        'afterrender': function(cmp) {
            if (undefined === cmp.height) {
                var height = 550;
                if (typeof window.innerHeight != 'undefined') {
                    height = (window.innerHeight < 550) ? 550 : window.innerHeight - 235;
                }
                cmp.setHeight(height);
            }
            if (undefined === cmp.width) {
                cmp.setWidth(Ext.get(cmp.renderTo).getWidth());
            }
            if (undefined === cmp.width || undefined === cmp.height) {
                cmp.doLayout();
            }
        }
    },

    initComponent: function() {
        this.plugins.push(
            new Ext.ux.PanelResizer()
        );
        Axis.Panel.superclass.initComponent.call(this);
    }
});
