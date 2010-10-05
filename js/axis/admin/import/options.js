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
    var import_checkbox = new Ext.form.Checkbox({
        id: 'import_checkbox',
        fieldLabel: 'Option'.l(), 
        name: 'name'
    })
})

function updateImportOptions(options){
    var optionGroup = Ext.getCmp('import_options');
    var importCheckbox = Ext.getCmp('import_checkbox');
    for (i in options){
        optionGroup.add(importCheckbox.cloneConfig({
            fieldLabel: i.l(),
            name: 'data[' + options[i] + ']'
        }));
    }
    optionGroup.doLayout();
}

function clearOptions(){
    var optionGroup = Ext.getCmp('import_options');
    Ext.each(optionGroup.items.items, function(item, index, allItems){
        optionGroup.remove(allItems[allItems.length-1], true);
    })
    optionGroup.doLayout();
}
