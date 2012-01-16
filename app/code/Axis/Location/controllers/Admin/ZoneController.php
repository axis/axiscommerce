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
class Axis_Location_Admin_ZoneController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('location')->__('States / Provinces');

        $this->view->countries = Axis::single('location/country')
            ->fetchAll()
            ->toArray();

        $this->render();
    }

    public function listAction()
    {
        $showAllZones = (bool) $this->_getParam('show_allzones', true);

        $select = Axis::single('location/zone')->select('*')
            ->calcFoundRows()
            ->addFilters($this->_getParam('filter', array()))
            ->limit(
                $this->_getParam('limit', 20),
                $this->_getParam('start', 0)
            )
            ->order(
                $this->_getParam('sort', 'name')
                . ' '
                . $this->_getParam('dir', 'ASC')
            );

        if (!$showAllZones) {
            $select->where('id <> 0');
        }

        return $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->foundRows())
            ->sendSuccess()
        ;
    }

    public function batchSaveAction()
    {
        $_rowset = Zend_Json::decode($this->_getParam('data'));

        if (!sizeof($_rowset)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'No data to save'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        $model = Axis::single('location/zone');
        foreach ($_rowset  as $_row) {
            $model->save($_row);
        }

        return $this->_helper->json->sendSuccess();
    }

    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));

        if (!count($data)) {
            Axis::message()->addError(
                Axis::translate('location')->__(
                    'No zone found to delete'
                )
            );

            return $this->_helper->json->sendFailure();
        }
        Axis::single('location/zone')->delete(
            $this->db->quoteInto('id IN(?)', $data)
        );

        Axis::message()->addSuccess(
            Axis::translate('location')->__(
                'Zone was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}