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

Ext.onReady(function() {

    var sitesPanel = new Ext.FormPanel({
        standardSubmit: true,
        url: Axis.getUrl('index'),
        title: 'Sites'.l(),
        header: false,
        bodyStyle: {
            padding: '20px 10px 13px'
        },
        items:[new Ext.form.ComboBox({
            hideLabel: true,
            editable: false,
            transform: 'site_id',
            triggerAction: 'all',
            anchor: '100%',
            listeners: {
                select: function(combo, record, index) {
                    sitesPanel.getForm().submit();
                }
            }
        })]
    });

    var quickSummaryPanel = new Ext.Panel({
        title: 'Quick Summary'.l(),
        border: true,
        bodyStyle: {
            padding: '10px'
        },
        items:[
            {contentEl: 'quick-summary-content', border: false}
        ]
    });

    var spacerPanel = new Ext.Panel({
        border: false,
        bodyStyle: {
            height: '7px',
        }
    });

    var westPanel = new Ext.Panel({
        border: false,
        collapsible: true,
        collapseMode: 'mini',
        split: true,
        header: false,
        id: 'panel-west',
        region: 'west',
        layout: 'vbox',
        layoutConfig: {
            align : 'stretch'
        },
        width: 500,
        items: [
            sitesPanel,
            spacerPanel,
            quickSummaryPanel,
            spacerPanel.cloneConfig(),
            ActivityPanel.el,
            spacerPanel.cloneConfig(),
            StatisticsPanel.el
        ]
    });

    new Axis.Panel({
        items: [
            westPanel,
            Ext.getCmp('panel-chart')
        ]
    });
});