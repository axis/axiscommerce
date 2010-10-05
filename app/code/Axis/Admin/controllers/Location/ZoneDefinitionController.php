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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Location_ZoneDefinitionController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('location')->__(
            'Zones Definitions'
        );

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
        
        $data = Axis::single('location/geozone_zone')
            ->getListByGeozone($this->_getParam('gzoneId'));
        
        $this->_helper->json->sendSuccess(array(
            'data' => $data
        ));
    }
    
    public function getAssignAction()
    {
        $this->_helper->layout->disableLayout();
        
        $this->_helper->json->sendJson(
            Axis::single('location/geozone_zone')
                ->find($this->_getParam('assignId', 0))->current()->toArray(),
            false, false
        );
    }
    
    public function saveAssignAction()
    {
        $this->_helper->layout->disableLayout();
        $data = $this->_getAllParams();
        $table = Axis::single('location/geozone_zone');
        if ($data['assignId']) {
        	$table->update(
                array(
                    'country_id' => $data['country'],
                    'zone_id' => $data['zone']
                ),
                'id = ' . intval($data['assignId'])
        	);
        } else {
        	$table->insert(
                array(
                    'geozone_id' => $data['gzoneId'],
                    'country_id' => $data['country'],
                    'zone_id' => $data['zone']
                )
            );
        }
        
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