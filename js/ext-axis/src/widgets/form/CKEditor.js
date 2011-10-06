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

Axis.form.CKEditor = Ext.extend(Ext.form.TextArea, {

    hideLabel: true,

    afterRender: function() {
        var self = this;
        if (typeof CKEDITOR != 'object') {
            alert(
                'CKEditor not found at AXIS_ROOT/js/ckeditor/ckeditor.js'
                + "\n"
                + 'Download it from http://download.cksource.com/CKEditor/CKEditor/CKEditor 3.5.3/ckeditor_3.5.3.zip'
                + "\n"
                + 'and unpack under the js folder (AXIS_ROOT/js)'
            );
        }
        CKEDITOR.config.toolbar_axis = [
            ['Source','-','Templates'],
            ['Cut','Copy','Paste','PasteText','PasteFromWord','-'],
            ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
            ['Image','Flash','Table','HorizontalRule','SpecialChar','Iframe'],
            '/',
            ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
            ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
            ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
            ['BidiLtr', 'BidiRtl' ],
            ['Link','Unlink','Anchor'],
            '/',
            ['Styles','Format','Font','FontSize'],
            ['TextColor','BGColor'],
            ['Maximize', 'ShowBlocks']
        ];
        CKEDITOR.config.toolbar_axis_min = [ // feature http://dev.ckeditor.com/ticket/7280
            ['Source'],
            ['Bold','Italic','Underline','Strike'],
            ['NumberedList','BulletedList'],
            ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
            ['Link','Unlink']
        ];
        var tags = ['p', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        CKEDITOR.replace(this.id, {
            toolbar: 'axis',
            height: this.height ? this.height : 200,
            on: {
                blur: function() {
                    self.updateHidden();
                },
                instanceReady: function(ev) {
                    if (!this.getData().length) { // slow initialization fix
                        this.setData(self.getValue());
                    }

                    self.findParentByType('form').doLayout();

                    var w = this.dataProcessor.writer;
                    w.indentationChars  = '  ';
                    w.sortAttributes    = 0;
                    Ext.each(tags, function(el) {
                        w.setRules(el, {
                            indent              : true,
                            breakBeforeOpen     : true,
                            breakAfterOpen      : false,
                            breakBeforeClose    : false,
                            breakAfterClose     : true
                        })
                    });
                }
            }
        });
        Axis.form.CKEditor.superclass.afterRender.call(this);
    },

    setValue: function(value) {
        Axis.form.CKEditor.superclass.setValue.apply(this, [value]);
        if (CKEDITOR.instances[this.id]) {
            CKEDITOR.instances[this.id].setData(value);
        }
    },

    // getValue: function() {
    //     if (CKEDITOR.instances[this.id]) {
    //         CKEDITOR.instances[this.id].updateElement();
    //     }
    //     return Axis.form.CKEditor.superclass.getValue(this);
    // },
    //
    // getRawValue: function() {
    //     CKEDITOR.instances[this.id].updateElement();
    //     return Axis.form.CKEditor.superclass.getRawValue(this);
    // },

    getErrors: function(value) {
        var errors = Axis.form.CKEditor.superclass.getErrors.apply(this, arguments);

        if (!CKEDITOR.instances[this.id] || !CKEDITOR.instances[this.id].container) {
            return errors;
        }

        if (errors.length) {
            $(CKEDITOR.instances[this.id].container.$).addClass('x-form-invalid');
        } else {
            $(CKEDITOR.instances[this.id].container.$).removeClass('x-form-invalid');
        }

        return errors;
    },

    clearInvalid: function() {
        Axis.form.CKEditor.superclass.clearInvalid.apply(this);
        if (CKEDITOR.instances[this.id] && CKEDITOR.instances[this.id].container) {
            $(CKEDITOR.instances[this.id].container.$).removeClass('x-form-invalid');
        }
    },

    updateHidden: function() {
        var value = CKEDITOR.instances[this.id].getData();
        Axis.form.CKEditor.superclass.setValue.apply(this, [value]);
    },

    onDestroy: function() {
        if (CKEDITOR.instances[this.id]) {
            CKEDITOR.instances[this.id].destroy();
            delete CKEDITOR.instances[this.id];
        }
    }
});

Ext.reg('ckeditor', Axis.form.CKEditor);
