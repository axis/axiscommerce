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
$(document).ready(function(){
    
    var elZone = $('.input-zone');
    
    elZone.after(
        '<input type="text" class="'
        + elZone.attr('class') + ' input-text" name="'
        + elZone.attr('name') + '" id="'
        + elZone.attr('id') + '"'
        + ' />'
    );
        
    function toggleZone(el)
    {
        var select = el, textfield = el.next();

        if (select.children().length) {
            select.insertAfter(textfield)
                .removeAttr('disabled')
                .show();
            textfield.attr('disabled', 'disabled')
                .hide();
        } else {
            select.insertAfter(textfield)
                .attr('disabled', 'disabled')
                .hide();
            textfield.removeAttr('disabled')
                .show();
        }
    }
    toggleZone(elZone);
    
    $('.input-country').change(function(){

        elZone.removeOption(/./);

        if (Zones[this.value]) {
            elZone.addOption(Zones[this.value], false);
        }
        toggleZone(elZone);
    });
});

function form_send()
{
    $('#form_submit').attr('disabled', 'true')
    $.ajax({
        type: "POST",
        url : Axis.getUrl('account/address-book/save', true),
        data : $('#form-new-address').serialize(),
        dataType: 'json',
        success: function(response) {
            $('#form_submit').removeAttr('disabled');
            $("#form-new-address input").removeClass('error');
            $('div.error').remove();
            if (true === response) {
                window.location = Axis.getUrl('account/address-book', true);
            } else {
                for (var i in response) {
                    var messages = response[i];
                    var message = "";
                    $('#' + i).addClass('error');
                    for (var j in messages) {
                        message += messages[j] + '<br />';
                    }
                    $('#' + i).after('<div class="error">' + message + '</div>');
                }
            }
        }
    });
}