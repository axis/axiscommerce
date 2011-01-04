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

Ext.onReady(function(){


    myRow = function (config1, config2) {
        _myBox = function (config) {
            return Ext.applyIf(config || {}, {
                xtype: 'fieldset'
            });
        };
        column1 = _myBox(config1);
        column2 = _myBox(config2);
//    myRow = function (column1, column2) {
        column1.anchor = '-10';
        return {
            layout: 'column',
            anchor: '100%',
            border: false,
            defaults: {
                border: false,
                columnWidth: '.5',
                layout: 'form'
            },
            items: [{
                items: column1
            }, {
                items: column2
            }]
        }
    };

}, this);