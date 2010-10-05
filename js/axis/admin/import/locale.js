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

Ext.onReady(function(){
    var import_language_store = new Ext.data.SimpleStore({
        fields: ['id', 'name'],
        id: 'import_language_store'
    });

    var import_language_combo = new Ext.form.ComboBox({
        id: 'import_language_combo',
        store: import_language_store,
        editable: false,
        hideLabel: false,
        triggerAction: 'all',
        displayField: 'name',
        valueField: 'id',
        mode: 'local'
    });

    /*Ext.override(Ext.layout.FormLayout, {
        renderItem : function(c, position, target){
            if(c && !c.rendered && c.isFormField && c.inputType != 'hidden'){
                var args = [
                    c.id, c.fieldLabel,
                    c.labelStyle||this.labelStyle||'',
                    this.elementStyle||'',
                    typeof c.labelSeparator == 'undefined' ? this.labelSeparator : c.labelSeparator,
                    (c.itemCls||this.container.itemCls||'') + (c.hideLabel ? ' x-hide-label' : ''),
                    c.clearCls || 'x-form-clear-left'
                ];
                if(typeof position == 'number'){
                    position = target.dom.childNodes[position] || null;
                }
                if(position){
                    c.formItem = this.fieldTpl.insertBefore(position, args, true);
                }else{
                    c.formItem = this.fieldTpl.append(target, args, true);
                }

                c.on('destroy', c.formItem.remove, c.formItem, {single: true});
                c.render('x-form-el-'+c.id);
            }else {
                Ext.layout.FormLayout.superclass.renderItem.apply(this, arguments);
            }
        }
    });*/
})

function updateLanguages(languages){
    clearLanguages();
    for (var i in languages){
        if (!languages[i].name || !languages[i].languages_id)
            continue;
        Ext.getCmp('import_language_combo').store.add(
            new Ext.data.Record({
                    'id': languages[i].languages_id,
                    'name': languages[i].name
                },languages[i].languages_id
            )
        )
    }

    /*Ext.getCmp('general_fields').add(Ext.getCmp('import_language_combo').cloneConfig({
        fieldLabel: 'Primary language'.l(),
        name: 'primary_language',
        hiddenName: 'primary_language',
        qtipText: 'This language will be used for links'.l()
    }));*/

    for (var i in axis_languages){
        if (!axis_languages[i].name || !axis_languages[i].id)
            continue;
        Ext.getCmp('locale').add(Ext.getCmp('import_language_combo').cloneConfig({
            fieldLabel: axis_languages[i].name,
            name: 'language[' + axis_languages[i].id + ']',
            hiddenName: 'language[' + axis_languages[i].id + ']'
        }));
    }
    Ext.getCmp('locale').doLayout();
    Ext.getCmp('general_information').doLayout();
}

function clearLanguages(){
    var localeTab = Ext.getCmp('locale');
    var generalTab = Ext.getCmp('general_information');
    Ext.each(localeTab.items.items, function(item, index, allItems){
        localeTab.remove(allItems[allItems.length-1], true);
    });
    Ext.getCmp('import_language_combo').store.removeAll();
    /*Ext.each(Ext.getCmp('general_fields').items.items, function(item, index, allItems){
        if (item.name == 'primary_language') {
            generalTab.remove(item);
        }
    })*/
    localeTab.doLayout();
    generalTab.doLayout();
}
