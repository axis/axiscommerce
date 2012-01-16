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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

Ext.onReady(function(){

     var toolbar = new Ext.Toolbar({
        items:[
            new Ext.form.TextField({
                width: 130,
                emptyText:'Find a category'.l(),
                listeners:{
                    render: function(f){
                        f.el.on('keydown', filterTree, f, {buffer: 350});
                    }
                }
            }),
            new Ext.Button({
                cls: 'x-btn-icon',
                icon: Axis.skinUrl + '/images/icons/tree_expand.png',
                tooltip: 'Expand'.l(),
                handler: function() {
                    CategoryTree.el.root.expand(true);
                }
            }), '-',
            new Ext.Button({
                cls: 'x-btn-icon',
                icon: Axis.skinUrl + '/images/icons/tree_collapse.png',
                tooltip: 'Collapse'.l(),
                handler: function() {
                    CategoryTree.el.root.collapse(true);
                }
            })
        ]
    });

     var searchPanel = new Ext.Panel({
         bodyStyle: 'border: 1px solid #D0D0D0; border-width: 1px 1px 0;',
         region: 'north',
         id: 'panel-search',
         height: 26,
         items: toolbar
     });
})

var hiddenPkgs  = [];
var markCount   = [];

function filterTree(e){
    var text = e.target.value;
    Ext.each(hiddenPkgs, function(n){
        n.ui.show();
    });

    markCount  = [];
    hiddenPkgs = [];

    if( text.trim().length > 0 ){
        CategoryTree.el.expandAll();

        var re = new RegExp( Ext.escapeRe(text), 'i');
        CategoryTree.el.root.cascade( function( n ){
            if( re.test(n.text) )
                markToRoot( n, CategoryTree.el.root );
        });

        // hide empty packages that weren't filtered
        CategoryTree.el.root.cascade(function(n){
            if( ( !markCount[n.id] || markCount[n.id] == 0 ) && n != CategoryTree.el.root ){
                n.ui.hide();
                hiddenPkgs.push(n);
            }
        });
    }
}

function markToRoot( n, root ){

    if( markCount[n.id] )
        return;

    markCount[n.id] = 1;

    if( n.parentNode != null )
        markToRoot( n.parentNode, root );
}