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

    function toggleZone(select) {
        var textfield = $('#state-row').find('input');
        if (select.children().length) {
            select.removeAttr('disabled')
                .parent('li')
                .show();
            textfield.attr('disabled', 'disabled')
                .parent('li')
                .hide();
        } else {
            select.attr('disabled', 'disabled')
                .parent('li')
                .hide();
            textfield.removeAttr('disabled')
                .parent('li')
                .show();
        }
    }
    toggleZone($('#zone_id-row').find('select'));

    $('.input-country').change(function() {
        var zoneSelect = $('#zone_id-row').find('select');
        zoneSelect.html('');
        if (Zones[this.value]) {
            for (var id in Zones[this.value]) {
                var zone    = Zones[this.value][id],
                    option  = '<option label="' + zone + '" value="' + id + '">'
                        + zone
                        + '</option>';
                zoneSelect.append(option);
            }
        }
        toggleZone(zoneSelect);
    });
});
