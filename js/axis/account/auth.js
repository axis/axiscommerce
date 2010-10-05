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
function loginCheck(response) {
    if (response.logged) {
        if (response.returnUrl) {
            window.location = Axis.getUrl(response.returnUrl);
        } else {
            window.location = Axis.getUrl('account');
        }
    } else {
        var message = 'Wrong login. ';
        for (var i = 0, n = response.messages.length; i < n; i++) {
            message += response.messages[i] + ' ';
        }
        alert('Wrong login');
    }
}

function login(e) {
    $.ajax({
        type: "POST",
        url : Axis.getUrl('account/auth/login', true),
        data: $('#form-login').serialize(),
        success: loginCheck,
        dataType: 'json'
    });
}

function register() {
    $.ajax({
        type: "POST",
        url : Axis.getUrl('account/auth/register', true),
        data: $('#form-signup').serialize(),
        dataType: 'json',
        success: function(response) {
            $("#form-signup input").removeClass('error');
            $('div.error').remove();
            if (true === response) {
                alert('OK. Now you can login');
                $('#form-signup')[0].reset();
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

function toggle_signup()
{
    if ($('#block-signup').is(':hidden')) {
        $('#block-signup').slideDown('slow');
    } else {
        $('#block-signup').slideUp('slow');
    }
}