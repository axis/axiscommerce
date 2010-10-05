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

Ext.onReady(function() {
    
    var tabsTopBar = new Ext.Toolbar();
    tabsTopBar.addSpacer();
    tabsTopBar.addText('Name'.l());
    tabsTopBar.addElement('groupName');
    tabsTopBar.addSeparator();
    tabsTopBar.addText('Sort Order'.l());
    tabsTopBar.addElement('sortOrder');
    tabsTopBar.addSeparator();
    tabsTopBar.addElement('groupAct');
    tabsTopBar.addSeparator();
    tabsTopBar.addButton({
        text: 'Save'.l(),
        icon: Axis.skinUrl + '/images/icons/accept.png',
        cls: 'x-btn-text-icon',
        handler: function(){
            saveGroup();
        }
    });
    
    var groupTabs = new Ext.TabPanel({
        renderTo: 'language-tabs',
        activeTab: 0,
        plain:true,
        items: groupTab,
        tbar: tabsTopBar,
        listeners: {
            'beforerender': function(cmp) {
                cmp.width = $('#ext-cmp-layout').width() - $('#ext-cmp-layout .sidebox').width() - 20;
            }
        }
    });
})