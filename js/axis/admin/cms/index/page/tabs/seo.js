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
 */

Ext.onReady(function() {

    for (var id in Axis.locales) {
        PageWindow.formFields.push({
            name: 'content[' + id + '][link]',
            mapping: 'content.lang_' + id + '.link'
        }, {
            name: 'content[' + id + '][meta_title]',
            mapping: 'content.lang_' + id + '.meta_title'
        }, {
            name: 'content[' + id + '][meta_description]',
            mapping: 'content.lang_' + id + '.meta_description'
        }, {
            name: 'content[' + id + '][meta_keyword]',
            mapping: 'content.lang_' + id + '.meta_keyword'
        });
    }

    PageWindow.addTab({
        title: 'Seo'.l(),
        bodyStyle: 'padding: 10px',
        defaults: {
            anchor: '-20',
            border: false
        },
        items: [{
            fieldLabel: 'Link'.l(),
            name: 'content[link]',
            xtype: 'langset'
        }, {
            fieldLabel: 'Title'.l(),
            xtype: 'langset',
            name: 'content[meta_title]'
        }, {
            fieldLabel: 'Description'.l(),
            defaultType: 'textarea',
            xtype: 'langset',
            name: 'content[meta_description]'
        }, {
            fieldLabel: 'Keywords'.l(),
            defaultType: 'textarea',
            xtype: 'langset',
            name: 'content[meta_keyword]'
        }]
    }, 20);
});
