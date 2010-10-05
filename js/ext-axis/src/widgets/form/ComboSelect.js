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

Ext.ux.Andrie.Select.override({
    
    // private
    assertValue: function() {
//        var val = this.getRawValue(),
//            rec = this.findRecord(this.displayField, val);
//
//        if(!rec && this.forceSelection){
//            if(val.length > 0 && val != this.emptyText){
//                this.el.dom.value = Ext.value(this.lastSelectionText, '');
//                this.applyEmptyText();
//            }else{
//                this.clearValue();
//            }
//        }else{
//            if(rec){
//                // onSelect may have already set the value and by doing so
//                // set the display field properly.  Let's not wipe out the
//                // valueField here by just sending the displayField.
//                if (val == rec.get(this.displayField) && this.value == rec.get(this.valueField)){
//                    return;
//                }
//                val = rec.get(this.valueField || this.displayField);
//            }
//            this.setValue(val);
//        }
    },
    
    setValue:function(v){
        var result = [],
                resultRaw = [];
        if (!(v instanceof Array)){
            if (this.separator && this.separator !== true){
                
                // bo axis fix
                v = '' + v;
                // eo axis fix
                
                v = v.split(String(this.separator));
            }else{
                v = [v];
            }
        }
        else if (!this.multiSelect){
            v = v.slice(0,1);
        } 
        for (var i=0, len=v.length; i<len; i++){
            var value = v[i];
            var text = value;
            if(this.valueField){
                var r = this.findRecord(this.valueField || this.displayField, value);
                if(r){
                    text = r.data[this.displayField];
                }else if(this.forceSelection){
                    continue;
                }
            }
            result.push(value);
            resultRaw.push(text);
        }
        v = result.join(this.separator || ',');
        text = resultRaw.join(this.displaySeparator || this.separator || ',');
        
        this.commonChangeValue(v, text, result, resultRaw);
        
        if (this.history && !this.multiSelect && this.mode == 'local'){
            this.addHistory(this.valueField?this.getValue():this.getRawValue());
        }
        if (this.view){
            this.view.clearSelections();
            this.selectByValue(this.valueArray);
        }
    }
    
});
