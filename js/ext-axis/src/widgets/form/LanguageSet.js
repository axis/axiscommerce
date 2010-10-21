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

Axis.form.LanguageSet = Ext.extend(Ext.form.TextField, {

    baseCls: 'x-form-langset',

    flagsUrl: Axis.secureUrl + '/js/ext-axis/resources/images/flags/',

    formId: null,

    items: [],

    defaultType: 'textfield',

    locale: null,

    localeMenuId: 'menu-language-select',

    resizedItems: [],

    tpl: '{self_plain}[{language_id}][{self_nested}]',

    tplCls: '{self} {self}-{language_id}',

    tplId: '{self}-{language_id}',

    tplVars: ['cls', 'dataIndex', 'hiddenName', 'id', 'name'],

    /**
     * Disable dynamic validation of the field
     */
    validationEvent: false,

    // private
    initComponent: function() {
        Axis.form.LanguageSet.superclass.initComponent.call(this);
        this.locale = Axis.locales[Axis.language].locale;
        this.formId = this.findParentByType('form').id;

        this.items = [];
        var menuItems = [];

        for (langId in Axis.locales) {
            // generate language formfields
            var item = {};
            Ext.apply(item, this.initialConfig);
            //item.anchor = undefined;
            item.xtype = this.defaultType;
            item.locale = Axis.locales[langId].locale;

            for (var i = 0, limit = this.tplVars.length; i < limit; i++) {
                var v = this.tplVars[i];
                if (this[v]) {
                    var tpl = this[v];
                    var tplV = 'tpl' + v.charAt(0).toUpperCase() + v.substr(1);
                    if (this[tplV]) {
                        tpl = this[tplV];
                    } else if (this.tpl) {
                        tpl = this.tpl;
                    }
                    var openIndex = this[v].indexOf('[');
                    item[v] = this.getFormattedString(tpl, {
                        self: this[v],
                        self_plain: openIndex === -1 ?
                            this[v] : this[v].substr(0, openIndex),
                        self_nested: openIndex === -1 ?
                            '' : this[v].substr(openIndex + 1, this[v].length - openIndex - 2),
                        language_id: langId
                    });
                }
            }

            this.items.push(item);

            // create language select menu
            menuItems.push({
                icon: this.flagsUrl + this.getCountryCode(Axis.locales[langId].locale) + '.gif',
                id: this.localeMenuId + '-' + this.formId + '-' + Axis.locales[langId].locale,
                locale: Axis.locales[langId].locale,
                handler: function(item, e) {
                    this.setFormLocale(item.locale);
                },
                scope: this,
                text: Axis.locales[langId].language
            });

        }

        if (!Ext.getCmp(this.localeMenuId + '-' + this.formId)) {
            new Ext.menu.Menu({
                id: this.localeMenuId + '-' + this.formId,
                items: menuItems,
                cls: 'x-form-lang-menu'
            });
        }
    },

    // private
    onRender: function(ct, position) {
        if (!this.el) {
            var panelCfg = {
                activeItem: 0,
                autoEl: {
                    id: this.id
                },
                bufferResize: false,// Default this to false, since it doesn't really have a proper ownerCt.
                cls: this.baseCls,
                items: this.items,
                layout: 'card',
                renderTo: ct
            };

            this.panel = new Ext.Container(panelCfg);
            this.panel.ownerCt = this;
            this.el = this.panel.getEl();

            this.el.parent('.x-form-item').addClass('x-form-langset-item');
            if (this.defaultType == 'htmleditor') {
                this.el.parent('.x-form-element').addClass('x-form-htmleditor-element');
            }

            this.flag = this.el.createChild({
                alt: this.locale,
                cls: 'x-form-lang-trigger',
                src: Ext.BLANK_IMAGE_URL,
                style: {
                    'background-image': 'url('
                         + this.flagsUrl
                         + this.getCountryCode()
                         + '.gif)'
                },
                tag: 'img'
            });
            this.flag.on('click', function(e, t) {
                Ext.getCmp(this.localeMenuId + '-' + this.formId).showAt(e.xy);
            }, this);

            // webkit fix
            this.setWidth(this.getWidth() - 20);

            var fields = this.panel.findBy(function(c) {
                return c.isFormField;
            }, this);

            this.items = new Ext.util.MixedCollection();
            this.items.addAll(fields);
        }
        Axis.form.LanguageSet.superclass.onRender.call(this, ct, position);
    },

    afterRender: function() {
        Axis.form.LanguageSet.superclass.afterRender.call(this);
        this.setLocale(this, this.locale);
        this.eachItem(function(item) {
            item.afterMethod('onResize', this.doResize, this);
        });
    },

    initValue: function() {
        if (this.value) {
            this.setValue.apply(this, [this.value]);
            this.eachItem(function(item) {
                item.originalValue = this.value[item.name] ? this.value[item.name] : '';
            });
            delete this.value;
        }
    },

    /**
     * @param {String} tpl
     * @param {Object} bind
     * @return {String} formatted string
     */
    getFormattedString: function(tpl, bind) {
        for (var key in bind) {
            var r = new RegExp('{' + key + '}', 'g');
            tpl = tpl.replace(r, bind[key]);
        }
        return tpl;
    },

    /**
     * @param {String} locale [en_US]
     */
    setFormLocale: function(locale) {
        var sets = Ext.getCmp(this.formId).findByType('langset')
        var menuItemIndex = Ext.getCmp(this.localeMenuId + '-' + this.formId)
            .items
            .findIndex('id', this.localeMenuId + '-' + this.formId + '-' + locale);

        if (menuItemIndex == -1) {
            return false;
        }

        Ext.each(sets, function(item, index, allItems) {
            this.setLocale(item, locale, menuItemIndex);
        }, this);
    },

    // private
    setLocale: function(langset, locale, index) {
        if (undefined === index) {
            var index = Ext.getCmp(langset.localeMenuId + '-' + this.formId)
                .items
                .findIndex('id', langset.localeMenuId + '-' + this.formId + '-' + locale);

            if (index == -1) {
                return false;
            }
        }

        langset.locale = locale;
        if (langset.rendered) {
            // fileupload fix
            if (langset.defaultType == 'fileuploadfield') {
                Ext.each(langset.items.items, function(item, i) {
                    if (i == index) {
                        Ext.fly(item.wrap.dom).setVisibilityMode(Ext.Element.DISPLAY).show();
                    } else {
                        Ext.fly(item.wrap.dom).setVisibilityMode(Ext.Element.DISPLAY).hide();
                    }
                });
            }
            langset.panel.layout.setActiveItem(index);
            langset.flag.dom.setAttribute('alt', this.locale);
            langset.flag.setStyle({
                'background-image': 'url('
                                     + langset.flagsUrl
                                     + langset.getCountryCode()
                                     + '.gif)'
            });
        }

    },

    // private
    getCountryCode: function(locale) {
        if (locale) {
            var cc = locale.toLowerCase();
        } else {
            var cc = this.locale.toLowerCase();
        }
        var cIndex = cc.indexOf('_');
        if (cIndex != -1) {
            cc = cc.substr(cIndex + 1);
        }
        return cc;
    },

    // private
    getErrors: function() {
        var errors = [];

        if (!this.allowBlank) {
            var failedLocales = [];

            this.eachItem(function(f) {
                if (this.rendered) {
                    var value = f.getValue();
                    f.validate();
                } else {
                    var value = this.value[f.name];
                }
                if (value == '') {
                    failedLocales.push(f.locale);
                }
            });

            if (failedLocales.length) {
                if (-1 === failedLocales.indexOf(this.locale)) {
                    this.setFormLocale(failedLocales[0]);
                }
                errors.push(this.blankText);
            }
        }

        return errors;
    },

    // private
    isDirty: function(){
        if (this.disabled || !this.rendered) {
            return false;
        }

        var dirty = false;
        this.eachItem(function(item) {
            if (item.isDirty()) {
                dirty = true;
                return false;
            }
        });
        return dirty;
    },

    // private
    onDisable: function() {
        this.eachItem(function(item) {
            item.disable();
        });
    },

    // private
    onEnable: function() {
        this.eachItem(function(item) {
            item.enable();
        });
    },

    // private
    doLayout: function() {
        //ugly method required to layout hidden items
        if (this.rendered) {
            this.panel.forceLayout = this.ownerCt.forceLayout;
            this.panel.doLayout();
        }
    },

    // private
    doResize: function(w, h) {
        if (typeof w == 'number' && isNaN(this.anchor)) {
            var itemWidth = w - this.flag.getWidth();
            this.eachItem(function(item) {
                item.el.setWidth(itemWidth);
            });
        }
        if (!this.resizedItems[this.el.id]) {
            this.el.setWidth(w);
            this.resizedItems[this.el.id] = true;
        }
    },

    // private
    onResize: function(w, h) {
        this.panel.setSize(w, h);
        this.panel.doLayout();
    },

    // inherit docs from Field
    reset: function() {
        this.eachItem(function(item) {
            if (item.reset) {
                item.reset();
            }
        });
        // Defer the clearInvalid so if BaseForm's collection is being iterated it will be called AFTER it is complete.
        // Important because reset is being called on both the group and the individual items.
        (function() {
            this.clearInvalid();
        }).defer(50, this);
    },

    focus: function() {
        this.panel.layout.activeItem.focus();
    },

    setValue: function() {
        if (this.rendered) {
            this.onSetValue.apply(this, arguments);
        } else {
            if (!this.value) {
                this.value = {};
            }
            if (arguments.length < 2) {
                var args = arguments;
                this.eachItem(function(item) {
                    this.value[item.name] = args[0];
                });
            } else {
                this.value[arguments[0]] = arguments[1];
            }
        }
        return this;
    },

    onSetValue: function(id, value) {
        if (arguments.length == 1) {
            if (Ext.isArray(id)) {
                // an array of boolean values
                Ext.each(id, function(val, idx) {
                    var item = this.items.itemAt(idx);
                    if (item) {
                        item.setValue(val);
                    }
                }, this);
            } else if (Ext.isObject(id)) {
                // set of name/value pairs
                for (var i in id) {
                    var f = this.getField(i);
                    if (f) {
                        f.setValue(id[i]);
                    }
                }
            } else {
                this.setValueForItem(id);
            }
        } else {
            var f = this.getField(id);
            if (f) {
                f.setValue(value);
            }
        }
    },

    // private
    beforeDestroy: function() {
        Ext.destroy(this.panel);
        if (this.flag) {
            this.flag.remove();
        }
        if (this.items) {
            delete this.items;
        }
        Axis.form.LanguageSet.superclass.beforeDestroy.call(this);
    },

    setValueForItem: function(val) {
        this.eachItem(function(item) {
            item.setValue(val);
        });
    },

    // private
    getField: function(id){
        var field = null;
        this.eachItem(function(f) {
            if (id == f || f.dataIndex == id || f.id == id || f.name == id) {
                field = f;
                return false;
            }
        });
        return field;
    },

    /**
     * Gets an array of the values in the set.
     * @return {Object} An object of the values.
     */
    getValue: function() {
        var out = {};
        this.eachItem(function(item) {
            out[item.name] = this.rendered ? item.getValue() :
                this.value[item.name] ? this.value[item.name] : '';
        });
        return out;
    },

    // private
    eachItem: function(fn, scope) {
        if (this.items && this.items.each) {
            this.items.each(fn, scope || this);
        } else if (this.items) {
            // this is for getField method,
            // called in setValue (BasicForm) method
            // in case if the field wasn't rendered yet
            Ext.each(this.items, fn, scope || this);
        }
    },

    /**
     * @method getRawValue
     * @hide
     */
    getRawValue: Ext.emptyFn,

    /**
     * @method setRawValue
     * @hide
     */
    setRawValue: Ext.emptyFn

});

Ext.reg('langset', Axis.form.LanguageSet);