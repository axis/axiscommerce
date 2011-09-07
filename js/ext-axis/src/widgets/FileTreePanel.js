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

Axis.FileTreePanel = Ext.extend(Ext.ux.FileTreePanel, {
    
    animate: false,
    
    autoScroll: true,
    
    enableDD: false,
    
    enableOpen: true,
    
    enableProgress: false,
    
    hrefPrefix: Axis.baseUrl + '/',
    
    maxFileSize: null,
    
    rootPath: 'media',
    
    rootText: 'media',
    
    rootVisible: true,
    
    singleUpload: false, // server works properly only with this config
    
    url: Axis.getUrl('catalog/image/cmd'),
    
    confirmOverwrite:function(filename, callback, scope) {
        Ext.Msg.show({
             title:this.confirmText
            ,modal: false
            ,msg:String.format(this.existsText, filename) + '. ' + this.overwriteText
            ,icon:Ext.Msg.QUESTION
            ,buttons:Ext.Msg.YESNO
            ,fn:callback.createDelegate(scope || this)
        });
    },
    
    deleteNode:function(node) {
        // fire beforedelete event
        if(true !== this.eventsSuspended && false === this.fireEvent('beforedelete', this, node)) {
            return;
        }

        Ext.Msg.show({
             title:this.deleteText
            ,modal: false
            ,msg:this.reallyWantText + ' ' + this.deleteText.toLowerCase()  + ' <b>' + node.text + '</b>?'
            ,icon:Ext.Msg.WARNING
            ,buttons:Ext.Msg.YESNO
            ,scope:this
            ,fn:function(response) {
                // do nothing if answer is not yes
                if('yes' !== response) {
                    this.getEl().dom.focus();
                    return;
                }
                // setup request options
                var options = {
                     url:this.deleteUrl || this.url
                    ,method:this.method
                    ,scope:this
                    ,callback:this.cmdCallback
                    ,node:node
                    ,params:{
                         cmd:'delete'
                        ,file:this.getPath(node)
                    }
                };
                Ext.Ajax.request(options);
            }
        });
    },
    
    showError: Ext.emptyFn
    
});

Ext.reg('filetreepanel', Axis.FileTreePanel);
