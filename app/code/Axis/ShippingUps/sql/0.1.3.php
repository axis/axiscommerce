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
 * @package     Axis_ShippingUps
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_ShippingUps_Upgrade_0_1_3 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.3';
    protected $_info = '';

    public function up()
    {
        $_pickup = array( 
            'RDP' => Axis_ShippingUps_Model_Standard_Pickup::RDP,
            'CC'  => Axis_ShippingUps_Model_Standard_Pickup::CC,
            'OTP' => Axis_ShippingUps_Model_Standard_Pickup::OTP,
            'OCA' => Axis_ShippingUps_Model_Standard_Pickup::OCA,
            'LC'  => Axis_ShippingUps_Model_Standard_Pickup::LC
        );
        
        $rowset = Axis::single('core/config_value')->select()
            ->where('path = ?', 'shipping/Ups_Standard/pickup')
            ->fetchRowset();
        foreach ($rowset as $row) {
            $row->value = isset($_pickup[$row->value]) ? 
                $_pickup[$row->value] : Axis_ShippingUps_Model_Standard_Pickup::CC;
            $row->save();
        }
        ///////////////////////////
        $_package = array( 
            'CP'  => Axis_ShippingUps_Model_Standard_Package::CP,
            'ULE' => Axis_ShippingUps_Model_Standard_Package::ULE,
            'UT'  => Axis_ShippingUps_Model_Standard_Package::UT,
            'UEB' => Axis_ShippingUps_Model_Standard_Package::UEB
        );
        $rowset = Axis::single('core/config_value')->select()
            ->where('path = ?', 'shipping/Ups_Standard/package')
            ->fetchRowset();
        foreach ($rowset as $row) {
            $row->value = isset($_package[$row->value]) ? 
                $_package[$row->value] : Axis_ShippingUps_Model_Standard_Package::CP;
            $row->save();
        }
        ///////////////////////////
        $_dest = array( 
            'RES' => Axis_ShippingUps_Model_Standard_DestinationType::RES,
            'COM' => Axis_ShippingUps_Model_Standard_DestinationType::COM
        );
        $rowset = Axis::single('core/config_value')->select()
            ->where('path = ?', 'shipping/Ups_Standard/res')
            ->fetchRowset();
        foreach ($rowset as $row) {
            $row->value = isset($_dest[$row->value]) ? 
                $_dest[$row->value] : Axis_ShippingUps_Model_Standard_DestinationType::RES;
            $row->save();
        }
        ///////////////////////////
        $paths = array(
            'shipping/Ups_Standard/pickup'  => 'Axis_ShippingUps_Model_Standard_Pickup',
            'shipping/Ups_Standard/package' => 'Axis_ShippingUps_Model_Standard_Package',
            'shipping/Ups_Standard/res'     => 'Axis_ShippingUps_Model_Standard_DestinationType',
            'shipping/Ups_Standard/measure' => 'Axis_ShippingUps_Model_Standard_Measure',
            'shipping/Ups_Standard/type'    => 'Axis_ShippingUps_Model_Standard_RequestType'
            
        );
        $rowset = Axis::single('core/config_field')->select()->fetchRowset();
        
        foreach ($rowset as $row) {
            if (isset($paths[$row->path])) {
                $row->config_options = null; 
                $row->model = $paths[$row->path];
                $row->save();
            }
        }
    }
}
