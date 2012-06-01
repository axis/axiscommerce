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
 * @package     Axis_Contacts
 * @subpackage  Axis_Contacts_Admin_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Contacts
 * @subpackage  Axis_Contacts_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Contacts_Admin_DepartmentController extends Axis_Admin_Controller_Back
{
    public function listAction()
    {
        $select = Axis::single('contacts/department')->select('*')
            ->join('contacts_department_name',
                'cd.id = cdn.department_id  AND cdn.language_id = :languageId',
                'cdn.name'
            )->bind(array(
                'languageId' => Axis_Locale::getLanguageId()
            ));

        return $this->_helper->json
            ->setData($select->fetchAll())
            ->sendSuccess()
        ;
    }

    public function loadAction()
    {
        $data = array();
        $id = $this->_getParam('id');
        $row = Axis::single('contacts/department')->find($id)->current();

        if ($row) {
            $data = $row->toArray();

            $names = Axis::single('contacts/department_name')->select(
                    array('language_id', 'name')
                )->where('department_id = ?', $row->id)
                ->fetchPairs();
            foreach ($names as $languageId => $name) {
                $data['name']['language_' . $languageId] = $name;
            }

        }

        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }

    public function saveAction()
    {
        $_row = array(
            'id' => $this->_getParam('id'),
            'email' => $this->_getParam('email')
        );
        $model = Axis::single('contacts/department');
        $row = $model->getRow($_row);

        $row->save();

        $modelName = Axis::single('contacts/department_name');
        $names = $this->_getParam('name');
        foreach (Axis::model('locale/option_language') as $languageId => $lname) {

            $modelName->getRow(array(
                'department_id' => $row->id,
                'language_id'   => $languageId,
                'name'          => isset($names[$languageId]) ? $names[$languageId] : ''
            ))->save();
        }
        Axis::message()->addSuccess(
            Axis::translate('contacts')->__(
               'Department was saved succesfully'
            )
        );

        return $this->_helper->json->sendSuccess();
    }

    public function removeAction()
    {
        $id = $this->_getParam('id', 0);

        if (!$id) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'No data to delete'
                )
            );
            return $this->_helper->json->sendFailure();
        }

        $model = Axis::single('contacts/department');

        $model->delete($this->db->quoteInto('id = ?', $id));
        Axis::message()->addSuccess(
            Axis::translate('contacts')->__(
                'Department was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}
