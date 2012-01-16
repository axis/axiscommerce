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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

Axis.FileUploader = Ext.extend(Ext.ux.FileUploader, {
    
    processSuccess: function(options, response, o) {
        var record = false;
        
        if (this.singleUpload) {
            this.store.each(function(r) {
                r.set('state', 'done');
                r.set('error', '');
                r.commit();
            });
        } else {
            record = options.record;
            record.set('state', 'done');
            record.set('error', '');
            // modification begin
            record.set('file', Ext.decode(response.responseText).data.file);
            // modification end
            record.commit();
        }
        
        this.deleteForm(options.form, record);
    }
    
});
