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

var Chart = {
    _url: Axis.getUrl('index/dash-board-chart'),
    siteId: false,
    type: 'order',
    period: 'day',
    periodIndex: 1,
    init: function(siteId) {
        this.siteId = siteId;
    },
    getUrl: function () {
        return this._url;
    },
    reload: function() {
        var params = {
            'period': Chart.period,
            'type': Chart.type,
            'periodIndex': Chart.periodIndex
        };
        if (Chart.siteId) {
            params.siteId = Chart.siteId;
        }
        Ext.Ajax.request({
            url: Chart.getUrl(),
            params: params,
            success: function(response) {
                response = Ext.decode(response.responseText);
                var yAxis = Ext.getCmp('chart').yAxis;

                var data = response.data;
                var max = data[0].value || 1;
                var length = data.length;
                for(var i = length; i--;) {
                    if (max < data[i].value) {
                        max = data[i].value;
                    }
                }

                yAxis.maximum = null;
                var text = '';
                switch (Chart.type) {
                    case 'amount':
                        text = 'Amounts ({d})';
                        break;
                    case 'visit':
                        text = 'Visitors ({d})';
                        break;
                    case 'customer':
                        text = 'Customers ({d})';
                        break;
                    case 'rate':
                        yAxis.maximum = 100;
                        max = 100;
                        text = 'Convertion Rate ({d})';
                        break;
                    case 'order':
                    default:
                        text = 'Orders ({d})';
                        break;
                }
                var timestamp = Date.parseDate(data[0].time, "Y-m-d g:i:s");
                switch (Chart.period) {
                    case 'day':
                        timestamp = Ext.util.Format.date(timestamp, "d F Y");
                        break;
                    case 'week':
                        timestamp = Ext.util.Format.date(timestamp, "W")
                            + ' ' + 'Week'.l()
                            + ' ' + Ext.util.Format.date(timestamp, "Y")
                        ;
                        break;
                    case 'month':
                        timestamp = Ext.util.Format.date(timestamp, "F Y");
                        break;
                    case 'year':
                        timestamp = Ext.util.Format.date(timestamp, "Y");
                        break;
                }
                Ext.getCmp('panel-chart').setTitle(text.l('core', timestamp));

                yAxis.majorUnit = Math.ceil(max/10);
                try {
                    Ext.getCmp('chart').setYAxis(yAxis);
                } catch(err) {
                    //console.log(err.description);//who cares
                }
                Ext.StoreMgr.lookup('storeChart').loadData(response);
            }
        });
    },
    setType: function(type){
        this.type = type;
        this.periodIndex = 1;
        Ext.getCmp('button-time-period-next').disable();
        return this;
    },
    setPeriod: function(period){
        this.period = period;
        this.periodIndex = 1;
        Ext.getCmp('button-time-period-next').disable();
        return this;
    },
    setPeriodIndex: function(value){
        value = value || 1;
        if (this.periodIndex + value > 0) {
            this.periodIndex += value;
        }
        if (this.periodIndex + value > 0) {
            Ext.getCmp('button-time-period-next').enable();
        } else {
            Ext.getCmp('button-time-period-next').disable();
        }
        return  this;
    },
    getTime: function(date, format) {
        if (format) {
            return date.format(format);
        }
        switch (Chart.period) {
            case 'day':
                format = "H";
                break;
            case 'week':
                format = "l";
                break;
            case 'month':
                format = "j";
                break;
            case 'year':
                format = "F";
                break;
            default:
                format = "d.m.Y H:i:s";
                break;
        }
        return date.format(format);
    }
}

Ext.chart.Chart.CHART_URL = Axis.secureUrl + '/js/ext-3.3.1/resources/charts.swf';

Ext.onReady(function(){

    var store = new Ext.data.JsonStore({
        root: 'data',
        storeId: 'storeChart',
        autoLoad: false,
        fields: [
            {name: 'time', type: 'date', dateFormat: 'Y-m-d H:i:s'},
            {name: 'value'}
        ]
    });

    var comboboxTimePeriod = new Ext.form.ComboBox({
        width: 100,
        store: new Ext.data.ArrayStore({
            fields: ['id', 'value'],
            data : [['day', 'Day'.l()], ['week', 'Week'.l()],
                ['month', 'Month'.l()], ['year', 'Year'.l()]
            ]
        }),
        displayField: 'value',
        valueField: 'id',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus: true,
        value: 'day',
        editable: false,
        listeners:{
            'beforeselect': function (combo, record, index) {
                Chart.setPeriod(record.get('id')).reload();
            }
        }
    });

    var panelChangeType = new Ext.Panel({
        id: 'panel-change-type',
        layout: 'table',
        defaultType: 'button',
        baseCls: 'x-plain',
        cls: 'btn-panel',
        defaults: {
            toggleGroup: 'panel-buttons-change-type',
            enableToggle: true
        },
        items: [{
            text: 'Orders'.l(),
            icon: Axis.skinUrl + '/images/icons/package.png',
            handler: function() {Chart.setType('order').reload();}
        }, {
            icon: Axis.skinUrl + '/images/icons/money.png',
            text: 'Amounts'.l(),
            handler: function() {Chart.setType('amount').reload();}
        }, {
            icon: Axis.skinUrl + '/images/icons/user.png',
            text: 'Visitor'.l(),
            handler: function() {Chart.setType('visit').reload();}
        }, {
            icon: Axis.skinUrl + '/images/icons/user_add.png',
            text: 'Customers'.l(),
            handler: function() {Chart.setType('customer').reload();}
        }, {
            icon: Axis.skinUrl + '/images/icons/chart_curve.png',
            text: 'Convertion Rate'.l(),
            handler: function() {Chart.setType('rate').reload();}
        }]
    });
    var yAxis = new Ext.chart.NumericAxis({
        majorUnit: 1
    });
    var xAxis = new Ext.chart.TimeAxis({
        stackingEnabled: true,
        majorUnit: 1,
        labelRenderer: Chart.getTime
    });

    new Ext.Panel({
        id: 'panel-chart',
        region: 'center',
        header: true,
        title : '-',
        split: true,
        layout: 'fit',
        border: true,
        tbar:[
            panelChangeType,
            '->',
            comboboxTimePeriod
        ],
        bbar: [{
            text: 'Previous'.l(),
            icon: Axis.skinUrl + '/images/icons/arrow_left.png',
            handler: function() {
                Chart.setPeriodIndex(1).reload();
            }
        }, {
            text: 'Next'.l(),
            id: 'button-time-period-next',
            iconAlign: 'right',
            disabled: true,
            icon: Axis.skinUrl + '/images/icons/arrow_right.png',
            handler: function() {
                Chart.setPeriodIndex(-1).reload();
            }
        }],
        items: {
            //http://developer.yahoo.com/yui/charts/
            xtype: 'columnchart',
            store: store,
            id: 'chart',
            url: Axis.secureUrl + '/js/ext-3.3.1/resources/charts.swf',
            xField: 'time',
            yAxis: yAxis,
            xAxis: xAxis,
            tipRenderer : function(chart, record, index, series) {
                var text = ' ';
                switch (Chart.type) {
                    case 'amount':
                        text = '{v} amount {t}';
                        break;
                    case 'visit':
                        text = '{v} visitor {t}';
                        break;
                    case 'customer':
                        text = '{v} customer {t}';
                        break;
                    case 'rate':
                        text = '{v} rate {t}';
                        break;
                    case 'order':
                    default:
                        text = '{v} order {t}';
                        break;
                }

                switch (Chart.period) {
                    case 'week':
                        format = "d F Y";
                        break;
                    case 'month':
                        format = "d F Y";
                        break;
                    case 'year':
                        format = "F Y";
                        break;
                    case 'day':
                    default:
                        format = "H a d F Y";
                        break;
                }
                return text.l(
                    'core',
                    Chart.getTime(record.data.time, format),
                    Ext.util.Format.number(record.data.value, '0,0')
                );
            },
            chartStyle: {
                padding: 10,
                font: {
                    name: 'Tahoma',
                    color: 0x444444,
                    size: 11
                },
                dataTip: {
                    padding: 5,
                    border: {
                        color: 0x99bbe8,
                        size: 1
                    },
                    background: {
                        color: 0xDAE7F6,
                        alpha: .9
                    },
                    font: {
                        name: 'Tahoma',
                        color: 0x15428B,
                        size: 10,
                        bold: true
                    }
                },
                xAxis: {
                    color: 0x69aBc8,
                    labelRotation: -90,//-67,
                    majorTicks: {
                        color: 0x69aBc8,
                        length: 4
                    },
                    minorTicks: {
                        color: 0x69aBc8,
                        length: 2
                    },
                    majorGridLines: {
                        size: 1,
                        color: 0xeeeeee
                    }
                },
                yAxis: {
                    color: 0x69aBc8,
                    majorTicks: {
                        color: 0x69aBc8,
                        length: 4
                    },
                    minorTicks: {
                        color: 0x69aBc8,
                        length: 2
                    },
                    majorGridLines: {
                        size: 1,
                        color: 0xdfe8f6
                    }
                }
            },
            series: [{
                type: 'column',
                displayName: 'Page Views',
                yField: 'value',
                style: {
                    fillColor: 0x99BBE8,
                    mode: 'stretch',
                    color: 0x99BBE8
                }
            }]
        }
    });

    Ext.getCmp('panel-change-type').items.first().toggle(true);

    Chart.reload();
});