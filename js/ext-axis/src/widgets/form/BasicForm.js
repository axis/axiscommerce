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

Ext.form.BasicForm.override({

    trackResetOnLoad: true,

    invalidTabs: [],

    isValid : function() {
        var valid = true;
        var invalidPanels = [];
        var processedPanels = [];
        this.invalidTabs = [];
        this.items.each(function(f) {
            if (!f.validate()) {
                valid = false;
                // modification begin
                var panel;
                var tabPanel = f.findParentBy(function(p, c) {
                    if (p.isXType('tabpanel')) {
                        return true;
                    }
                    panel = p;
                });
                if (tabPanel && !processedPanels[panel.id]) {
                    var tabEl = Ext.get(tabPanel.getTabEl(panel));
                    if (tabEl) {
                        tabEl.addClass('error');
                        this.invalidTabs.push(tabEl);
                    }
                    processedPanels[panel.id] = true;
                    invalidPanels.push(panel);
                }
                // modification end
            }
        }, this);

        // modification begin
        // @todo change logic: check current tab, if clear - next step by step (no need to check all of them)
        // collect panels to activate and mark tabPanels to skip
        for (var i = 0, len = invalidPanels.length; i < len; i++) {
            var tabPanel = invalidPanels[i].findParentByType('tabpanel');
            if (tabPanel.activeTab == invalidPanels[i]) {
                tabPanel.skipActivation = true;
            } else if (!tabPanel.panelToActivate) {
                tabPanel.panelToActivate = invalidPanels[i];
            }
        }

        // activate panels with errors
        for (var i = 0, len = invalidPanels.length; i < len; i++) {
            var tabPanel = invalidPanels[i].findParentByType('tabpanel');
            if (tabPanel.skipActivation) {
                continue;
            }
            tabPanel.setActiveTab(tabPanel.items.indexOf(tabPanel.panelToActivate));
        }

        // remove custom variables
        for (var i = 0, len = invalidPanels.length; i < len; i++) {
            var tabPanel = invalidPanels[i].findParentByType('tabpanel');
            delete tabPanel.skipActivation;
            delete tabPanel.panelToActivate;
        }
        // modificaton end

        return valid;
    },

    findField : function(id) {
        var field = this.items.get(id);

        if (!Ext.isObject(field)) {
            //searches for the field corresponding to the given id. Used recursively for composite fields
            var findMatchingField = function(f) {
                if (f.isFormField) {
                    if (f.dataIndex == id || f.id == id || f.getName() == id) {
                        field = f;
                        return false;
                    } else if (f.isComposite && f.rendered) {
                        return f.items.each(findMatchingField);
                    } else if (f.isComposite) {
                        Ext.each(f.items, function(el) {
                            if (el.dataIndex == id || el.id == id || el.name == id) {
                                field = el;
                                return false;
                            }
                        });
                        if (undefined !== field) {
                            return false;
                        }
                    } else if (f.xtype == 'langset' && f.getField(id)) {
                        field = f;
                        return false;
                    }
                }
            };

            this.items.each(findMatchingField);
        }
        return field || null;
    },


    reset: function() {
        this.items.each(function(f){
            f.reset();
        });
        this.resetValidationMessages();
        return this;
    },

    clear: function() {
        clearField = function(f) {
            if ('compositefield' === f.xtype && f.items.each) {
                f.items.each(clearField);
            }
            var value = (undefined !== f.initialValue ? f.initialValue : '');
            if (f.setValue) {
                f.setValue(value);
            } else {
                f.value = value;
            }
            if (f.clearInvalid) {
                f.clearInvalid();
            }
        };

        this.items.each(clearField);

        this.resetValidationMessages();
        return this;
    },

    resetValidationMessages: function() {
        this.isValid();

        clearInvalid = function(f) {
            if ('compositefield' === f.xtype && f.items.each) {
                f.items.each(clearField);
            }
            if (f.clearInvalid) {
                f.clearInvalid();
            }
        };

        this.items.each(clearInvalid);

        for (var i = 0, limit = this.invalidTabs.length; i < limit; i++) {
            this.invalidTabs[i].removeClass('error');
        }
    },

    setValues: function(values) {
        this.resetValidationMessages();
        if(Ext.isArray(values)){ // array of objects
            for(var i = 0, len = values.length; i < len; i++){
                var v = values[i];
                var f = this.findField(v.id);
                if(f){
                    f.setValue(v.value);
                    if(this.trackResetOnLoad){
                        f.originalValue = f.getValue();
                    }
                }
            }
        }else{ // object hash
            var field, id;
            for(id in values){
                if(!Ext.isFunction(values[id]) && (field = this.findField(id))){
//                    console.log(id);
                    // modification start
                    if (field.xtype == 'langset' && (fi = field.getField(id))) {
                        field.setValue(id, values[id]);
                    }
                    else {
                        if (field.setValue) {
                            field.setValue(values[id]);
                        } else {
                            field.value = values[id];
                        }
                    }
                    // modification end
                    if(this.trackResetOnLoad){
                        field.originalValue = field.getValue ? field.getValue() : '';
                    }
                }
            }
        }
        return this;
    }
});