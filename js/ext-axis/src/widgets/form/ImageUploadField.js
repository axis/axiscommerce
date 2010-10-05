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

/**
 * Element provides single upload of the image
 * Utilize some of Ext.ux.UploadPanel.js and Ext.ux.form.FileUploadField functionality
 */
Axis.form.ImageUploadField = Ext.extend(Ext.form.TextField, {

    anchor: '100%',

    blankImage: Axis.skinUrl + '/images/no_image.gif',

    cls: 'x-form-field-image-upload',

    previewHeight: 22,

    previewWidth: 22,

    uploadText: 'Upload'.l(),

    browseText: 'Choose from uploaded'.l(),

    urlText: 'Url'.l(),

    buttonOnly: true,

    autoSize: Ext.emptyFn,

    // private
    initComponent: function() {

        var fields = [
            {name:'id', type:'text', system:true},
            {name:'shortName', type:'text', system:true},
            {name:'fileName', type:'text', system:true},
            {name:'filePath', type:'text', system:true},
            {name:'fileCls', type:'text', system:true},
            {name:'input', system:true},
            {name:'form', system:true},
            {name:'state', type:'text', system:true},
            {name:'error', type:'text', system:true},
            {name:'progressId', type:'int', system:true},
            {name:'bytesTotal', type:'int', system:true},
            {name:'bytesUploaded', type:'int', system:true},
            {name:'estSec', type:'int', system:true},
            {name:'filesUploaded', type:'int', system:true},
            {name:'speedAverage', type:'int', system:true},
            {name:'speedLast', type:'int', system:true},
            {name:'timeLast', type:'int', system:true},
            {name:'timeStart', type:'int', system:true},
            {name:'pctComplete', type:'int', system:true}
        ];

        this.store = new Ext.data.SimpleStore({
            id: 0,
            fields: fields,
            data: []
        });

        this.uploader = new Axis.FileUploader({
            enableProgress: false,
            id: 'file-upload',
            maxFileSize: null,
            store: this.store,
            url: this.url
        });

        // relay uploader events
        this.relayEvents(this.uploader, [
            'beforeallstart',
            'allfinished',
            'progress'
        ]);

        Axis.form.ImageUploadField.superclass.initComponent.call(this);

        this.addEvents(
            'browseclick',
            'urlclick'
        );

        this.on({
            allfinished: {
                scope: this,
                fn: function(uploader) {
                    if (uploader.store.data.items[0].get('state') != 'done') {
                        return;
                    }
                    // update input field and thumbnail
                    this.setValue(uploader.store.data.items[0].get('file'));
//                    this.removeAll();
                }
            }
        });
    },

    // private
    onRender: function(ct, position) {
        Axis.form.ImageUploadField.superclass.onRender.call(this, ct, position);

        this.uploadWrap = this.el.wrap({
            cls: 'x-form-field-wrap x-form-file-wrap'
        });
        this.wrap = this.uploadWrap.wrap({
            cls: 'x-form-image-upload-field-wrap'
        });
        this.el.addClass('x-form-image-upload-text');

        this.uploadButton = new Ext.Button({
            cls: 'x-form-file-upload',
            renderTo: this.uploadWrap,
            scope: this,
            text: this.uploadText
        });
        this.createFileInput();
        this.bindListeners();

        if (this.buttonOnly) {
            this.el.setVisibilityMode(Ext.Element.DISPLAY).hide();
        }

        this.browseButton = new Ext.Button({
            cls: 'x-form-file-browse',
            handler: this.onBrowseClick,
            renderTo: this.wrap,
            scope: this,
            text: this.browseText
        });
        this.urlButton = new Ext.Button({
            cls: 'x-form-file-url x-hidden',
            handler: this.onUrlClick,
            renderTo: this.wrap,
            scope: this,
            text: this.urlText
        });
        this.imagePreviewLink = this.wrap.insertFirst({
            cls: 'x-form-file-preview',
            href: this.blankImage,
            tag: 'a',
            target: '_blank'
        });
        this.imagePreview = this.imagePreviewLink.createChild({
            height: this.previewHeight,
            tag: 'img',
            src: this.blankImage,
            width: this.previewWidth
        });
    },

    afterRender: function() {
        Axis.form.ImageUploadField.superclass.afterRender.call(this);
        this.afterMethod('onResize', this.doResize, this);
    },

    bindListeners: function(){
        this.fileInput.on({
            scope: this,
            mouseenter: function() {
                this.uploadButton.addClass(['x-btn-over','x-btn-focus'])
            },
            mouseleave: function(){
                this.uploadButton.removeClass(['x-btn-over','x-btn-focus','x-btn-click'])
            },
            mousedown: function(){
                this.uploadButton.addClass('x-btn-click')
            },
            mouseup: function(){
                this.uploadButton.removeClass(['x-btn-over','x-btn-focus','x-btn-click'])
            },
            change: function(){
                this.onSetFile();
            }
        });
    },

    createFileInput: function() {
//        var bWidth = this.uploadButton.getEl().getWidth();
        this.fileInput = this.uploadWrap.createChild({
            id: this.getFileInputId(),
            name: 'image',
            cls: 'x-form-file',
            tag: 'input',
            type: 'file',
            size: 1,
            style: 'cursor:pointer'
//            style: 'width:' + (this.buttonOnly ? bWidth : bWidth + this.el.getWidth()) + 'px'
        });
    },

    setValue: function(v) {
        Axis.form.ImageUploadField.superclass.setValue.call(this, v);

        if (this.rendered) {
            this.imagePreviewLink.set({
                href: Axis.baseUrl + '/' + this.rootPath + v
            });
            this.imagePreview.set({
                src: ''
            });
            this.imagePreview.set({
                src: Axis.secureUrl + '/' + this.rootPath + v
            });
        }
    },

    doResize: function(w, h) {
        if (typeof w == 'number') {
            this.el.setWidth(
                w
                - this.uploadButton.getEl().getWidth()
                - this.browseButton.getEl().getWidth()
                - this.urlButton.getEl().getWidth()
                - this.imagePreview.getWidth()
                - 12 // button margins
            );
        }
    },

    // private
    getFileInputId: function(){
        return this.id + '-file';
    },

    getFileName: function(inp) {
        return inp.getValue().split(/[\/\\]/).pop();
    },

    onSetFile: function() {
        this.fileInput.id = 'image';
        var rec = new this.store.recordType({
            input: this.fileInput,
            fileName: this.getFileName(this.fileInput),
            state: 'queued'
        }, this.fileInput.id);
        rec.commit();
        this.store.add(rec);

        this.uploader.upload();

        this.createFileInput();
        this.bindListeners();
    },

    onBrowseClick: function() {
        this.fireEvent('browseclick', this);
        if (!this.mediaBrowser) {
            this.mediaBrowser = new Axis.ImageBrowser({
                rootPath: this.rootPath,
                rootText: this.rootText,
                listeners: {
                    okpress: {
                        scope: this,
                        fn: function(records) {
                            Ext.each(records, function(r) {
                                this.setValue(r.get('absolute_url').replace(Axis.secureUrl + '/' + this.rootPath, ''));
                                return;
                            }, this);
                        }
                    }
                }
            });
        }
        this.mediaBrowser.show();
    },

    onUrlClick: function() {
        this.fireEvent('urlclick', this);
    },

    onRemoveFile: function(record) {
        if (true !== this.eventsSuspended) {
            return;
        }

        // remove DOM elements
        var inp = record.get('input');
        var wrap = inp.up('em');
        inp.remove();
        if (wrap) {
            wrap.remove();
        }

        // remove record from store
        this.store.remove(record);
    },

    removeAll: function() {
        var suspendState = this.eventsSuspended;
        if (false !== this.eventsSuspended) {
            return false;
        }
        this.suspendEvents();

        this.store.each(this.onRemoveFile, this);

        this.eventsSuspended = suspendState;
    },

    reset: function() {
        if (this.rendered) {
            this.fileInput.remove();
            this.createFileInput();
            this.bindListeners();
        }
        Axis.form.ImageUploadField.superclass.reset.call(this);
    },

    // private
    onDestroy: function() {
        Axis.form.ImageUploadField.superclass.onDestroy.call(this);

        // destroy uploader
        if (this.uploader) {
            this.uploader.stopAll();
            this.uploader.purgeListeners();
            this.uploader = null;
        }

        // destroy store
        if(this.store) {
            this.store.purgeListeners();
            this.store.destroy();
            this.store = null;
        }

        if (this.imagePreviewLink) {
            this.imagePreviewLink.remove();
        }

        if (this.imagePreview) {
            this.imagePreview.remove();
        }

        Ext.destroy(
            this.fileInput,
            this.uploadButton,
            this.browseButton,
            this.urlButton,
            this.wrap,
            this.uploadWrap
        );
    },

    onDisable: function() {
        Axis.form.ImageUploadField.superclass.onDisable.call(this);
        this.doDisable(true);
    },

    onEnable: function() {
        Axis.form.ImageUploadField.superclass.onEnable.call(this);
        this.doDisable(false);
    },

    // private
    doDisable: function(disabled) {
        this.fileInput.dom.disabled = disabled;
        this.uploadButton.setDisabled(disabled);
        this.browseButton.setDisabled(disabled);
        this.urlButton.setDisabled(disabled);
    },

    // private
    preFocus: Ext.emptyFn,

    // private
    alignErrorIcon: function() {
        this.errorIcon.alignTo(this.wrap, 'tl-tr', [2, 0]);
    }
});

Ext.reg('imageuploadfield', Axis.form.ImageUploadField);