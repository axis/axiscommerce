var inlineField = function() {
    return {
        init: function(field) {
            field.addInlineClass = function() {
                if (this.readOnly) {
                    this.el.addClass('x-form-inline-field-readonly');
                }

                this.el.addClass(this.isValid() ?
                    'x-form-inline-field' : '');
//                    'x-form-inline-field' : 'x-form-inline-invalid-field');
                if (this.trigger && this.el.next()) {
                    this.el.next().addClass('x-hidden');
                }
            };
            field.removeInlineClass = function() {
                if (this.readOnly) {
                    return;
                }
                this.el.removeClass(['x-form-inline-field'/*, 'x-form-inline-invalid-field'*/]);
                if (this.trigger && this.el.next()) {
                    this.el.next().removeClass('x-hidden');
                }
            };
            field.on('afterrender', field.addInlineClass, field);
            field.on('blur', field.addInlineClass, field);
            field.on('focus', field.removeInlineClass, field);
        }
    }
}();
