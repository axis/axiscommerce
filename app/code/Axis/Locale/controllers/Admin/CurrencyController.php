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
 * @package     Axis_Locale
 * @subpackage  Axis_Locale_Admin_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Locale
 * @subpackage  Axis_Locale_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Locale_Admin_CurrencyController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle         = Axis::translate('locale')->__('Currency List');
        $this->view->currencyDisplays  = Axis::model('locale/option_currency_display')->toArray();
        $this->view->currencyPositions = Axis::model('locale/option_currency_position')->toArray();
        $this->view->zendLocales       = Axis::model('locale/option_ZendLocale')->toArray();
        $this->view->zendCurrencies    = Axis::model('locale/option_ZendCurrency')->toArray();
        $this->render();
    }
    
    public function listAction()
    {
        $select = Axis::single('locale/currency')
            ->select()
            ->calcFoundRows();
        return $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->count())
            ->sendSuccess();
    }
    
    public function saveAction()
    {
        $data = $this->_getParam('currency');
        
        if (!sizeof($data)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'No data to save'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        
        Axis::single('locale/currency')->save($data);

        Axis::message()->addSuccess(
            Axis::translate('locale')->__(
                'Currency was saved successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
    
    public function batchSaveAction()
    {
        $dataset = Zend_Json::decode($this->_getParam('data'));
        
        if (!sizeof($dataset)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'No data to save'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        
        $model = Axis::model('locale/currency');
        foreach ($dataset as $_row) {
            $model->save($_row);
        }
        
        Axis::message()->addSuccess(
            Axis::translate('locale')->__(
                'Currency was saved successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
    
    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));
        
        if (!sizeof($data)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'No data to delete'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        Axis::single('locale/currency')->delete(
            $this->db->quoteInto('id IN(?)', $data)
        );

        Axis::message()->addSuccess(
            Axis::translate('locale')->__(
                'Currency was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}