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
Ext.chart.Chart.CHART_URL = Axis.secureUrl + '/js/ext-3.4.0/resources/charts.swf';

Ext.onReady(function () {

    var store = new Ext.data.JsonStore({
        url: Axis.getUrl('poll/get-result'),
        root: 'data',
        storeId: 'storeResults',
        fields: ['answer', 'count']
    });


    var resultWin = new Ext.Window({
        id: 'window-question-result',
        width: 700,
        constrainHeader: true,
        height: 500,
        closeAction: 'hide',
        items: [{
            xtype: 'stackedbarchart',
            store: store,
            yField: 'answer',
            xAxis: new Ext.chart.NumericAxis({
                stackingEnabled: true,
                majorUnit: 1
            }),
            yAxis: new Ext.chart.CategoryAxis({
                labelRenderer: function(str) {
                    if (str.length < 50) {
                        return str;
                    }
                    return str.substr(0, 50) + '\n...' ;
                }
            }),
            series: [{
                xField: 'count',
                displayName: 'count'
            }]
        }],

        buttons: [{
            text: 'Close'.l(),
            handler: function(){resultWin.hide();}
        }]
    });
});
//eof