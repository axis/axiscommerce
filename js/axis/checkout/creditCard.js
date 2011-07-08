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
function strtolower( str ) {
    return str.toLowerCase();
}

function CreditCard(valueElementId, code) {
     
    this.valueElementId = valueElementId;
    this.ccNumber =  $('#' + valueElementId).val();
    this.code = code;
    $('#' + this.code + '-CcType').hide();
    this.cardType = 'undefined';
    var el = $('#' + this.valueElementId );
    el.blur(this.run);
    el.get(0).cc = this;
}

CreditCard.prototype.validateNumber = function() {
     // Credit Card Validation Javascript
    // copyright 12th May 2003, by Stephen Chapman, Felgall Pty Ltd
                    
    // remove non-numerics
     var s = this.ccNumber;
    var v = "0123456789";
    var w = "";
    for (i = 0; i < s.length; i++) {
        x = s.charAt(i);
        if (v.indexOf(x, 0) != -1)
            w += x;
    }
    // validate number
    j = w.length / 2;
    if (j < 6.5 || j > 8 || j == 7) 
        return false;
    k = Math.floor(j);
    m = Math.ceil(j) - k;
    c = 0;
    for (i = 0; i < k; i++) {
        a = w.charAt(i * 2 + m) * 2;
        c += a > 9 ? Math.floor(a / 10 + a % 10) : a;
    }
    for (i=0; i < k + m; i++) 
        c += w.charAt(i * 2 + 1 - m) * 1;
    return (c%10 == 0); 
}

CreditCard.prototype.getCreditCardType = function() {
     this.cardType = 'undefined';
    // remove spaces and hyphens
    this.ccNumber = this.ccNumber.replace(/[ -]/g, '');
    // define card names and their matching patterns
    var ccArray = {
        "VISA"             : "^4[0-9]{12}(?:[0-9]{3})?$", //(^4\d{12}$)|(^4[0-8]\d{14}$)|(^(49)[^013]\d{13}$)|(^(49030)[0-1]\d{10}$)|(^(49033)[0-4]\d{10}$)|(^(49110)[^12]\d{10}$)|(^(49117)[0-3]\d{10}$)|(^(49118)[^0-2]\d{10}$)|(^(493)[^6]\d{12}$)
        "MASTERCARD"       : "^5[1-5][0-9]{14}$",
        "AMERICAN_EXPRESS" : "^3[47][0-9]{13}$",
        "DISCOVER"         : "^6011[0-9]{12}$",
        "JCB"              : "(^(352)[8-9](\\d{11}$|\\d{12}$))|(^(35)[3-8](\\d{12}$|\\d{13}$))",
        "SOLO"             : "(^(6334)[5-9](\\d{11}$|\\d{13,14}$)) |(^(6767)(\\d{12}$|\\d{14,15}$))",
        "DINERS_CLUB"      : "(^(30)[0-5]\\d{11}$)|(^(36)\\d{12}$)|(^(38[0-8])\\d{11}$)",
        "MAESTRO"          : "(^(5[0678])\\d{11,18}$) |(^(6[^0357])\\d{11,18}$) |(^(601)[^1]\\d{9,16}$) |(^(6011)\\d{9,11}$) |(^(6011)\\d{13,16}$) |(^(65)\\d{11,13}$) |(^(65)\\d{15,18}$) |(^(633)[^34](\\d{9,16}$)) |(^(6333)[0-4](\\d{8,10}$)) |(^(6333)[0-4](\\d{12}$)) |(^(6333)[0-4](\\d{15}$)) |(^(6333)[5-9](\\d{8,10}$)) |(^(6333)[5-9](\\d{12}$)) |(^(6333)[5-9](\\d{15}$)) |(^(6334)[0-4](\\d{8,10}$)) |(^(6334)[0-4](\\d{12}$)) |(^(6334)[0-4](\\d{15}$)) |(^(67)[^(59)](\\d{9,16}$)) |(^(6759)](\\d{9,11}$)) |(^(6759)](\\d{13}$)) |(^(6759)](\\d{16}$)) |(^(67)[^(67)](\\d{9,16}$)) |(^(6767)](\\d{9,11}$)) |(^(6767)](\\d{13}$)) |(^(6767)](\\d{16}$)) ",
        
        "Blance"           : "^(389)[0-9]{11}$",
        "EnRoute"          : "(^(2014)|^(2149))\\d{11}$",
        "Switch"           : "(^(49030)[2-9](\\d{10}$|\\d{12,13}$)) |(^(49033)[5-9](\\d{10}$|\\d{12,13}$)) |(^(49110)[1-2](\\d{10}$|\\d{12,13}$)) |(^(49117)[4-9](\\d{10}$|\\d{12,13}$)) |(^(49118)[0-2](\\d{10}$|\\d{12,13}$)) |(^(4936)(\\d{12}$|\\d{14,15}$)) |(^(564182)(\\d{11}$|\\d{13,14}$)) |(^(6333)[0-4](\\d{11}$|\\d{13,14}$)) |(^(6759)(\\d{12}$|\\d{14,15}$))"
        
        
    };
    
    // identify the card type
    for (key in ccArray) {
    var regex = new RegExp(ccArray[key]);
        if (regex.test(this.ccNumber)) {
            this.cardType = key;
            break;
        }
    }
    return this.cardType;
}
CreditCard.prototype.run = function() {
     this.cc.ccNumber =  $('#' + this.cc.valueElementId).val();
     $('#extra-fields-' + this.cc.code + ' p.error').remove();
     $('#extra-fields-' + this.cc.code + ' .error').removeClass('error');
     if (this.cc.ccNumber == '') {
        $('#extra-fields-' + this.cc.code + ' .card-icon').removeClass('disabled');
        return;
    }
    if (!(this.cc.validateNumber())) {
        $('#extra-fields-' + this.cc.code + ' .card-icon').removeClass('disabled');
        $('#' + this.cc.valueElementId ).addClass('error');
        $('#extra-fields-' + this.cc.code + ' #' + this.cc.code + '-CcNumber')
            .parent()
            .append('<p class="error">Credit Card Number is not valid</p>');
        return;
    }
    if (this.cc.getCreditCardType() != 'undefined'){
        $('#extra-fields-' + this.cc.code + ' .card-icon')
            .addClass('disabled');
        $('#extra-fields-' + this.cc.code + ' #cc-' + strtolower(this.cc.cardType))
            .removeClass('disabled');
        if ($('#extra-fields-' + this.cc.code + ' #cc-' + strtolower(this.cc.cardType)).length == 0 )
            $('#extra-fields-' + this.cc.code + ' #' + this.cc.code + '-CcNumber')
                .parent()
                .append('<p class="error">Credit Card Type is not supported</p>');

        $('#extra-fields-' + this.cc.code + ' #' + this.cc.code + '-CcType' )
            .val(this.cc.cardType);
    } else {
        $('#extra-fields-' + this.cc.code + ' .card-icon')
            .removeClass('disabled');
        $('#' + this.cc.valueElementId)
            .addClass('error');
        $('#extra-fields-' + this.cc.code + ' #' + this.cc.code + '-CcNumber')
            .parent()
            .append('<p class="error">Credit Card Type undefined</p>');
        return;
    }
}