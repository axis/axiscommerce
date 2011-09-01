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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Admin_ValueSetController extends Axis_Admin_Controller_Back
{
    public function listAction()
    {
        $data = array();
        $rowset = Axis::model('account/Customer_ValueSet')->fetchAll();
        foreach ($rowset as $row) {
            $data[] = array(
                'leaf'    => true,
                'id'      => $row->id,
                'text'    => $row->name,
                'iconCls' => 'folder'
            );
        }
        return $this->_helper->json->sendRaw($data);
    }
    
    public function saveAction()
    {
        $_row = Zend_Json::decode($this->_getParam('data'));
        $row  = Axis::model('account/Customer_ValueSet')->save($_row);
        if (!$row) {
            return $this->_helper->json->sendFailure();
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
            )
        );
        return $this->_helper->json
            ->setValuesetId($row->id)
            ->sendSuccess();
    }
    
    public function removeAction()
    {
        $id = $this->_getParam('id');
        Axis::single('account/Customer_ValueSet')
            ->delete($this->db->quoteInto('id IN(?)', $id));
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Group was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}