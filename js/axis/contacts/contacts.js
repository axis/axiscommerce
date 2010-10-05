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

function contact_us_send()
{
    $('#contact_us_submit').attr('disabled', 'true')
    $.ajax({
        type: "POST",
        url : Axis.getUrl('contacts/index/send'),
        data: $('#form-contact-us').serialize(),
        dataType: 'json',
        success: function(response) {
             $('#contact_us_submit').removeAttr('disabled');
            $("#form-contact-us input").removeClass('error');
            $('div.error').remove();
            if (true === response) {
                alert('Message sent.');
                $('#form-contact-us')[0].reset();
            } else {
                for (var i in response) {
                    var messages = response[i];
                    var message = "";
                    $('#' + i).addClass('error');
                    for (var j in messages)
                        message += messages[j] + '<br />';
                    $('#' + i).after('<div class="error">'+message+'<\/div>');
                }
            }
        }
    });
}
