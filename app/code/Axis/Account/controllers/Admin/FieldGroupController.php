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
 * @package     Axis_Account
 * @subpackage  Axis_Account_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Admin_FieldGroupController extends Axis_Admin_Controller_Back
{
    public function listAction()
    {
        $select = Axis::model('account/customer_fieldGroup')->select('*')
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

        $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->foundRows())
            ->sendSuccess();
    }

    public function loadAction()
    {
        $rowset = Axis::model('account/customer_fieldGroup')
            ->select(array('acfl.language_id', '*'))
            ->joinLeft(
                'account_customer_fieldgroup_label',
                'acfl.customer_field_group_id = acf.id',
                '*'
            )
            ->where('acf.id = ?', $this->_getParam('id', 0))
            ->fetchAssoc();

        if (!$rowset) {
            Axis::message()->addError(
                Axis::translate('account')->__(
                    'Group %s not found', $this->_getParam('id', 0)
                )
            );
            return $this->_helper->json->sendFailure();
        }

        $data['group'] = current($rowset);
        unset($data['group']['customer_field_group_id']);
        unset($data['group']['group_label']);
        unset($data['group']['language_id']);
        foreach (Axis::model('locale/option_language') as $languageId => $_n) {
            $label = isset($rowset[$languageId]['group_label']) ?
                $rowset[$languageId]['group_label'] : '';

            $data['label']['lang_' . $languageId] = $label;
        }

        $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }

    public function saveAction()
    {
        $data = $this->_getParam('group');
        $data['name'] = preg_replace(
            array("/[^a-z0-9\s+]/", "/\s+/"),
            array('', '_'),
            strtolower($data['name'])
        );

        $mFieldGroup = Axis::model('account/customer_fieldGroup');

        $duplicate = $mFieldGroup->select('id')
            ->where('name = ?', $data['name'])
            ->where('id <> ?', $data['id'])
            ->fetchOne();
        if ($duplicate) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'Record %s already exist', $data['name']
                )
            );
            $this->_helper->json->sendFailure();
        }

        $row = $mFieldGroup->getRow($data);
        $row->save();

        $modelLabel = Axis::model('account/customer_fieldGroup_label');
        $labels     = $this->_getParam('label', array());
        foreach ($labels as $languageId => $label) {
            $rowLabel = $modelLabel->getRow($row->id, $languageId);
            $rowLabel->group_label = $label;
            $rowLabel->save();
        }

        Axis::message()->addSuccess(
            Axis::translate('account')->__(
                'Group was saved successfully'
            )
        );

        $this->_helper->json
            ->setData(array('id' => $row->id))
            ->sendSuccess();
    }

    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));
        Axis::single('account/Customer_FieldGroup')
            ->delete($this->db->quoteInto('id IN(?)', $data));

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Group was deleted successfully'
            )
        );
        $this->_helper->json->sendSuccess();
    }
}
