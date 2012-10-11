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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Catalog_Upgrade_0_3_1 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.3.1';

    protected $_info = '';

    public function up()
    {       
        $rowset = Axis::single('core/config_field')->select()
            ->where('model = ?', 'Manufacturer')
            ->fetchRowset();
        
        foreach ($rowset as $row) {
            $row->model = 'catalog/option_product_manufacturer';
            $row->save();
        }
        
        $paths = array(
            'image/watermark/position'             => 'catalog/option_watermark_position',
            'catalog/lightzoom/zoomStagePosition'  => 'catalog/option_lightzoom_stagePosition',
            'catalog/lightzoom/zoomCursor'         => 'catalog/option_lightzoom_cursor',
            'catalog/lightzoom/zoomOnTrigger'      => 'catalog/option_lightzoom_domEvent_onTrigger',
            'catalog/lightzoom/zoomOffTrigger'     => 'catalog/option_lightzoom_domEvent_offTrigger',
            'catalog/lightzoom/lightboxTrigger'    => 'catalog/option_lightzoom_domEvent_trigger',
            'catalog/lightzoom/switchImageTrigger' => 'catalog/option_lightzoom_domEvent_imageTrigger',
            'catalog/listing/type'                 => 'catalog/option_product_listing_type'
           
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