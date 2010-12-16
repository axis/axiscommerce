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
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Location_GeozoneController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('location')->__('Geozones');

        $countries = Axis::single('location/country')
            ->select(array('id', 'name'))
            ->order('name')
            ->fetchPairs();

        $countries = array('0' => 'All') + $countries;
        $zones = Axis::single('location/zone')->fetchAll()->toArray();
        $countryZones = array();
        foreach ($zones as $zone) {
                $countryZones[$zone['country_id']][] = $zone;
        }
        $this->view->countries = $countries;
        $this->view->countryZones = $countryZones;
        $this->render();
    }

    public function listAction()
    {
        $dbField = new Axis_Filter_DbField();

        $order = $dbField->filter($this->_getParam('sort', 'id')) . ' '
               . $dbField->filter($this->_getParam('dir', 'ASC'));

        $limit = (int) $this->_getParam('limit', 20);
        $start = $this->_getParam('start', 0);

        $select = Axis::single('location/geozone')
            ->select()
            ->calcFoundRows()
            ->order($order)
            ->limit($limit, $start)
            ;

        return $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->count())
            ->sendSuccess();
    }

    public function saveAction()
    {
        $this->getHelper('layout')->disableLayout();
        $data = Zend_Json::decode($this->_getParam('data'));

        if (!sizeof($data)) {
            return;
        }
        $modelGeozone = Axis::single('location/geozone');

        foreach ($data as $rowData) {
            try {
                $modelGeozone->save($rowData);

            } catch (Zend_Db_Exception $e) {
                if (23000 === $e->getCode()) {

                    Axis::message()->addError(
                        Axis::translate('location')->__(
                            "An error has been occured while trying to save '%s' zone. 'priority' field should be unique",
                            $rowData['name']
                    ));
                }
                Axis::message()->addError($e->getMessage());
            }
        }
        $this->_helper->json->sendSuccess();
    }

    public function deleteAction()
    {
        $this->getHelper('layout')->disableLayout();
        $ids = Zend_Json_Decoder::decode($this->_getParam('data'));
        if (!count($ids)) {
            return;
        }
        Axis::single('location/geozone')
            ->delete($this->db->quoteInto('id IN(?)', $ids));
        $this->_helper->json->sendSuccess();
    }

    public function listAssignsAction()
    {
        $this->_helper->layout->disableLayout();
        $geozoneId = $this->_getParam('geozone_id');
        $data = Axis::single('location/geozone_zone')->select('id')
            ->join('location_geozone',
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
               ->where("geozone_id = ?", $geozoneId)
               ->fetchAll()
        ;
        $this->_helper->json->setData($data)->sendSuccess();
    }

    public function getAssignAction()
    {
        $this->_helper->layout->disableLayout();
        $id = $this->_getParam('id', 0);
        $data = Axis::single('location/geozone_zone')->find($id)
            ->current()
            ->toArray();
        $this->_helper->json->sendJson($data, false, false);
    }

    public function saveAssignAction()
    {
        $this->_helper->layout->disableLayout();
        $data = $this->_getAllParams();
        Axis::single('location/geozone_zone')->save($data);
        $this->_helper->json->sendSuccess();
    }

    public function deleteAssignsAction()
    {
        $this->_helper->layout->disableLayout();
        $data = Zend_Json_Decoder::decode($this->_getParam('data'));
        if (!sizeof($data)) {
            return;
        }
        Axis::single('location/geozone_zone')->delete(
            $this->db->quoteInto('id IN(?)', $data)
        );

        $this->_helper->json->sendSuccess();
    }
}