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
class Axis_Account_Admin_FieldController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('account')->__(
            'Custom Customer Fields'
        );

        $mField = Axis::model('account/customer_field');
        $fieldTypes = array();
        foreach ($mField->getFieldTypes() as $id => $name) {
            $fieldTypes[] = array(
                'id'    => $id,
                'name'  => $name
            );
        }
        $this->view->fieldTypes = $fieldTypes;

        $validators = array();
        foreach ($mField->getValidators() as $id => $name) {
            $validators[] = array(
                'id'    => $id,
                'name'  => $name
            );
        }
        $this->view->validators = $validators;

        $this->render();
    }

    public function listAction()
    {
        $select = Axis::model('account/customer_field')->select('*')
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
        $rowset = Axis::model('account/customer_field')
            ->select(array('acfl.language_id', '*'))
            ->joinLeft(
                'account_customer_field_label',
                'acfl.customer_field_id = acf.id',
                '*'
            )
            ->where('acf.id = ?', $this->_getParam('id', 0))
            ->fetchAssoc();

        if (!$rowset) {
            Axis::message()->addError(
                Axis::translate('account')->__(
                    'Field %s not found', $this->_getParam('id', 0)
                )
            );
            return $this->_helper->json->sendFailure();
        }

        $data['field'] = current($rowset);
        unset($data['field']['customer_field_id']);
        unset($data['field']['field_label']);
        unset($data['field']['language_id']);
        foreach (array_keys(Axis_Collect_Language::collect()) as $languageId) {
            $label = isset($rowset[$languageId]['field_label']) ?
                $rowset[$languageId]['field_label'] : '';

            $data['label']['lang_' . $languageId] = $label;
        }

        $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }

    public function saveAction()
    {
        $row  = Axis::model('account/customer_field')->save(
            $this->_getParam('field')
        );

        $mLabel = Axis::model('account/customer_field_label');
        $labels = $this->_getParam('label', array());
        foreach ($labels as $languageId => $label) {
            $rowLabel = $mLabel->getRow($row->id, $languageId);
            $rowLabel->field_label = $label;
            $rowLabel->save();
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
            )
        );
        $this->_helper->json
            ->setData(array(
                'id' => $row->id
            ))
            ->sendSuccess();
    }

    public function batchSaveAction()
    {
        $dataset = Zend_Json::decode($this->_getParam('data'));
        $mField  = Axis::model('account/customer_field');
        foreach ($dataset as $data) {
            $row = $mField->save($data);
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
            )
        );
        $this->_helper->json->sendSuccess();
    }

    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));
        Axis::single('account/customer_field')->delete(
            $this->db->quoteInto('id IN (?)', $data)
        );
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Field was deleted successfully'
            )
        );
        $this->_helper->json->sendSuccess();
    }
}