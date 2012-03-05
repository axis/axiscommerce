<?php
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
 * @category    Axis
 * @package     Axis_Catalog
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Catalog_Upgrade_0_2_9 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.9';
    protected $_info = '';

    public function up()
    {       
        $rowset = Axis::single('core/config_field')->select()
            ->where('model = ?', 'Manufacturer')
            ->fetchRowset();
        
        foreach ($rowset as $row) {
            $row->model = 'Axis_Catalog_Model_Option_Product_Manufacturer';
            $row->save();
        }
        
        $paths = array(
            'image/watermark/position'             => 'Axis_Catalog_Model_Watermark_Position',
            'catalog/lightzoom/zoomStagePosition'  => 'Axis_Catalog_Model_Lightzoom_StagePosition',
            'catalog/lightzoom/zoomCursor'         => 'Axis_Catalog_Model_Lightzoom_Cursor',
            'catalog/lightzoom/zoomOnTrigger'      => 'Axis_Catalog_Model_Lightzoom_DomEvent_OnTrigger',
            'catalog/lightzoom/zoomOffTrigger'     => 'Axis_Catalog_Model_Lightzoom_DomEvent_OffTrigger',
            'catalog/lightzoom/lightboxTrigger'    => 'Axis_Catalog_Model_Lightzoom_DomEvent_Trigger',
            'catalog/lightzoom/switchImageTrigger' => 'Axis_Catalog_Model_Lightzoom_DomEvent_ImageTrigger',
            'catalog/listing/type'                 => 'Axis_Catalog_Model_Product_Listing_Type'
           
        );
        $rowset = Axis::single('core/config_field')->select()->fetchRowset();
        
        foreach ($rowset as $row) {
            if (isset($paths[$row->path])) {
                $row->model = $paths[$row->path];
                $row->save();
            }
        }
    }
}