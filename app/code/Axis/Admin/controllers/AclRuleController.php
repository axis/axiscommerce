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
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_AclRuleController extends Axis_Admin_Controller_Back
{
    public function listAction()
    {
        $roleId = $this->_getParam('role_id');
        $select = Axis::model('admin/acl_rule')->select('*')
            ->where('role_id = ?', $roleId)
//            ->calcFoundRows()
        ;

        return $this->_helper->json
            ->setData($select->fetchAll())
//            ->setCount($select->foundRows())
            ->sendSuccess()
        ;
    }
    
    public function batchSaveAction()
    {
        $dataset = Zend_Json::decode($this->_getParam('dataset'));
        $model = Axis::model('admin/acl_rule');
        
        foreach ($dataset as $data) {
            $row = $model->select()
                ->where('role_id = ?', $data['role_id'])
                ->where('resource_id = ?', $data['resource_id'])
                ->fetchRow()
            ;
            if (!$row) {
                $row = $model->createRow($data);
            }
            
            if (empty($data['permission'])) {
                $row->delete();
            } else {
                $row->permission = $data['permission'];
                $row->save();
            }
        }
        
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Rules was saved successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}