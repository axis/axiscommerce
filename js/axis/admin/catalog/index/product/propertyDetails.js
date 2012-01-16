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

Axis.PropertyDetails = Ext.extend(Ext.util.Observable, {

    constructor: function(config) {
        Ext.apply(this, config);

        this.events = {
            /**
             * @event cancel
             * Fires when cancel button was pressed
             */
            'cancelpress': true,
            /**
             * @event ok
             * Fires when ok button was pressed
             * @param {Ext.data.Record} Record with form data
             */
            'okpress': true
        };
        Axis.PropertyDetails.superclass.constructor.call(this);

        var item;
        if (this.item == 'text') {
            item = {
                anchor: '100% -10',
                xtype: 'textarea'
            }
        } else if (this.item == 'text-l') {
            item = {
                anchor: '100% -10',
                defaultType: 'textarea',
                xtype: 'langset'
            }
        } else if (this.item == 'file') {
            item = {
                anchor: '100%',
                xtype: 'fileuploadfield'
            }
        } else {
            item = {
                anchor: '-20',
                defaultType: 'fileuploadfield',
                xtype: 'langset'
            }
        }
        item.id = this.id + this.item;

        this.formPanel = new Axis.FormPanel({
            border: true,
            bodyStyle: 'padding: 10px 10px 0 10px',
            defaults: {
                hideLabel: true,
                name: 'value',
                tpl: '{self}_{language_id}',
                value: this.value
            },
            items: [item]
        });

        this.window = new Axis.Window({
            border: false,
            closable: false,
            title: 'Property value'.l(),
            width: 350,
            height: 220,
            items: [
                this.formPanel
            ],
            buttons: [{
                icon: Axis.skinUrl + '/images/icons/accept.png',
                text: 'Ok'.l(),
                scope: this,
                handler: this.okPress
            }, {
                icon: Axis.skinUrl + '/images/icons/cancel.png',
                text: 'Cancel'.l(),
                scope: this,
                handler: this.cancelPress
            }],
            listeners: {
                hide: {
                    scope: this,
                    fn: this.hide
                }
            }
        });
    },

    destroy: function() {
        if (this.formPanel) {
            this.formPanel.destroy();
        }
        if (this.window) {
            this.window.destroy();
        }
        this.purgeListeners();
    },

    setTitle: function(title) {
        this.window.setTitle(title);
        return this;
    },

    focus: function() {
        this.formPanel.items.items[0].focus();
    },

    hide: function() {
        this.window.hide();
    },

    show: function(activeItem) {
        this.window.show();
        this.focus();
    },

    okPress: function() {
        var value = this.formPanel.getForm()
            .findField(this.id + this.item)
            .getValue();

        if (false === this.fireEvent('okpress', value)) {
            return;
        }
        this.destroy();
    },

    cancelPress: function() {
        this.fireEvent('cancelpress');
        this.destroy();
    }
});
