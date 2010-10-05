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
 
/*CUSTOM FUNCTIONS*/
function in_array(search, values)
{
    for (var i in values) {
        if (values[i] == search)
            return true;
    }
    return false;
}

function key_by_value(search, values)
{
    for (var i in values) {
        if (values[i] == search)
            return i;
    }
    return false;
}

function str_replace(search, replace, subject) {
    // http://kevin.vanzonneveld.net
 
    var s = subject;
    var ra = r instanceof Array, sa = s instanceof Array;
    var f = [].concat(search);
    var r = [].concat(replace);
    var i = (s = [].concat(s)).length;
    var j = 0;
    
    while (j = 0, i--) {
        if (s[i]) {
            while (s[i] = (s[i]+'').split(f[j]).join(ra ? r[j] || "" : r[0]), ++j in f){};
        }
    }
 
    return sa ? s : s[0];
}

function Count( mixed_var, mode ) {
    // http://kevin.vanzonneveld.net
    var key, cnt = 0;
    if( mode == 'COUNT_RECURSIVE' ) mode = 1;
    if( mode != 1 ) mode = 0;
    for (key in mixed_var){
        cnt++;
        if( mode==1 && mixed_var[key] && (mixed_var[key].constructor === Array || mixed_var[key].constructor === Object) ){
            cnt += count(mixed_var[key], 1);
        }
    }
    return cnt;
}

function array_keys (input, search_value, argStrict) {
    //http://kevin.vanzonneveld.net)
    var tmp_arr = {}, strict = !!argStrict, include = true, cnt = 0;
    var key = '';

    for (key in input) {
        include = true;
        if (search_value != undefined) {
            if (strict && input[key] !== search_value){
                include = false;
            } else if (input[key] != search_value){
                include = false;
            }
        }

        if (include) {
            tmp_arr[cnt] = key;
            cnt++;
        }
    }

    return tmp_arr;
}

function array_diff_assoc ( array ) {
    // http://kevin.vanzonneveld.net
    var arr_dif = {}, i = 1, argc = arguments.length, argv = arguments, key, key_c, found=false;
    if( !array || (array.constructor !== Array && array.constructor !== Array && typeof array != 'object' && typeof array != 'array') ){
        return null;
    }
    for ( key in array ){
        for (i = 1; i< argc; i++){
            found = false;
            if(argv[i][key] && argv[i][key] == array[key]){
                found = true;
                break;
            }
            if(!found){
                arr_dif[key] = array[key];
            }
        }
    }
    return arr_dif;
}

function array_intersect () {
    // discuss at: http://phpjs.org/functions/array_intersect
    var arr1 = arguments[0], retArr = {};
    var k1 = '', arr = {}, i = 0, k = '';

    arr1keys:
    for (k1 in arr1) {
        arrs:
        for (i=1; i < arguments.length; i++) {
            arr = arguments[i];
            for (k in arr) {
                if (arr[k] === arr1[k1]) {
                    if (i === arguments.length-1) {
                        retArr[k1] = arr1[k1];
                    }
                    // If the innermost loop always leads at least once to an equal value, continue the loop until done
                    continue arrs;
                }
            }
            // If it reaches here, it wasn't found in at least one array, so try next value
            continue arr1keys;
        }
    }

    return retArr;
}

function array_intersect_assoc ( array ) {
    // http://kevin.vanzonneveld.net
    var arr_dif = {}, i = 1, argc = arguments.length, argv = arguments, key, key_c, found=false;
    if( !array || (array.constructor !== Array && array.constructor !== Array && typeof array != 'object' && typeof array != 'array') ){
        return null;
    }
    for ( key in array ){
        for (i = 1; i< argc; i++){
            found = false;
            if(argv[i][key] && argv[i][key] == array[key]){
                found = true;
            }
            if(found){
                arr_dif[key] = array[key];
            }
        }
    }
    return arr_dif;
}

function round ( val, precision ) {
    // http://kevin.vanzonneveld.net
    return parseFloat(parseFloat(val).toFixed(precision));
}

function deepObjCopy (dupeObj) {
    var retObj = new Object();
    if (typeof(dupeObj) == 'object') {
        if (typeof(dupeObj.length) != 'undefined')
            var retObj = new Array();
        for (var objInd in dupeObj) {   
            if (typeof(dupeObj[objInd]) == 'object') {
                retObj[objInd] = deepObjCopy(dupeObj[objInd]);
            } else if (typeof(dupeObj[objInd]) == 'string') {
                retObj[objInd] = dupeObj[objInd];
            } else if (typeof(dupeObj[objInd]) == 'number') {
                retObj[objInd] = dupeObj[objInd];
            } else if (typeof(dupeObj[objInd]) == 'boolean') {
                ((dupeObj[objInd] == true) ? retObj[objInd] = true : retObj[objInd] = false);
            }
        }
    }
    return retObj;
}

function formatCurrency(price, format) {
    precision = isNaN(format.precision = Math.abs(format.precision)) ? 2 : format.precision;
    requiredPrecision = isNaN(format.requiredPrecision = Math.abs(format.requiredPrecision)) ? 2 : format.requiredPrecision;
    precision = requiredPrecision;
    integerRequired = isNaN(format.integerRequired = Math.abs(format.integerRequired)) ? 1 : format.integerRequired;
    decimalSymbol = format.decimalSymbol == undefined ? "," : format.decimalSymbol;
    groupSymbol = format.groupSymbol == undefined ? "." : format.groupSymbol;
    groupLength = format.groupLength == undefined ? 3 : format.groupLength;
    i = parseInt(price = Math.abs(+ price || 0).toFixed(precision)) + "";
    pad = i.length < integerRequired ? integerRequired - i.length : 0;
    while (pad) {
        i = "0" + i;
        pad--;
    }
    j = (j = i.length) > groupLength ? j % groupLength : 0;
    re = new RegExp("(\\d{" + groupLength + "})(?=\\d)", "g");
    r = (j ? i.substr(0, j) + groupSymbol : "") + i.substr(j).replace(re, "$1" + groupSymbol) + (precision ? decimalSymbol + Math.abs(price - i).toFixed(precision).replace(/-/, 0).slice(2) : "");
    return format.pattern.replace("%s", r).replace(/^\s\s*/, "").replace(/\s\s*$/, "");
}

//==============================================================================                
if (!Product) {
    var Product = {};
}

Product.Attributes = {
    basePrice: 0,
    currencyChar: '$',
    currencyRate: 1,
    currentPrice: 0,
    format: {
        'name' : '',
        'shortName' : '',
        'symbol' : '$',
        'decimalSymbol' : ',',
        'display' : '3',
        'groupLength' : 3,
        'groupSymbol' : '.',
        'integerRequired' : 1,
        'position' : 'Right',
        'precision' : '2',
        'requiredPrecision' : 2,
        'pattern': "%s"
    },
    settings: {
        'container': '.col-main .product-info'
    },
    
    init: function (prices, options) {
        this.basePrice = prices.finalPrice;
        this.currentPrice = this.basePrice;
        this.format = prices.format;
        $.extend(this.settings, options);

        switch (this.format.display) {
            case "2":
                this.currencyChar = this.format.symbol;
                break;
            case "3":
                this.currencyChar = this.format.shortName;
                break;
            case "4":
                this.currencyChar = this.format.name;
                break;
            default :
                this.currencyChar = '';
        }
        this.currencyRate = prices.currencyRate || this.currencyRate;
        this.Variations.init(prices.variation);
        this.Modifiers.init(prices.modifier);
    },
    toCurrency: function(price, prefix, attribute) {
        prefix = prefix || '';
        attribute = attribute || '';

        var currency = this.currencyChar;
        price = price * this.currencyRate;
        
        if (this.format.position != 'Left') {
            this.format.pattern =
                attribute + ' ' + prefix + ' ' + "%s" + '' + currency;
        } else {
            this.format.pattern =
                attribute + ' ' + prefix + ' ' + currency + '' +  "%s";
        }
        return ' ' + formatCurrency(price, this.format).replace(/\s{2,}/, " ");
    },
    newPrice: function(price, amount, type, prefix /*true === +, false === -*/) {

        prefix = prefix || false;
        price = parseFloat(price);
        amount = parseFloat(amount);
            switch (type) {
            case 'to':
                return round(amount, 2);
            case 'by':
                if (prefix) {
                    return round(price + amount, 2);
                } else {
                    return round(price - amount, 2);
                }
            case 'percent' :
                if (prefix) {
                    return round(price + amount*price/100, 2);
                } else {
                    return round(price - amount*price/100, 2);
                }
                
            default:
                return round(price, 2);
        }
    },
    renderPrice: function () {

        this.currentPrice = this.Variations.getPrice() +
            this.Modifiers.getPrice(
                this.Variations.getPrice()
            );

        var discountPrice =  this.applyDiscountRule(
            this.Variations.getPrice(),
            discountRules[this.Variations.currentVariationId]
        );
        discountPrice += this.Modifiers.getPrice(discountPrice);
        
        if (this.currentPrice != discountPrice) {
            
            $('.price-box').html(
                this.settings.oldPriceTemplate +
                this.settings.specialPriceTemplate +
                this.settings.savePriceTemplate
            );

        } else {
            $('.price-box').html(this.settings.regularPriceTemplate);
        }
        
        $('.price-box .old-price .price', this.settings.container)
            .html(this.toCurrency(this.currentPrice)
        );

        $('.price-box .special-price .price', this.settings.container)
            .html(this.toCurrency(discountPrice));

        var savePrefix  = this.currentPrice - discountPrice > 0 ? "" : "-";
        $('.price-box .save-price .price', this.settings.container).html(
            savePrefix + this.toCurrency(this.currentPrice - discountPrice
        ));

        $('.price-box .regular-price .price', this.settings.container)
            .html(this.toCurrency(this.currentPrice)
        );

        this.Modifiers.renderPrice();
    },
    applyDiscountRule: function (price, rules) {
        var first = true;
        
        for (var discountId in rules) {
            rule = rules[discountId];
            if (rule.attribute && Count(rule.attribute)) {
                var ruleAttributeIds = array_keys(rule.attribute);
                //green arrows
                var currentAttributeIds = Product.Attributes.Modifiers.getAttributeIds().concat(
                    Product.Attributes.Variations.getAttributeIds()
                );
                if (!Count(array_intersect(ruleAttributeIds, currentAttributeIds))) {
                    first = false;
                    continue;
                }
            }

            if (rule.is_combined != 1) {
                if (first) {
                    return this.newPrice(price, rule.amount, rule.type);
                }
                first = false;
                continue;
            }
            price = this.newPrice(price, rule.amount, rule.type);
        }
        return price;
    }
}
Product.Attributes.Modifiers = {
    pricesRules:[],
    //currentOptionId: false,
    changeStack: [],
    currentToPriceOptionId:false,
    currentToPrice: false,
    init: function(prices) {
        this.pricesRules = prices;
        var self = this;
        $('.product-modifiers-list .sub-price').change(function() {
            self.onChange(this);
        });
        $('.product-modifiers-list .sub-price').change();
    },
    renderPrice: function() {
        var hubModifier = $('.product-modifiers-list .sub-price');
        for (hubId in hubModifier) {
            switch(hubModifier[hubId].tagName) {
                case 'SELECT':
                    this._renderSelectEl(hubModifier[hubId]);
                break;
                case 'INPUT':
                    switch($(hubModifier[hubId]).attr('type')) {
                        case 'radio':
                           this._renderRadioEl(hubModifier[hubId]);
                        break;
                        case 'checkbox':
                            this._renderCheckboxEl(hubModifier[hubId]);
                        break;
                        default:
                            this._renderTextEl(hubModifier[hubId]);
                    }
                break;
                case 'TEXTAREA':
                    this._renderTextEl(hubModifier[hubId]);
                break;
                default:
            }
        }
    },
    _analize: function (text) {
        if (/(^.+?\s+)[+|-]?\s+.*/.test(text)) {
            var match = /(^.+?\s+)[+|-]?\s+.*/.exec(text);
            return match[1];
        } else if (/^\s*[+|-].*/.test(text)) {
            return '';
        } else {
            return text;
        }
    },
    _renderSelectEl: function(element) {
        var self = this;
        $(element).children().each(function(i, el){
            var oldOption = el.innerHTML;
            var optionId = /^modifier-\d+-(\d+)$/.exec(el.id)[1];
            if (typeof optionId == 'undefined' || optionId == element.id) {
                return;
            }
            var prefix = '';
            var rule = self.pricesRules[optionId];
            prefix = rule.difference > 0 ? '+' : '-';

            var match = self._analize(oldOption);

            
            if (rule.difference == 0 /*|| (rule.type == 'to'
            && Product.Attributes.newPrice(Product.Attributes.currentPrice, rule.amount, 'to', true) < this.currentToPrice
            && this.currentToPriceOptionId != rule.optionId)*/) {

                el.innerHTML = match;
            } else {
                el.innerHTML = Product.Attributes.toCurrency(
                    rule.difference, prefix, match
                );
            }
        });
    },
    _renderRadioEl: function(element) {
        if (!$(element).attr('checked')) {
            return;
        }
        var self = this;
        var match = /^modifier-(\d+)-(\d+)$/.exec(element.id);
        var modifierId = match[1];
        if (typeof modifierId == 'undefined') {
            return;
        }
        $('input:radio', '#modifier-' + modifierId).each(function(index, el) {
            var oldOption = $('#' + el.id + '-text').html();
            var optionId  = /^modifier-\d+-(\d+)$/.exec(el.id)[1];
            var rule = self.pricesRules[optionId];
            var prefix = rule.difference > 0 ? '+' : '-';
            
            match = self._analize(oldOption);
            
            if (rule.difference == 0) {
                $('#' + el.id + '-text').html(match);
            } else {
                $('#' + el.id + '-text').html(Product.Attributes.toCurrency(
                    rule.difference, prefix, match
                ));
            }
        });
    },
    _renderCheckboxEl: function(elem) {

    },
    _renderTextEl:function(elem) {
        var optionId = /^modifier-(\d+).*$/.exec(elem.id)[1];
        var modifierId = false;
        for (modifierIndex in this.pricesRules) {
            var rule = this.pricesRules[str_replace('option', '', modifierIndex)];
            if (rule.optionId == optionId) {
                modifierId = rule.id;
                //break;
            }
        }

        if (!modifierId) return;

        rule = this.pricesRules[str_replace('option', '', modifierId)];
        var oldOption = $('#modifier-' + optionId + '-text').html();
        var prefix = rule.difference > 0 ? '+' : '-';

        var match = this._analize(oldOption);
        if (rule.difference == 0) {
            $('#modifier-' + optionId + '-text').html(match);
        } else {
            $('#modifier-' + optionId + '-text').html(Product.Attributes.toCurrency(
                rule.difference, prefix, match
            ));
        }
    },
    _updatePrices: function() {
        for (index in this.changeStack) {
            var optionId = this.changeStack[index].optionId;
            for (modifierId in this.pricesRules) {
                rule = this.pricesRules[str_replace('option', '', modifierId)];
                if (rule.optionId == optionId) {
                    var oldPrice = Product.Attributes.currentPrice;
                    rule.difference = this.getPriceByModifierId(modifierId)
                        - oldPrice;
                }
            }
        }
    },
    getPriceByModifierId: function(modifierId) {
        var stack = deepObjCopy(this.changeStack);
        for (index in stack) {
            rule = this.pricesRules[str_replace('option', '', modifierId)];
            if (stack[index].optionId == rule.optionId) {
                stack[index].modifierId = modifierId;
            }
        }

        var basePrice = Product.Attributes.Variations.getPrice();
        return basePrice + this.getPrice(basePrice, stack);
    },
    //use onchange
    _getModifierIdByOption : function (el){
        switch(el.tagName) {
            case 'SELECT':
                var modifierId = /^modifier-\d+-(\d+).*$/.exec($(':selected', el).attr('id'))[1];
                
                //var modifierId = $(el).children()[$(el).get(0).selectedIndex].id;
            break;
            case 'INPUT':
                switch($(el).attr('type')) {
                    case 'checkbox':
                        if ($(el).attr('checked')) {
                            var modifierId = /^modifier-\d+-(\d+).*$/.exec(el.id)[1];
                        } else {
                            return false;
                        }
                    break;
                    case 'radio':
                        if ($(el).attr('checked')) {
                            var modifierId = /^modifier-\d+-(\d+).*$/.exec(el.id)[1];
                        }
                    break;
                    case 'text':
                        if (!$(el).val()) {
                            return false;
                        }
                        var modifierId = $('#' + el.id + '-id').val();
                    break;
                    default:
                        var modifierId = $('#' + el.id + '-id').val();
                }
            break;
            case 'TEXTAREA':
                if ($(el).val()) {
                    var modifierId = $('#' + el.id + '-id').val();
                } else {
                    return false;
                }
            break;
            default:
                var modifierId = $('#' + el.id + '-id').val();
        }
        return modifierId;
    },
    onChange: function(el) {
        var self = this;//Product.Attributes.Modifiers
        this.changeStack = [];
        $('.product-modifiers-list .sub-price').each(function (index, element) {
            var optionId = /^modifier-(\d+).*$/.exec(element.id)[1];
            if (optionId == 'undefined') {
               optionId = index;
            }
            var value = self._getModifierIdByOption(element);
            if (typeof value != 'undefined' && value != 'undefined') {
               self.changeStack[index] = {'modifierId':value, 'optionId':optionId, 'index':index};
            }
        });

        var basePrice = Product.Attributes.Variations.getPrice();
        Product.Attributes.currentPrice = basePrice + this.getPrice(basePrice);
        //this.currentOptionId = /^modifier-(\d+).*$/.exec(el.id)[1];
        this._updatePrices();
        Product.Attributes.renderPrice();
    },
    getPrice: function (basePrice, stack) {
        basePrice = basePrice || Product.Attributes.Variations.getPrice();
        stack = stack || this.changeStack;

        var price = basePrice;
        // TO TO Kostul`
        var maxPriceTo = false;
        for (option in stack) {
            var modifierId = stack[option].modifierId;
            var optionId = stack[option].optionId;

            rule = this.pricesRules[str_replace('option', '', modifierId)];
            if (!modifierId || !optionId || rule.type != 'to') {
                continue;
            }
            var tempPrice = Product.Attributes.newPrice(
                price, rule.amount, rule.type, true
            );
            if (tempPrice > maxPriceTo) {
                maxPriceTo = tempPrice;
                this.currentToPriceOptionId = rule.optionId;
            }
        }
        if (maxPriceTo) {
            price = Product.Attributes.newPrice(price, maxPriceTo, 'to', true);
            this.currentToPrice = price;
        } else {
            this.currentToPrice = 0;
        }
        // END TO TO
        for (option in stack) {
            modifierId = stack[option].modifierId;
            optionId = stack[option].optionId;
            rule = this.pricesRules[str_replace('option', '', modifierId)];
            if (!modifierId || !optionId 
                || rule.type == 'to' /*to to kostul`*/) {
                
                continue;
            }
            price = Product.Attributes.newPrice(price, rule.amount, rule.type, true);
        }
        return round(price - basePrice, 2);
    },//end Product.Attributes.Modifiers.getPrice()
    //get attributes ids 
    getAttributeIds : function() {
        var attributeIds = [];
        var len = this.changeStack.length;
        for (var i = len; i--;) {
            if (!this.changeStack[i] || !this.changeStack[i].modifierId) {
                continue;
            }
            attributeIds[i] = this.changeStack[i].modifierId;
        }
        return attributeIds;
    }
} // End Of MOdifiers

Product.Attributes.Variations = {
    textSelect : 'Choose option',
    currentVariationId: 0,
    previosVariationId: 0,
    variation:[],
    variationOptions: {
        options:[],
        optionsLabels:[],
        valuesLabels:[]
    },
    pricesRules: [],
    changeStack : [],

    init: function(prices/*assigns, options, optionsLabels, valuesLabels, prices, textSelect*/) {
        this.variation = variationOptions.assigns;
        this.variationOptions.options = variationOptions.options;
        this.variationOptions.optionsLabels = variationOptions.optionsLabels;
        this.variationOptions.valuesLabels = variationOptions.valuesLabels;
        this.pricesRules = prices;
        this.textSelect = variationOptions.textSelect || 'Choose option';
        var self = this;
        $('.product-variations-list .sub-price').change(function() {
            self.onChange(this);
        });
        
    },
    _variationExists: function (optionId, valueId) {
        var filters = this._getFilters();
        filters[optionId] = valueId;
        for (i in this.variation) {
            var exists = true;
            var variation = this.variation[i];
            for (var j in filters) {
                if (!variation[j] || variation[j] != filters[j]) {
                    exists = false;
                    break;
                }
            }
            if (exists)
                return true;
        }
        return false;
    },
    _getFilters: function() {
        var filters = {};
        for (var i in this.changeStack) {
            var id = this.changeStack[i];
            filters[id] = $('#option-' + id)[0].value;
        }
        return filters;
    },
    _disabledVariationOptions: function() {
        $('.product-variations-list select').each(function(index, el){
            if ($(el).children().length < 2) {
                $(el).attr('disabled', true);
            }
        });
    },
    _enabledVariationOptions: function() {
        $('.product-variations-list select').attr('disabled', false);
    },
    _getVariationOptions: function() {
        var attributes = {};
        var status = false;
        $('.product-variations-list select').each(function(index, el){
            attributes[el.id.replace(/option-/, '')] = el.value;
            if (!status && el.value != 0 ) {
                status = true;
            }
        });
        if (status) {
            return attributes;
        }
        return false;
    },
    _getVariationIdByOptions: function() {
        var res = false, maxMatch = -1, match = 0, diff = -1, currentDiff = 0;
        var options = this._getVariationOptions();
        if (!options) {
            return res;
        }
        for (optionId in this.variation) {
            match = Count(array_intersect_assoc(this.variation[optionId], options));
            diff = Count(array_diff_assoc(this.variation[optionId], options));
            if (maxMatch < match) {
                maxMatch = match;
                currentDiff = diff;
                res = optionId;
            } else if (maxMatch == match) {
                if (diff == 0) {
                    res = optionId;
                } else if (currentDiff >= diff) {
                    res = false;
                }
            }
        }
        return res;
    },
    _setVariation: function(variationId) {
        var variation = this.variation[variationId];
        for (optionId in variation) {
            $('#option-' + optionId).val(variation[optionId]);
        }
    },
    //
    updateOptions: function() {
        this._enabledVariationOptions();
        for (var optionId in this.variationOptions.options) {
            if (in_array(optionId, this.changeStack))
                continue;
            var selectBoxItems = $('#option-' + optionId)[0].options;
            while (selectBoxItems.length) {
                selectBoxItems[selectBoxItems.length - 1] = null;
            }
            selectBoxItems[selectBoxItems.length] = new Option(
                this.textSelect, 0
            );

            for (var i in this.variationOptions.options[optionId]) {
                var value_id = this.variationOptions.options[optionId][i];
                if (this._variationExists(optionId, value_id)) {
                    selectBoxItems[selectBoxItems.length] = new Option(
                        this.variationOptions.valuesLabels[value_id], value_id
                    );
                }
            }
        }
    },
    onChange: function (el) {
        this._enabledVariationOptions();
        var optionId = el.id.replace(/option-/, '');
        if (in_array(optionId, this.changeStack)) {
            var i = key_by_value(optionId, this.changeStack);
            if (el.value != '0')
                ++i;
            this.changeStack = this.changeStack.slice(0, i);
        } else if (el.value != '0') {
            this.changeStack.push(optionId);
        }

        this.updateOptions();

        var variationId = this._getVariationIdByOptions();

        if (variationId) {
            this._setVariation(variationId);
            this.previosVariationId = this.currentVariationId;
            this.currentVariationId = variationId;
        } else {
            this.previosVariationId = this.currentVariationId;
            this.currentVariationId = 0;
        }

        Product.Attributes.renderPrice();
        $('.product-modifiers-list .sub-price').change(); //mass call Product.Attributes.Modifier.onChange
        this._disabledVariationOptions();
    },
    // call modifier calculatePrice
    getPrice: function () {
        var price = Product.Attributes.basePrice;
        if (this.currentVariationId) {
            price = Product.Attributes.newPrice(
                price,
                this.pricesRules[this.currentVariationId].amount,
                this.pricesRules[this.currentVariationId].type,
                true
            );
        }
        return round(price, 2);
    },
    //get attributes ids
    getAttributeIds : function() {
        var attributeIds = [];
        var options = this._getVariationOptions();
        if (!options) {
            return attributeIds;
        }
        var i = 0;
        var discountVariationRules = discountRules[this.currentVariationId];
        for(discountId in discountVariationRules) {
            var rule = discountVariationRules[discountId];
            if (!rule || !Count(rule.attribute)) {
                continue;
            }
            var attributes = rule.attribute;
            for (attributeId in attributes) {
                var attribute = attributes[attributeId];

                for(optionId in options) {
                    if (optionId == attribute.optionId
                        && options[optionId] == attribute.optionValueId) {

                        attributeIds[i] = attributeId;
                        i++;
                    }
                }
            }
        }
        return attributeIds;
    }
};