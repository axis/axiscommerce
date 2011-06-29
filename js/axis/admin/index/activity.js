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

var ActivityPanel = {

    el: null,

    tabs: [],

    /**
     * @param {Object} tab
     * @param {integer} sortOrder
     */
    addTab: function(tab, sortOrder) {
        if (!ActivityPanel.tabCollection) {
            ActivityPanel.tabCollection = new Ext.util.MixedCollection();
        }
        ActivityPanel.tabCollection.add(sortOrder, tab);
        ActivityPanel.tabCollection.keySort('ASC', function(a, b) {
            return a - b;
        });
        ActivityPanel.tabs.splice(
            ActivityPanel.tabCollection.indexOf(tab), 0, tab
        );
    }

};

Ext.onReady(function() {

    ActivityPanel.el = new Ext.TabPanel({
        activeTab: 0,
        flex: 1,
        plain: true,
        items: ActivityPanel.tabs
    });

});