
/**
 *  http://www.sencha.com/forum/showthread.php?28826-Calculated-fields
 */

Ext.namespace('Ext.ux.data');
Ext.ux.data.CalcRecord = function(data, id) {
    Ext.ux.data.CalcRecord.superclass.constructor.call(this, data, id);
    this.calcFields();
}
Ext.ux.data.CalcRecord.create = function(o){
    var f = Ext.extend(Ext.ux.data.CalcRecord, {});
    var p = f.prototype;
    p.fields = new Ext.util.MixedCollection(false, function(field){
        return field.name;
    });
    for(var i = 0, len = o.length; i < len; i++){
        p.fields.add(new Ext.data.Field(o[i]));
    }
    f.getField = function(name){
        return p.fields.get(name);
    };
    return f;
};
Ext.extend(Ext.ux.data.CalcRecord, Ext.data.Record, {
set: function(name, value) {
    if(String(this.data[name]) == String(value)){
        return;
    }
    this.dirty = true;
    if(!this.modified){
        this.modified = {};
    }
    if(typeof this.modified[name] == 'undefined'){
        this.modified[name] = this.data[name];
    }
    this.data[name] = value;
    this.calcFields(name);
    if(!this.editing && this.store){
        this.store.afterEdit(this);
    }
},
calcFields: function(name) {
    this.fields.each(function(field) {
        if ((field.name != name) && (typeof field.calc == 'function') &&
            (!name || (!field.dependencies || field.dependencies.indexOf(name) != -1))) {
            var value = field.calc(this);
            if (!name || field.notDirty) {
                this.data[field.name] = value;
            } else {
                this.set(field.name, value);
            }
        }
    }, this);
}
});