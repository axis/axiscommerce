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
 * @package     Axis_Location
 * @subpackage  Axis_Location_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Location
 * @subpackage  Axis_Location_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Location_Admin_GeozoneZoneController extends Axis_Admin_Controller_Back
{
    public function listAction()
    {
        $select = Axis::single('location/geozone_zone')->select('*')
            ->calcFoundRows()
            ->addFilters($this->_getParam('filter', array()))
            ->joinInner('location_geozone',
                'lg.id = lgz.geozone_id',
                array('geozone_name' => 'name')
            )
            ->joinLeft('location_country',
                'lc.id = lgz.country_id',
                array('country_name' => 'name', 'iso_code_2', 'iso_code_3')
            )
            ->joinLeft('location_zone',
                'lz.id = lgz.zone_id',
                array('zone_name' => 'name', 'zone_code' => 'code')
            )
            ->order(
                $this->_getParam('sort', 'id')
                . ' '
                . $this->_getParam('dir', 'DESC')
            );

        if ($geozoneId = $this->_getParam('geozone_id')) {
            $select->where('geozone_id = ?', $geozoneId);
        }

        return $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->foundRows())
            ->sendSuccess()
        ;
    }

    public function loadAction()
    {
        $id = $this->_getParam('id', 0);
        $data = Axis::single('location/geozone_zone')->find($id)
            ->current()
            ->toArray();
        return $this->_helper->json->sendRaw($data);
    }

    public function saveAction()
    {
        $data = $this->_getAllParams();
        Axis::single('location/geozone_zone')->save($data);
        return $this->_helper->json->sendSuccess();
    }

    public function removeAction()
    {
        $data = Zend_Json_Decoder::decode($this->_getParam('data'));
        if (!sizeof($data)) {
            return;
        }
        Axis::single('location/geozone_zone')->delete(
            $this->db->quoteInto('id IN(?)', $data)
        );

        return $this->_helper->json->sendSuccess();
    }
}