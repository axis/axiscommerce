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

Axis.DelayedLoader = Ext.extend(Ext.util.Observable, {

    /**
     * @param {Ext.util.Observable} el
     */

    /**
     * @param {Ext.data.Store} ds
     */

    /**
     * @param {String} event to load store
     */
    event: 'activate',

    /**
     * @param {Boolean} singleLoad True to load store once
     */
    singleLoad: true,

    /**
     * @param {Function} loadFn
     */
    load: function() {
        if (this.singleLoad && this.state === 'loaded') {
            return;
        }
        this.loadFn();
    },

    loadFn: function() {
        this.ds.load();
    },

    constructor: function(config) {
        Ext.apply(this, config);
        this.ds.on('load', function() {
            this.state = 'loaded';
        }, this);
        this.el.on(this.event, this.load, this);
    }
});
