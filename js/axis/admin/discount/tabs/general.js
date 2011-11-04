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
generalTab = {
    el: {//first tab
        title: 'General'.l(),  
        bodyStyle: 'padding: 10px',
        defaults    : {
            anchor : '-20'
        },
        items: [{
            layout: 'column',
            border: false,
            defaults: {
                layout      : 'form',
                columnWidth : .5,
                border      : false
            },
            items: [{
                items: [{
                    fieldLabel : 'Name'.l(),
                    xtype      : 'textfield',
                    name       : 'discount[name]',
                    allowBlank : false,
                    anchor     : '-10' 
                }]
            }, {
                items: [{
                    fieldLabel : 'Priority'.l(),
                    xtype      : 'textfield',
                    name       : 'discount[priority]',
                    allowBlank : false,
                    anchor     : '100%'
                }]
            }]
        }, {
            layout: 'column',
            border: false,
            defaults: {
                layout      : 'form',
                columnWidth : .5,
                border      : false
            },
            items: [{
                items: [{
                    fieldLabel: 'Type'.l(),
                    name: 'discount[type]',
                    xtype: 'combo',
                    displayField: 'name',
                    valueField: 'id',
                    initialValue: 'percent',
                    mode: 'local',
                    hiddenName: 'discount[type]',
                    triggerAction: 'all',
                    store: new Ext.data.SimpleStore({
                    data: [
                            ["to", 'Fixed Price'.l()],
                            ["by", 'Fixed Amount'.l()],
                            ["percent", 'Percent'.l()]
                        ],
                        fields: ['id', 'name']
                    }),
                    anchor     : '-10'
                }]
            }, {
                items: [{
                    fieldLabel : 'Amount'.l(),
                    xtype      : 'textfield',
                    name       : 'discount[amount]',
                    allowBlank : false,
                    anchor     : '100%'
                }]
            }]
        }, {
            layout: 'column',
            border: false,
            defaults: {
                layout      : 'form',
                columnWidth : .5,
                border      : false
            },
            items: [{
                items: [{
                    allowBlank   : false,
                    columns      : [100, 100],
                    fieldLabel   : 'Status'.l(),
                    name         : 'discount[is_active]',
                    xtype        : 'radiogroup',
                    initialValue : 1,
                    items        : [{
                        boxLabel   : 'Enabled'.l(),
                        checked    : true,
                        name       : 'discount[is_active]',
                        inputValue : 1
                    }, {
                        boxLabel   : 'Disabled'.l(),
                        name       : 'discount[is_active]',
                        inputValue : 0
                    }]
                }]
            }, {
                labelWidth: 110,
                items: [{
    //                    allowBlank  : false,
                    columns      : [100, 100],
                    fieldLabel   : 'Is Combined'.l(),
                    name         : 'discount[is_combined]',
                    xtype        : 'radiogroup',
                    initialValue : 0,
                    items: [{
                        boxLabel   : 'Yes'.l(),
                        checked    : true,
                        name       : 'discount[is_combined]',
                        inputValue : 1
                    }, {
                        boxLabel   : 'No'.l(),
                        name       : 'discount[is_combined]',
                        inputValue : 0
                    }]
                }]
            }]
        }, {
            layout: 'column',
            border: false,
            defaults: {
                layout      : 'form',
                columnWidth : .5,
                border      : false
            },
            items: [{
                items: [{
                    fieldLabel : 'From Date'.l(),
                    name       : 'discount[from_date]',
                    xtype      : 'datefield',
                    anchor     : '-10'
                }]
            }, {
                labelWidth: 110,
                items: [{
                    fieldLabel : 'To Date'.l(),
                    name       : 'discount[to_date]',
                    xtype      : 'datefield',
                    anchor     : '100%'
                }]
            }]
        }, {
            layout: 'column',
            border: false,
            defaults: {
                layout      : 'form',
                columnWidth : .5,
                border      : false
            },
            items: [{
                items: [{
                    fieldLabel  : 'From Price'.l(),
                    xtype       : 'textfield',
                    name        : 'rule[price_greate]',
                    submitValue : false,
                    anchor      : '-10'
                }]
            }, {
                labelWidth: 110,
                items: [{
                    fieldLabel  : 'To Price'.l(),
                    xtype       : 'textfield',
                    name        : 'rule[price_less]',
                    submitValue : false,
                    anchor      : '100%'
                }]
            }]
        }, {
            xtype   : 'hidden',
            name    : 'discount[id]'
        }]
    }
}/*end first tab*/; 