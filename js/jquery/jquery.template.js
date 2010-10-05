jQuery.Template = function(html){
    var a = arguments;
    if(html instanceof Array){
        html = html.join("");
    }else if(a.length > 1){
        var buf = [];
        for(var i = 0, len = a.length; i < len; i++){
            if(typeof a[i] == 'object'){
                Ext.apply(this, a[i]);
            }else{
                buf[buf.length] = a[i];
            }
        }
        html = buf.join('');
    }
    
    this.html = html;
    if(this.compiled){
        this.compile();   
    }
};
jQuery.Template.prototype = {
    
    applyTemplate : function(values){
        if(this.compiled){
            return this.compiled(values);
        }
        var tpl = this;
        var fn = function(m, name, format, args){
            return values[name] !== undefined ? values[name] : "";
        };
        return this.html.replace(this.re, fn);
    },
    
    
    set : function(html, compile){
        this.html = html;
        this.compiled = null;
        if(compile){
            this.compile();
        }
        return this;
    },
    
    
    disableFormats : false,
    
    
    re : /\{([\w-]+)(?:\:([\w\.]*)(?:\((.*?)?\))?)?\}/g,
    
    
    compile : function(){
        var sep = jQuery.browser.mozilla ? "+" : ",";
        var fn = function(m, name, format, args){
            args= ''; 
			format = "(values['" + name + "'] == undefined ? '' : ";
            return "'"+ sep + format + "values['" + name + "']" + args + ")"+sep+"'";
        };
        var body;
        
        if(jQuery.browser.mozilla){
            body = "this.compiled = function(values){ return '" +
                   this.html.replace(/\\/g, '\\\\').replace(/(\r\n|\n)/g, '\\n').replace(/'/g, "\\'").replace(this.re, fn) +
                    "';};";
        }else{
            body = ["this.compiled = function(values){ return ['"];
            body.push(this.html.replace(/\\/g, '\\\\').replace(/(\r\n|\n)/g, '\\n').replace(/'/g, "\\'").replace(this.re, fn));
            body.push("'].join('');};");
            body = body.join('');
        }
        eval(body);
        return this;
    },
    
    call : function(fnName, value, allValues){
        return this[fnName](value, allValues);
    }
};

jQuery.Template.prototype.apply = jQuery.Template.prototype.applyTemplate;

jQuery.Template.from = function(el, config){
    el = jQuery('#' + el)[0];
    return new jQuery.Template(el.value || el.innerHTML, config || '');
};

jQuery.fn.tplAppend = function(values, tpl) {
	return this.append(tpl.applyTemplate(values));
}