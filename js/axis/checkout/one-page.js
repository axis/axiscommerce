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

var Checkout = {};

Checkout.submitOrder = function() {
    $.ajax({
        url: Axis.getUrl('checkout/onepage/process', true)
    });
}
Checkout.Step = {
    accordion: {},
    panels: {},
    current: 'login',
    bookmark: '',
    stack: [
        'login', 'billing-address', 'delivery-address',
        'shipping', 'payment', 'confirmation'
    ],

    getStepNum: function(step) {
        for (i = 0; i < this.stack.length; i++) {
            if (this.stack[i] == step)
                return i;
        }
        return -1;
    },
    possibleBack: function (step) {
        return this.getStepNum(step) < this.getStepNum(this.current);
    },
    goBack: function (step) {
        if (this.possibleBack(step)) {
            this.switchStep(step);
        }
    },
    switchStep: function (step) {
        this.current = step;
        /* clear old messages */
        MessageStack.clear().setTarget('#step-' + step);
        /* draw goBack icons */
        $('.icon-back').remove();
        $('.step').each(function(){
            var step = this.id.replace(/step-/, '');
//            if (step == 'login') {
//                return;
//            }
            if (!Checkout.Step.possibleBack(step)) {
                return;
            }
            var icon = '<a href="#" '
                     + 'onclick="Checkout.Step.goBack(\'' + step + '\'); return false;" '
                     + 'class="f-right icon-back" title="Edit">'
                     +  'Edit</a>';
            $('#title-' + step).prepend(icon);
        });

        $('.step .loading-bar').hide();
        $('button', '#checkout-accordion').removeAttr('disabled');

        /* set current step & next step classes */
        var currentStep = $('#checkout-accordion .checkout-step-title')
            .index($('#title-' + step).get(0));
        $('#checkout-accordion .checkout-step-title:gt(' + (currentStep > 0 ? currentStep - 1 : 0) +')')
            .addClass('next').removeClass('previous');
        $('#checkout-accordion .checkout-step-title:lt(' + (currentStep) +')')
            .addClass('previous').removeClass('next');

        $('div.step:visible').toggle();
        $('#step-' + step).toggle();
    },
    goTo: function(step) {
        $('.loading-bar', '#step-' + Checkout.Step.current).show();
        this.current = step;
        switch (step) {
            case 'billing-address':
                Address.load('billing-address');
                break;
            case 'delivery-address':
                Address.load('delivery-address');
                break;
            case 'shipping':
                Checkout.Shipping.loadMethods();
                break;
            case 'payment':
                Checkout.Billing.loadMethods();
                break;
            case 'confirmation':
                Checkout.Step.confirmation();
                break;
        }
    },
    next: function(current) {
        var num = this.getStepNum(current);
        num = (num == -1) ? 0 : num + 1;
        this.goTo(this.stack[num]);
    },
    start: function() {
        if (Customer.logged) {
            Checkout.Step.next('login');
        } else if (Customer.loggedAsGuest)  {
            Checkout.Step.next('login');
            Address.create();
        } else {
            this.switchStep('login');
        }
    },
    confirmation: function() {
        var extraData = {};
        for (var i in Checkout.Billing.extraFields) {
            var field = Checkout.Billing.extraFields[i];
            extraData[field['id']] = field['value'];
        }
        $.ajax({
            'url': Axis.getUrl('checkout/onepage/confirmation', true),
            'type': 'post',
            //'data': extraData,
            'success': function(response, textStatus) {
                $('#block-confirmation').empty().append(response);
                Checkout.Step.switchStep('confirmation');
            }
        });
    }
};

var Customer = {
    logged : false,
    loggedAsGuest: false,
    hideFormRegistry : function() {
        $('#form-signup').css('display', 'none');
    },
    showFormRegistry : function() {
        $('#form-signup').css('display', 'block');
    },
    isLogged : function () {
        return this.logged;
    },
    login : function() {
        $.ajax({
            url: Axis.getUrl('account/auth/login', true),
            type: 'post',
            data: $('.form-login').serialize(),
            dataType: 'json',
            success: function(response, textStatus) {
                if (response.logged) {
                    Customer.logged = true;
                    Checkout.Step.next('login');
                } else {
                    Customer.logged = false;
                    alert('Wrong login');
                }
            }
        });
    },
    register : function() {
        $.ajax({
            url: Axis.getUrl('account/auth/register', true),
            data: $('#form-signup').serialize()
        });
    },
    asGuest: function() {
        $('#form-guest .btn-continue').attr('disabled', 'disabled');
        $.ajax({
            url: Axis.getUrl('checkout/onepage/as-guest', true),
            type: 'post',
            data: $('#form-guest').serialize(),
            dataType: 'json',
            success: function(response, textStatus) {
                Customer.loggedAsGuest = true;
                $('#form-new-address').remove();
                Checkout.Step.next('login');
                Address.showForm();
                $('#form-guest .btn-continue').removeAttr('disabled');
            }
        });
    }
};

var Address = {
    currentId: 0,
    formId: '',
    countryZones : function() {
        $('.input-country').live('change', function(){
            var inputZone = $(this)
                .parents('form')
                .find('.input-zone');

            inputZone.removeOption(/./);

            if (Zones[this.value])
                inputZone.addOption(Zones[this.value], false);
        });
    },
    show: function(type, addressListHtml) {
        $('#' + type + '-list').html(addressListHtml);
    },
    create: function() {
        this.currentId = 0;
        $('#' + this.formId).get(0).reset();
        if ($('#step-address-book', '#step-' + Checkout.Step.current).css('display') != 'block') {
            this.showForm();
        } else {
            this.hideForm();
        }
        return false;
    },
    cancel: function() {
        Address.hideForm();
    },
    _populateForm: function() {
        $('#firstname').val('John');
        $('#lastname').val('Doe');
        $('#company').val('StoreArch');
        $('#phone').val('555-555-555-555');
        $('#fax').val('555-555-555-555');
        $('#street_address').val('Yellow stree');
        $('#city').val('New York');
        Address.countryZones();
        $('#country_id').val('223');
        $('#country_id').change();
        setTimeout("$('#zone_id').val('43')", 1000);
        $('#postcode').val('10001');
        $('#email').val('test@axiscommerce.com');
        $('#register_password').val('123654');
        $('#register_password_confirm').val('123654');
        $('#field_nickname').val('test');
    },
    submit: function(e) {
        Address.save(this.formId);
        return false;
    },
    save: function(formId) {
        formId = formId || this.formId;
        if (!$('#' + formId).valid()) {
            return false;
        }
        $('#' + formId + ' submit').attr('disabled', 'disabled');
        var action = $('#' + formId).attr('action');
        var params = $('#' + formId).serialize()
                   + '&id=' + Address.currentId
                   ;
        if (Customer.loggedAsGuest) {
            $('.btn-continue', '#step-' + Checkout.Step.current).attr('disabled', 'disabled');
            params = params + '&use_as_delivery='+ $('#use_as_delivery:checked').length;
        }
        $.ajax({
            url : action,
            type: 'post',
            data : params,
            dataType: 'json',
            success : function(response, textStatus) {
                $('#' + formId + ' submit').removeAttr('disabled');
                $('#' + formId + ' input').removeClass('error');
                $('div.error').remove();
                if (true === response || true === response.success) {
                    if (!Customer.loggedAsGuest) {
                        /* hide form, reload address list, goTo last step */
                        Address.hideForm();
                        Checkout.Step.goTo(Checkout.Step.current);
                        return;
                    }
                    if ('guest-billing-form-new-address' == formId) {
                        Checkout.Step.next('billing-address');
                        if (1 === $('#use_as_delivery:checked').length) {
                            Checkout.Step.next('delivery-address');
                        }
                    }
                    if ('guest-delivery-form-new-address' == formId) {
                        Checkout.Step.next('delivery-address');
                    }

                    return;
                }
                if (Customer.loggedAsGuest) {
                    $('.btn-continue', '#step-' + Checkout.Step.current).removeAttr('disabled');
                    $('.loading-bar', '#step-' + Checkout.Step.current).hide();
                }
                for (var i in response) {
                    var messages = response[i];
                    var message = "";
                    $('#' + formId + ' #' + i).addClass('error');
                    for (var j in messages)
                        message += messages[j] + '<br />';
                    $('#' + formId + ' input[name=' + i + ']').after(
                        '<div class="error">' + message + '</div>'
                    );
                }
            }
        });
    },
    edit: function(addressId) {
        this.currentId = addressId;
        $.ajax({
            url: Axis.getUrl('account/address-book/edit/id/'+ addressId, true),
            type: 'get',
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    return false;
                }
                /* put data to form */
                for (var fieldName in response.data) {
                    var field = $('#' + fieldName);
                    if (!field.length) // field not found
                        continue;

                    var fieldValue = response.data[fieldName];

                    switch (field.get(0).tagName.toLowerCase()) {
                        case 'select':
                            field.selectOptions(fieldValue, true);
                            field.change();
                            break;
                        default:
                            field.attr('value', fieldValue);
                            break;
                    }
                }
                Address.showForm();
                return true;
            }
        });
    },
    showForm: function() {
        if (!$('#' + Checkout.Step.current + ' #step-address-book').length) {
            $('#' + Checkout.Step.current).append($('#step-address-book'));
        }
        $('#step-address-book').show();
    },
    hideForm: function() {
        $('#step-address-book').hide();
    },
    load: function (type) {
        $.ajax({
            type : 'GET',
            url : Axis.getUrl('checkout/onepage/address-list/type/' + type, true),
            success : function(response, textStatus) {
                Address.show(type, response);
                Checkout.Step.switchStep(type);
                if (Customer.loggedAsGuest) {
                    $('.btn-create-address').hide();
                    Address.countryZones();
                }
            }
        });
    },
    onChange: function(el) {
        var selected = $(':selected', el)[0].value;
        if (0 == selected) {
            this.showForm();
        } else {
            this.hideForm();
        }
    }
};

Checkout.Shipping = {
    method : '',
    addressId : 0,
    editing : false,
    /* Delivery Address Methods */
    initAddressRadio : function() {
        if (!this.addressId) {
            return false;
        }
        $('#delivery-radios input[@value=' + this.addressId + ']').attr('checked', true);
        return false;
    },
    getAddressId : function() {
        var items = $('#delivery-address-id option:selected').get();
        if (items.length) {
            return items[0].value
        }
        return -1;
    },
    setAddress : function(element) {
        if (Customer.loggedAsGuest) {
            Address.save('guest-delivery-form-new-address');
            return;
        }
        var addressId = this.addressId = this.getAddressId();
        if (addressId <= 0) {
            alert('Select delivery address');
            return;
        }

        $(element).attr('disabled', 'disabled');

        $.ajax({
            url: Axis.getUrl('checkout/onepage/set-delivery-address', true),
            type: 'post',
            data: {'delivery-address-id' : addressId },
            dataType: 'json',
            success: function(response, textStatus) {
                if (response.success) {
                    Checkout.Step.next('delivery-address');
                } else {
                    $(element).removeAttr('disabled');
                    if (response.messages) {
                        MessageStack.init(response.messages, '#step-delivery').render();
                    }
                }
            }
        });
    },
    /* Shipping Module - Methods */
    loadMethods: function() {
        $.ajax({
            url: Axis.getUrl('checkout/onepage/shipping-method', true),
            success: function(response, textStatus) {
                $('#block-shippingMethod').empty().append(response);
                Checkout.Step.switchStep('shipping');
            }
        });
    },
    getMethod : function() {
        var items = $('#block-shippingMethod input:checked').get();
        if (items.length) {
            return items[0].value
        }
        return 0;
    },
    setMethod : function(e) {
        var method = this.getMethod();
        if (!method) {
            alert('Select Shipping Method');
            return;
        }
        $(e).attr('disabled', 'disabled');
        $.ajax({
            url: Axis.getUrl('checkout/onepage/set-shipping-method', true),
            type: 'post',
            data: { 'method' : method },
            dataType: 'json',
            success: function(response, textStatus) {
                if (response.success) {
                    Checkout.Step.next('shipping');
                } else {
                    $(e).removeAttr('disabled');
                    if (response.messages) {
                        MessageStack.init(response.messages, '#step-shipping').render();
                    }
                }
            }
        });
    }
};

Checkout.Billing = {
    method: '',
    addressId: 0,
    editing: false,
    extraFields: [],
    setAddress: function(element) {
        if (Customer.loggedAsGuest) {
            Address.save('guest-billing-form-new-address');
            return;
        }
        var addressId = this.addressId = this.getAddressId();
        if (addressId <= 0) {
            alert('Select billing address');
            return;
        }

        if (!addressId) {
            return;
        }
        $(element).attr('disabled', 'disabled');
        $.ajax({
            url: Axis.getUrl('checkout/onepage/set-billing-address', true),
            type: 'post',
            data: {
                'billing-address-id': addressId,
                'use_as_delivery': $('#use_as_delivery:checked').length
            },
            dataType: 'json',
            success: function(response, textStatus) {
                if (response.success) {
                    Checkout.Step.next('billing-address');
                    if (1 === $('#use_as_delivery:checked').length) {
                        Checkout.Step.next('delivery-address');
                    }
                } else {
                    $(element).removeAttr('disabled');
                    if (response.messages) {
                        MessageStack.init(response.messages, '#step-billing').render();
                    }
                }
            }
        });
    },
    initAddressRadio: function() {
        if (!this.addressId)
            return false;
        $('#billing-radios input[@value=' + this.addressId + ']').attr('checked', true);
        return false;
    },
    getAddressId: function() {
        var items = $('#billing-address-id option:selected').get();
        if (items.length) {
            return items[0].value;
        }
        return -1;
    },
    /* Payment Module - Methods */
    loadMethods: function() {
        $.ajax({
            url: Axis.getUrl('checkout/onepage/payment-method', true),
            success: function(response, textStatus) {
               $('#block-paymentMethod').empty().append(response);
               Checkout.Billing.initExtra();
               Checkout.Step.switchStep('payment');
            }
        });
    },
    getMethod: function() {
        var items = $('#block-paymentMethod input.payment-method-radio:checked').get();
        if (items.length) {
            return items[0].value;
        }
        return 0;
    },
    getExtra: function() {
        var extra = new Array();
        $('#extra-fields-' + this.getMethod() + ' input').each(function() {
            extra.push({'id': this.id, 'value': this.value});
        });
        $('#extra-fields-' + this.getMethod() + ' select').each(function() {
            extra.push({'id': this.id, 'value': this.value});
        });
        return extra;
    },
    initExtra: function() {
        for (var i in this.extraFields) {
            var field = this.extraFields[i];
            $('#' + field.id).val(field.value);
        }
    },
    setMethod: function(e) {
        var method = this.getMethod();
        if (!method) {
            alert('Select Payment Method');
            return;
        }

        this.extraFields = this.getExtra();

        var data = { 'method': method };
        for (var i in this.extraFields) {
            var field = this.extraFields[i];
            data[field['id']] = field['value'];
        }
        $(e).attr('disabled', 'disabled');
        $.ajax({
            url: Axis.getUrl('checkout/onepage/set-payment-method', true),
            type: 'post',
            data: data,
            dataType: 'json',
            success: function(response, textStatus) {
                if (response.success) {
                    Checkout.Step.next('payment');
                } else {
                    $(e).removeAttr('disabled');
                    if (response.messages) {
                        MessageStack.init(response.messages, '#step-payment').render();
                    }
                }
            }
        });
    }
};