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

var ImageGrid = {

    el: null,

    clearData: function() {
        ImageGrid.el.store.loadData([]);
    },

    loadData: function(data) {
        ImageGrid.el.store.loadData(data.images);
    },

    getData: function() {
        var modified = ImageGrid.el.store.getModifiedRecords();

        if (!modified.length) {
            return;
        }

        var data = {};
        for (var i = modified.length - 1; i >= 0; i--) {
            data[modified[i].id] = modified[i]['data'];
        }

        return {
            'image': data
        };
    }
};

Ext.onReady(function() {

    var imageReader = [
        {name: 'id', type: 'int'},
        {name: 'path'},
        {name: 'sort_order'},
        {name: 'is_thumbnail', type: 'int'},
        {name: 'is_listing', type: 'int'},
        {name: 'is_base', type: 'int'},
        {name: 'remove', type: 'int'}
    ];
    var imageTitles = [];

    for (var id in Axis.languages) {
        imageReader.push(
            {name: 'title_' + id}
        );
        imageTitles.push({
            dataIndex: 'title_' + id,
            editor: new Ext.form.TextField({
                maxLength: 128
            }),
            header: 'Title ({language})'.l('core', Axis.languages[id])
        });
    }

    var imageStore = new Ext.data.Store({
        mode: 'local',
        pruneModifiedRecords: true,
        reader: new Ext.data.JsonReader({
            idProperty: 'id',
            fields: imageReader
        })
    });

    var imageThumbnail = new Axis.grid.RadioColumn({
        dataIndex: 'is_thumbnail',
        header: 'Thumbnail'.l(),
        width: 60
    });

    var imageListing = new Axis.grid.RadioColumn({
        dataIndex: 'is_listing',
        header: 'Listing'.l(),
        width: 60
    });

    var imageBase = new Axis.grid.RadioColumn({
        dataIndex: 'is_base',
        header: 'Base'.l(),
        width: 60
    });

    var imageDelete = new Axis.grid.CheckColumn({
        dataIndex: 'remove',
        header: 'Delete'.l(),
        width: 60
    });

    var imageCols = [{
        dataIndex: 'path',
        header: 'Image'.l(),
        renderer: function(v) {
            return String.format(
                '<a href="{0}" target="_blank" title="{1}" class="x-grid-image-preview">' +
                    '<img src="{0}" alt="{1}"/>' +
                '</a>',
                Product.imageRoot + v,
                v
            );
        },
        width: 50
    }, {
        align: 'right',
        dataIndex: 'sort_order',
        editor: new Ext.form.NumberField({
            allowBlank: true,
            allowNegative: false,
            maxValue: 255
        }),
        header: 'Sort Order'.l(),
        width: 60
    },
        imageThumbnail,
        imageListing,
        imageBase,
        imageDelete
    ];
    for (var i = 0, limit = imageTitles.length; i < limit; i++) {
        imageCols.splice(i + 1, 0, imageTitles[i]);
    }

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

    var uploaderStore = new Ext.data.SimpleStore({
        id: 0,
        fields: fields,
        data: []
    });

    var fileUploader = new Axis.FileUploader({
        enableProgress: false,
        events: [],
        id: 'product-file-upload',
        maxFileSize: null,
        store: uploaderStore,
        url: Axis.getUrl('catalog/product/save-image'),
        listeners: {
            allfinished: {
                scope: this,
                fn: function(uploader) {
                    if (uploader.store.data.items[0].get('state') != 'done') {
                        return;
                    }

                    var record = new imageStore.recordType({
                        path: uploader.store.data.items[0].get('file'),
                        sort_order: 50,
                        is_thumbnail: 0,
                        is_listing: 0,
                        is_base: 0,
                        remove: 0
                    });
                    record.dirty = true;
                    if (!record.modified) {
                        record.modified = {};
                    }
                    if (imageStore.modified.indexOf(record) == -1) {
                        imageStore.modified.push(record);
                    }
                    record.markDirty();

                    imageStore.insert(0, record);

                    ProductWindow.imageGrid.startEditing(0, 1);
                }
            }
        }
    });

    var fileUploadField = new Ext.form.FileUploadField({
        buttonCfg: {
            icon: Axis.skinUrl + '/images/icons/add.png',
            text: 'Upload'.l()
        },
        buttonOnly: true,
        listeners: {
            fileselected: function() {
                this.fileInput.id = 'image';
                var rec = new fileUploader.store.recordType({
                    input: this.fileInput,
                    fileName: this.fileInput.getValue().split(/[\/\\]/).pop(),
                    state: 'queued'
                }, this.fileInput.id);
                rec.commit();
                fileUploader.store.add(rec);

                fileUploader.upload();

                this.createFileInput();
                this.bindListeners();
            }
        }
    });

    var imageBrowser = new Axis.ImageBrowser({
        rootPath: 'media/product',
        rootText: 'product'
    });

    ImageGrid.el = ProductWindow.imageGrid = new Axis.grid.EditorGridPanel({
        //autoExpandColumn: 'title',
        border: false,
        cm: new Ext.grid.ColumnModel({
            defaults: {
                sortable: true,
                menuDisabled: true
            },
            columns: imageCols
        }),
        disableSelection: true,
        ds: imageStore,
        massAction: false,
        title: 'Images'.l(),
        plugins: [
            imageThumbnail,
            imageListing,
            imageBase,
            imageDelete
        ],
        tbar: [fileUploadField, {
            icon: Axis.skinUrl + '/images/icons/folder_image.png',
            handler: function() {
                imageBrowser.show();
            },
            text: 'Choose from uploaded'.l()
        }]
    });

    imageBrowser.on('okpress', function(records) {
        Ext.each(records, function(r) {
            var record = new imageStore.recordType({
                path: r.get('absolute_url').replace(Axis.secureUrl + '/media/product', ''),
                sort_order: 50,
                is_thumbnail: 0,
                is_listing: 0,
                is_base: 0,
                remove: 0
            });
            record.dirty = true;
            if (!record.modified) {
                record.modified = {};
            }
            if (imageStore.modified.indexOf(record) == -1) {
                imageStore.modified.push(record);
            }
            record.markDirty();
            imageStore.insert(0, record);
            ProductWindow.imageGrid.startEditing(0, 1);
        });
    });

    ProductWindow.addTab(ImageGrid.el, 50);
    ProductWindow.dataObjects.push(ImageGrid);

});
