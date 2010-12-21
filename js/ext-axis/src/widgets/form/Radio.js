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

Ext.DomQuery.matchers[2] = {
    re: /^(?:([\[\{])(?:@)?([\w-]+)\s?(?:(=|.=)\s?(["']?)(.*?)\4)?[\]\}])/,
    select: 'n = byAttribute(n, "{2}", "{5}", "{3}", "{1}");'
};
Ext.override(Ext.form.Radio, {
    getGroupValue : function(){
        var p = this.el.up('form') || Ext.getBody();
        var c = p.child('input[name="'+this.el.dom.name+'"]:checked', true);
        return c ? c.value : null;
    },
    onClick : function(){
        if(this.el.dom.checked != this.checked){
            var els = this.getCheckEl().select('input[name="' + this.el.dom.name + '"]');
            els.each(function(el){
                if(el.dom.id == this.id){
                    this.setValue(true);
                }else{
                    Ext.getCmp(el.dom.id).setValue(false);
                }
            }, this);
        }
    },
    setValue : function(v){
        if (typeof v == 'boolean') {
            Ext.form.Radio.superclass.setValue.call(this, v);
        } else {
            var r = this.getCheckEl().child('input[name="' + this.el.dom.name + '"][value="' + v + '"]', true);
            if(r){
                Ext.getCmp(r.id).setValue(true);
            }
        }
        return this;
    }
});
