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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Location
 * @subpackage  Axis_Location_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Location_Admin_GeozoneController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('location')->__('Geozones');

        $this->view->countries = Axis::single('location/country')
            ->select(array('id', 'name'))
            ->order('name')
            ->fetchPairs();

        $zones = Axis::single('location/zone')->fetchAll()->toArray();
        $countryZones = array();
        foreach ($zones as $zone) {
            $countryZones[$zone['country_id']][] = $zone;
        }
        $this->view->countryZones = $countryZones;

        $this->render();
    }

    public function listAction()
    {
        $select = Axis::single('location/geozone')->select('*')
            ->calcFoundRows()
            ->addFilters($this->_getParam('filter', array()))
            ->limit(
                $this->_getParam('limit', 25),
                $this->_getParam('start', 0)
            )
            ->order(
                $this->_getParam('sort', 'id')
                . ' '
                . $this->_getParam('dir', 'DESC')
            );

        return $this->_helper->json->sendSuccess(array(
            'data'  => $select->fetchAll(),
            'count' => $select->foundRows()
        ));
    }

    public function batchSaveAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));

        if (!count($data)) {
            return;
        }

        $model = Axis::single('location/geozone');
        foreach ($data as $rowData) {
            $model->save($rowData);
        }

        return $this->_helper->json->sendSuccess();
    }

    public function removeAction()
    {
        $ids = Zend_Json::decode($this->_getParam('data'));
        if (!count($ids)) {
            return;
        }
        Axis::single('location/geozone')
            ->delete($this->db->quoteInto('id IN(?)', $ids));
        return $this->_helper->json->sendSuccess();
    }
}