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

var Onestep = (function() {

    var settings = {
        form    : '#form-checkout',
        steps   : [
            '.billing-address',
            '.shipping-methods',
            '.payment-methods',
            '.order-review'
        ]
    };

    function addStepNumbers() {
        var i = 0,
            j = 0,
            selector,
            head;
        while ((selector = settings.steps[i])) {
            i++;
            if ((head = $(selector + ' .head'))) {
                head.prepend('<span class="number">' + (++j) + '</span>');
            }
        }
    };

    function addFormValidator() {
        var form = $(settings.form);
        form.validate({
            rules: {
                'billing_address[password_confirm]': {
                    equalTo: "#billing_address-password"
                }
            },
            submitHandler: function() {
                var postValidates = [Shipping, Payment],
                    isValid = true,
                    i = 0, o;

                while ((o = postValidates[i])) {
                    i++;
                    var res = o.postValidate();
                    if (!res) {
                        isValid = false;
                    }
                }
                if (!isValid) {
                    return;
                }

                update(form.attr('action'));
            }
        });
    };

    function update(url, sections) {
        setLoadWaiting(true, sections);
        var form = $(settings.form);​
        $.post(url, form.serialize())
            .success(setResponse)
            .error(function(response) {
                setLoadWaiting(false);
                if (response.status) {
                    alert('An error has been occured during request processing. Try again');
                }
            });
    };

    function setResponse(data) {
        if (data.redirect) {
            return window.location = data.redirect;
        }

        for (var i in data.sections) {
            var section = $('#' + i + '-load'),
                values  = {};

            section.find('input, select, textarea').each(function(i, el) {
                var el = $(el);
                values[el.attr('id')] = el.val();
            });

            section.html(data.sections[i]);

            // restore previously entered data
            for (var i in values) {
                section.find('#' + i).val(values[i]);
            }
        }

        setLoadWaiting(false);

        if (data.messages && data.messages.error) {
            alert(data.messages.error.join("\n"));
        }
    };

    function setLoadWaiting(status, sections) {
        $('.loading').removeClass('loading');
        $('#submit').removeAttr('disabled');
        $('#osc-ajax-loader').remove();
        $('#osc-mask').remove();

        if (status) {
            $(document.body).append('<div id="osc-mask"></div><div id="osc-ajax-loader"></div>');

            var oscSet          = $('.onestep-set'),
                oscOffset       = oscSet.offset(),
                viewportSize    = BrowserWindow.getViewportSize(),
                scrollOffset    = BrowserWindow.getScrollOffset();

            $('#osc-mask').css({
                width   : oscSet.width(),
                height  : oscSet.height(),
                left    : oscOffset.left,
                top     : oscOffset.top
            });

            $('#osc-ajax-loader').css({
                left: viewportSize.width / 2 + scrollOffset.left,
                top : viewportSize.height / 2 + scrollOffset.top
            });
            $('#submit').attr('disabled', 'disabled');
            sections = sections || {};
            for (var i in sections) {
                if (!sections[i]) {
                    continue;
                }
                $('#' + i + '-load').addClass('loading');
            }
        }
    };

    var Address = {

        zones: {},

        init: function() {
            // add textfield near the zone combobox
            $('.input-zone').each(function() {
                $(this).after(
                    '<input type="text" class="'
                    + $(this).attr('class') + ' input-text" name="'
                    + $(this).attr('name') + '" id="'
                    + $(this).attr('id') + '"'
                    + ' />'
                );
            });
            this.addObservers();
        },

        addObservers: function() {
            var self = this;

            // fill zones combobox with country zones
            // if zones is not available for selected country textfield will be shown instead of combo
            $('select.input-country').change(function() {
                var subform     = this.id.replace('country_id', ''),
                    zoneSelect  = $('select#' + subform + 'zone_id'),
                    zoneInput   = $('input#' + subform + 'zone_id');

                zoneSelect.html('');
                if (self.zones[this.value]) {
                    for (var id in self.zones[this.value]) {
                        var zone = self.zones[this.value][id],
                            option = '<option label="' + zone + '" value="' + id + '">'
                                + zone
                                + '</option>';
                        zoneSelect.append(option);
                    }
                }
                self.toggleZoneField($(this).parents('ol'));
            });
        },

        toggleFormDisplay: function(container, status) {
            if (status) {
                container.show();
                container.find('input, select, textarea').removeAttr('disabled');
                this.toggleZoneField(container);
            } else {
                container.hide();
                container.find('input, select, textarea').attr('disabled', 'disabled');
            }
        },

        /**
         * Toggles the zones input field between combobox and textfield.
         * If combobox has no options available - textfield will be displayed.
         */
        toggleZoneField: function(container) {
            container.find('select.input-zone').each(function() {
                var zoneCombo = $(this),
                    zoneInput = container.find('input.input-zone');

                if (zoneCombo.children().length) {
                    zoneCombo.insertBefore(zoneInput)
                        .removeAttr('disabled')
                        .show();
                    zoneInput.attr('disabled', 'disabled')
                        .hide();
                } else {
                    zoneCombo.insertAfter(zoneInput)
                        .attr('disabled', 'disabled')
                        .hide();
                    zoneInput.removeAttr('disabled')
                        .show();
                }
            });
        }
    };

    var BillingAddress = {

        init: function() {
            this.addObservers();
            this.toggleFormDisplay();
            if (!$('#billing_address-id').length) {
                Address.toggleZoneField($('.billing-address'));
            }
        },

        addObservers: function() {
            var self = this;

            $('#billing_address-id').change(function() {
                self.toggleFormDisplay(
                    !parseInt($(this).val())
                );
                self.update();
            });

            var inputs = [
                'select#billing_address-country_id',
                'select#billing_address-zone_id',
                '#billing_address-postcode'
            ];

            $(inputs.join(',')).change(function() {
                self.update();
            });

            $('#billing_address-use_for_delivery').change(function() {
                DeliveryAddress.toggleDisplay(!$(this).prop('checked'));

                var billingAddressSelect    = $('#billing_address-id'),
                    deliveryAddressSelect   = $('#delivery_address-id');

                // if saved address is selected we can't compare country_id and zone_id
                if (billingAddressSelect.length
                    && (billingAddressSelect.val() || deliveryAddressSelect.val())) {

                    if (billingAddressSelect.val() == deliveryAddressSelect.val()) {
                        return;
                    }

                    DeliveryAddress.update();
                    return;
                }

                if ($('#billing_address-country_id').val() != $('#delivery_address-country_id').val()
                    || $('#billing_address-zone_id').val() != $('#delivery_address-zone_id').val()
                    || $('#billing_address-postcode').val() != $('#delivery_address-postcode').val()) {

                    DeliveryAddress.update();
                }
            });

            $('#billing_address-register').change(function() {
                var fieldset = $('#fieldset-registration_fields'),
                    elements = fieldset.find('input, select, textarea');

                if ($(this).attr('checked')) {
                    fieldset.show();
                    elements.removeAttr('disabled');
                } else {
                    fieldset.hide();
                    elements.attr('disabled', 'disabled');
                }
            });
            $('#billing_address-register').change();
        },

        update: function() {
            var useForDelivery = $('#billing_address-use_for_delivery').prop('checked');

            if (settings.ajax.billing_address) {
                update(settings.urls.billing_address, {
                    'payment-method'    : 1,
                    'shipping-method'   : useForDelivery,
                    'shopping-cart'     : useForDelivery
                });
            } else if (useForDelivery) {
                DeliveryAddress.update();
            }
        },

        toggleFormDisplay: function(status) {
            if (undefined === status) {
                var addressSelect = $('#billing_address-id'),
                    status = true;

                if (addressSelect.length && 0 != addressSelect.val()) {
                    status = false;
                }
            }

            Address.toggleFormDisplay.call(
                Address,
                $('.billing-address .address-form'),
                status
            );

            var registerCheckbox = $('#billing_address-register');
            if (registerCheckbox.length && !registerCheckbox.prop('checked')) {
                $('#fieldset-registration_fields')
                    .find('select, input, textarea').attr('disabled', 'disabled');
            }
        }
    };

    var DeliveryAddress = {

        init: function() {
            this.addObservers();
            this.toggleDisplay();
            if (!$('#delivery_address-id').length) {
                Address.toggleZoneField($('.delivery-address'));
            }
        },

        addObservers: function() {
            var self = this;

            $('#delivery_address-id').change(function() {
                self.toggleFormDisplay(
                    !parseInt($(this).val())
                );
                self.update();
            });

            var inputs = [
                'select#delivery_address-country_id',
                'select#delivery_address-zone_id',
                '#delivery_address-postcode'
            ];

            $(inputs.join(',')).change(function() {
                self.update();
            });
        },

        update: function() {
            if (!settings.ajax.delivery_address) {
                return;
            }
            update(settings.urls.delivery_address, {
                'shipping-method'   : 1,
                'shopping-cart'     : 1
            });
        },

        toggleDisplay: function(status) {
            var container = $('.delivery-address');
            if (status) {
                container.show();
                $('#delivery_address-id').removeAttr('disabled');
                this.toggleFormDisplay();
            } else {
                container.hide();
                $('#delivery_address-id').attr('disabled', 'disabled');
                this.toggleFormDisplay(false);
            }
        },

        toggleFormDisplay: function(status) {
            if (undefined === status) {
                var addressSelect = $('#delivery_address-id'),
                    status = true;

                if (addressSelect.length && 0 != addressSelect.val()) {
                    status = false;
                }
            }

            Address.toggleFormDisplay.call(
                Address,
                $('.delivery-address .address-form'),
                status
            );
        }
    };

    var Payment = {

        init: function() {
            this.addObservers();
        },

        addObservers: function() {
            var self = this;

            $('.cvv-window-close, .cvv-help').live('click', function() {
                self.toggleCvvHelp();
                return false;
            });

            $('.payment-method-radio').live('change', function() {
                self.activate($(this).val());
                self.update();
            });

            $('#payment-method-reset').click(function(e) {
                e.preventDefault();
                $('.payment-method-radio:checked').removeAttr('checked');
                self.activate(null);
                self.update();
            });
        },

        update: function() {
            if (!settings.ajax.payment_method) {
                return;
            }
            update(settings.urls.payment_method, {
                'shipping-method': 1
            });
        },

        activate: function(method) {
            if (undefined === method) {
                method = $('.payment-method-radio[checked="checked"]').val();
            }
            $('.payment-additional').hide();
            $('input, select', '.payment-additional').attr('disabled', 'disabled');
            if (!method) {
                return;
            }
            $('#payment_method-' + method).attr('checked', 'checked');
            $('#extra-fields-' + method).show();
            $('input, select', '#extra-fields-' + method).removeAttr('disabled');
        },

        toggleCvvHelp: function() {
            if ($('.cvv-window').length) {
                $('.cvv-window').remove();
            } else {
                var scrollOffset = BrowserWindow.getScrollOffset(),
                    viewportSize = BrowserWindow.getViewportSize();
                $('body').append('<div class="cvv-window"><a class="cvv-window-close" href="#"></a></div>');
                $('.cvv-window').css({
                    left: viewportSize.width / 2 + scrollOffset.left,
                    top : viewportSize.height / 2 + scrollOffset.top
                });
            }
        },

        postValidate: function() {
            if (!$('.payment-method-radio').length
                || !$('.payment-method-radio[checked="checked"]').val()) {

                var errorLabel = $('#payment-required');
                errorLabel.show();
                $('html, body').scrollTop(
                    errorLabel.offset().top
                );
                return false;
            }
            return true;
        }
    };

    var Shipping = {

        init: function() {
            this.addObservers();
        },

        addObservers: function() {
            var self = this;

            $('.shipping-method-radio').live('change', function() {
                self.activate($(this).val());
                self.update();
            });

            $('#shipping-method-reset').click(function(e) {
                e.preventDefault();
                $('.shipping-method-radio:checked').removeAttr('checked');
                self.activate(null);
                self.update();
            });
        },

        update: function() {
            if (!settings.ajax.shipping_method) {
                return;
            }
            update(settings.urls.shipping_method, {
                'payment-method': 1,
                'shopping-cart' : 1
            });
        },

        activate: function(method) {
            if (undefined === method) {
                method = $('.shipping-method-radio[checked="checked"]').val();
            }
            $('.shipping-type-additional').hide();
            $('input, select', '.shipping-type-additional').attr('disabled', 'disabled');
            if (!method) {
                return;
            }
            $('#shipping_method-' + method).attr('checked', 'checked');
            $('#extra-fields-' + method).show();
            $('input, select', '#extra-fields-' + method).removeAttr('disabled');
        },

        postValidate: function() {
            if (!$('.shipping-method-radio').length
                || !$('.shipping-method-radio[checked="checked"]').val()) {

                var errorLabel = $('#shipping-required');
                errorLabel.show();
                $('html, body').scrollTop(
                    errorLabel.offset().top
                );
                return false;
            }
            return true;
        }
    };

    return {
        init: function(options) {
            $.extend(settings, options || {});

            addFormValidator();

            Address.zones = settings.zones;
            var i = 0, o,
                objects = [
                    Address,
                    BillingAddress,
                    DeliveryAddress,
                    Payment,
                    Shipping
                ];

            while (o = objects[i++]) {
                o.init();
            }

            addStepNumbers();
        },

        getPayment: function() {
            return Payment;
        },

        getShipping: function() {
            return Shipping;
        }
    }
})();
