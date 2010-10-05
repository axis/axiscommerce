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
function forgotConfirm() {
    $.ajax({
        type: "POST",
        url : Axis.getUrl('account/forgot/confirm', true),
        data: $('#form-forgot-confirm').serialize(),
        dataType: 'json',
        success: function(response) {
            $("#form-forgot-confirm input").removeClass('error');
            $('div.success').remove();
            $('div.error').remove();
            if (true === response) {
                $('#hash').after('<div class="success"> Your password change success </div>');
            } else {
                for (var i in response) {
                    var messages = response[i];
                    var message = "";
                    $('#' + i).addClass('error');
                    for (var j in messages)
                        message += messages[j] + '<br />';
                    $('#' + i).after('<div class="error">'+message+'</div>');
                }
            }
        }
    });
}