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

var Seo = {

    clearData: Ext.emptyFn,

    loadData: Ext.emptyFn,

    getData: function() {
        var form = ProductWindow.form.getForm(),
            url  = form.findField('key_word');

        if ('' === url.getValue()) {
            var name = form.findField('description[name]').getValue();
            for (var i in name) {
                name = name[i];
                break;
            }
            name = Ext.util.Format.trim(name)
            name = name.toLowerCase();
            name = name.replace(/\s+/g, '-');
            url.setValue(name);
        }

        return [];
    }
};

Ext.onReady(function() {

    ProductWindow.formFields.push(
        {name: 'key_word', mapping: 'key_word'}
    );

    for (var id in Axis.languages) {
        ProductWindow.formFields.push({
            name: 'description[' + id + '][meta_title]',
            mapping: 'description.lang_' + id + '.meta_title'
        }, {
            name: 'description[' + id + '][meta_description]',
            mapping: 'description.lang_' + id + '.meta_description'
        }, {
            name: 'description[' + id + '][meta_keyword]',
            mapping: 'description.lang_' + id + '.meta_keyword'
        }, {
            name: 'description[' + id + '][image_seo_name]',
            mapping: 'description.lang_' + id + '.image_seo_name'
        });
    }

    ProductWindow.addTab({
        title: 'Seo'.l(),
        bodyStyle: 'padding: 10px',
        defaults: {
            anchor: '-20',
            border: false
        },
        items: [{
            anchor: '100%',
            layout: 'column',
            defaults: {
                border: false,
                columnWidth: '.5',
                layout: 'form'
            },
            items: [{
                items: [{
                    anchor: '-5',
                    fieldLabel: 'SEO url'.l(),
                    name: 'key_word',
                    xtype: 'textfield'
                }]
            }, {
                items: [{
                    anchor: '100%',
                    defaultType: 'textfield',
                    fieldLabel: 'Image SEO prefix'.l(),
                    name: 'description[image_seo_name]',
                    xtype: 'langset'
                }]
            }]
        }, {
            fieldLabel: 'Page title'.l(),
            defaultType: 'textfield',
            name: 'description[meta_title]',
            xtype: 'langset'
        }, {
            fieldLabel: 'Meta description'.l(),
            defaultType: 'textarea',
            name: 'description[meta_description]',
            xtype: 'langset'
        }, {
            fieldLabel: 'Meta keywords'.l(),
            defaultType: 'textarea',
            name: 'description[meta_keyword]',
            xtype: 'langset'
        }]
    }, 20);

    ProductWindow.dataObjects.push(Seo);
});
