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

$(document).ready(function() {

    var elZone = $('select.input-zone');

    elZone.after(
        '<input type="text" class="'
        + elZone.attr('class') + ' input-text" name="'
        + elZone.attr('name') + '" id="'
        + elZone.attr('id') + '"'
        + ' />'
    );

    function toggleZone(el) {
        var select      = el,
            textfield   = el.parent().find('input.input-zone');

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

    $('.input-country').change(function() {
        elZone.html('');
        if (Zones[this.value]) {
            for (var id in Zones[this.value]) {
                var zone    = Zones[this.value][id],
                    option  = '<option label="' + zone + '" value="' + id + '">'
                        + zone
                        + '</option>';
                elZone.append(option);
            }
        }
        toggleZone(elZone);
    });
});
