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

Ext.onReady(function(){
    /*category tree*/
    function initTree(root)
    {
        var inputs = document.getElementById(root).getElementsByTagName('input');
            for (var i=0; i<inputs.length; i++) {
                if (inputs[i].checked)
                    inputs[i].parentNode.className = inputs[i].parentNode.className + " selected";
        }

        $(".tree-cat").click(function() {
            if ($(this).is(".selected")) {
                $(this).removeClass("selected");
                $(this).children(":first").attr("checked", false);
            } else {
                $(this).addClass("selected");
                $(this).children(":first").attr("checked", true);
            }
        });

        $(".toggle-header").click(function() {
            $(this).next().toggle('fast');
        });
    }
/*category tree*/

    initTree('tab-categories-block');
    $(".date-picker").datepicker({dateFormat: 'yy-mm-dd'});
});

function attributesOnChange(el)
{
    var optionId = el.value;
    var selectOptions = $(el).nextAll('select').get(0).options;
    while (selectOptions.length) {
        selectOptions[selectOptions.length - 1] = null;
    }

    if (Attributes[optionId]) {
        for (var optionValueId in Attributes[optionId].option) {
            selectOptions[selectOptions.length] = new Option(
                Attributes[optionId].option[optionValueId], optionValueId
            );
        }
    }

}

function addCondition(element)
{
    var conditionType = element.value;
    switch (conditionType) {
        case 'attribute':
            clone = $('#condition-attribute-template').clone().removeAttr('id');
            $('#condition-list').append(clone);
            $('#condition-list .attribute-option').change();
            break;
        case 'price':
            clone = $('#condition-price-template').clone().removeAttr('id');
            $('#condition-list').append(clone);
            break;
        case 'date':
            $('#condition-date-template .hasDatepicker').removeClass('hasDatepicker');
            clone = $('#condition-date-template').clone().removeAttr('id');
            $('#condition-list').append(clone);
            $(".date-picker").datepicker({dateFormat: 'yy-mm-dd'});
            break;
        case 'product':
            clone = $('#condition-prodId-template').clone().removeAttr('id');
            $('#condition-list').append(clone);
            break;
        default:
            break;
    }
    element.value  = 'empty';
}

function deleteCondition(el)
{
    var elForRemove = el.parentNode;
    elForRemove.parentNode.removeChild(elForRemove);
}

//function saveDiscount()
//{
//    var params = {};
//    if (Discount.id)
//        params.id = Discount.id;
//    Ext.Ajax.request({
//        url: Axis.getUrl('discount_index/save'),
//        form: 'form-discount',
//        params : params,
//        callback: function(options, success, response) {
//            var oResponse = Ext.decode(response.responseText);
//            if (oResponse.id) {
//                $('#button-delete').removeClass('x-hidden');
//                Discount.id = oResponse.id;
//            }
//        }
//    });
//
//}
//
function saveBackDiscount()
{
    var params = {};
    if (Discount.id)
        params.id = Discount.id;
    Ext.Ajax.request({
        url: Axis.getUrl('discount_index/save'),
        form: 'form-discount',
        params : params,
        callback: function(options, success, response) {
            window.location = Axis.getUrl('discount_index/');
        }
    });
}

function deleteDiscount()
{
    if (!confirm('Delete discount?'))
        return false;
    window.location = Axis.getUrl('discount_index/delete/id/') + Discount.id;
}
