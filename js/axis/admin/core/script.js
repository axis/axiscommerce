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

if (typeof Ext != 'object') {
    alert(
        'ExtJs library not found at AXIS_ROOT/js folder'
        + "\n"
        + 'Download and unpack it under the js folder (AXIS_ROOT/js/ext-3.4.0)'
   );
}

if (typeof Range != "undefined" && typeof Range.prototype.createContextualFragment == "undefined") { // FIX IE9
    Range.prototype.createContextualFragment = function(html) {
        var doc = this.startContainer.ownerDocument;
        var container = doc.createElement("div");
        container.innerHTML = html;
        var frag = doc.createDocumentFragment(), n;
        while ( (n = container.firstChild) ) {
            frag.appendChild(n);
        }
        return frag;
    };
}

Axis.getUrl = function(url, disableSsl, front) {
    var prefix = Axis.secureUrl;
    if (disableSsl) {
        prefix = Axis.baseUrl;
    }
    if (!front) {
        prefix += Axis.adminUrl;
    }
    if (url) {
        url = url.replace(/^\/+/g, '');
    } else {
        url = '';
    }
    return (prefix + '/' + url).replace(/\/+$/g, '');
};

Axis.escape = function (string) {
    return Ext.util.Format.htmlEncode(string);
};

Ext.ux.Table = {
     colorize: function() {
          $(".axis-table tr").mouseover(function() {
               $(this).addClass("over");
          }).mouseout(function() {
               $(this).removeClass("over");
          });
          $(".axis-table tr:even").addClass("even");
     }
};
Ext.onReady(function(){
     Ext.ux.Table.colorize();
});

/*tabs*/
function pdTabs(tabsetId, useBlocks){

     var axisTabs = document.getElementById(tabsetId).getElementsByTagName('a');

    if (axisTabs.length) {
          $(axisTabs[0]).addClass('active');
     }
     if (useBlocks) {
        for (var i = 0; i < axisTabs.length; i++) {
               axisTabs[i].onclick = new Function("switchBlock(" + i + "); return false;");
               document.getElementById(axisTabs[i].id + '-block').style.display = "none";
          }
          document.getElementById(axisTabs[0].id + '-block').style.display = "block";
          $('#' + axisTabs[0].id + '-block').removeClass('x-hidden');
     }
}

function switchBlock(i){
    var elem = document.getElementById('axis-tabs').getElementsByTagName('a');

    for (var j = 0; j < elem.length; j++) {
        elem[j].className = "";
        document.getElementById(elem[j].id + '-block').style.display = "none";
    }
    document.getElementById(elem[i].id + '-block').style.display = "block";
    $('#' + elem[i].id + '-block').removeClass('x-hidden');
    elem[i].className = "active";
}
/*end of tabs*/