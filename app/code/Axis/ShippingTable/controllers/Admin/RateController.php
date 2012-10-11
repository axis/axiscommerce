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
 * @package     Axis_Import
 * @subpackage  Axis_Import_Admin_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_ShippingTable
 * @subpackage  Axis_ShippingTable_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_ShippingTable_Admin_RateController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('shippingTable')->__("Table Rate");
        $this->render();
    }
    
    public function listAction()
    {
        $filter = $this->_getParam('filter', array());
        $limit  = $this->_getParam('limit', 25); 
        $start  = $this->_getParam('start', 0);
        $order  = $this->_getParam('sort', 'id') . ' '
            . $this->_getParam('dir', 'DESC');

        $select = Axis::model('shippingTable/rate')->select('*')
            ->calcFoundRows()
            ->addFilters($filter)
            ->limit($limit, $start)
            ->order($order);
        
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
        $model = Axis::model('shippingTable/rate');
        foreach($_rowset as $_row) {
            $row = $model->getRow($_row);
            $row->save();
        }

        return $this->_helper->json->sendSuccess();
    }
    
    public function removeAction() 
    {
        $data = Zend_Json::decode($this->_getParam('data'));

        if (!count($data)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'No data to save'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        Axis::model('shippingTable/rate')->delete(
            $this->db->quoteInto('id IN(?)', $data)
        );

        Axis::message()->addSuccess(
            Axis::translate('location')->__(
                'Data was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
    
    public function exportAction() 
    {
        $this->_helper->layout->disableLayout();
        $filename = 'shippingtablerate.csv';
        $rowset = Axis::model('shippingTable/rate')->select()
            ->fetchRowset();
        $countrySet = Axis::model('location/country')->select()->fetchAssoc();
        $zoneSet = Axis::model('location/zone')->select()->fetchAssoc();
        $delimiter = ',';
        $enclosure = '"';
        
        ob_start();
        $outstream = fopen("php://output", "w");
        if (is_resource($outstream)) {
            $titles = explode(',', 'Country,Region/State,Zip,Value,Price');
            fputcsv($outstream, $titles, $delimiter, $enclosure);
            foreach ($rowset as $row) { 
                fputcsv($outstream, array(
                    $countrySet[$row->country_id]['iso_code_3'],
                    $zoneSet[$row->zone_id]['code'],
                    $row->zip,
                    $row->value,
                    $row->price
                ), $delimiter, $enclosure);
            }
            $content = ob_get_clean();
            $content = utf8_decode($content);
        }
        ob_end_clean();
        
        $this->getResponse()
            ->clearAllHeaders()
            ->setHeader('Content-Description','File Transfer', true)
            ->setHeader('Content-Type', 'text/csv; charset=utf-8', true)
            ->setHeader('Content-Disposition','attachment; filename=' . $filename, true)
            ->setHeader('Content-Transfer-Encoding','binary', true)
            ->setHeader('Expires','0', true)
            ->setHeader('Cache-Control','private, must-revalidate', true)
            ->setHeader('Pragma','public', true)
            ->setHeader('Content-Length: ', mb_strlen($content, 'utf-8'), true)
            ;
        $this->getResponse()->setBody($content);
    }
    
    public function importAction()
    {
        $this->_helper->layout->disableLayout();
        try {
            $upload = new Zend_File_Transfer_Adapter_Http();
            $upload->addValidator('Count', 1, 1)
                ->addValidator('Extension', false, 'csv');
            if (!$upload->isValid()) {
                throw new Zend_Validate_Exception();
            }
            $files = $upload->getFileInfo();
            $filename = $files['data']['tmp_name'];
            
            if (!file_exists($filename) || !$fp = fopen($filename, 'r')) {
                throw new Axis_Exception(
                    Axis::translate('core')->__(
                        "Can't open file : %s", $filename
                ));
            }
            $siteId     = $this->_getParam('site_id', Axis::getSiteId());
            $keys       = fgetcsv($fp);
            $rowSize    = count($keys);
            $model      = Axis::model('shippingTable/rate');
            $countrySet = Axis::model('location/country')->select(array('iso_code_3', 'id'))
                ->fetchPairs();
            while (!feof($fp)) {
                $value = fgetcsv($fp);
                if (!is_array($value)) {
                    continue;
                }
                $value = array_pad($value, $rowSize, '');
                $value = array_combine($keys, $value);
                
                $countryId = 0;
                if (isset($countrySet[$value['Country']])) {
                   $countryId = $countrySet[$value['Country']];
                }
                $zoneSet = Axis::model('location/zone')->select(array('code', 'id'))
                    ->where('country_id = ?', $countryId)
                    ->fetchPairs();
                
                $zoneId = 0;
                if (isset($zoneSet[$value['Region/State']])) {
                   $zoneId = $zoneSet[$value['Region/State']];
                }
                               
                $model->getRow(array(
                    'site_id'    => $siteId,
                    'country_id' => $countryId,
                    'zone_id'    => $zoneId,
                    'zip'        => $value['Zip'],  
                    'value'      => $value['Value'],
                    'price'      => $value['Price']
                ))->save();
            }
            
        } catch (Zend_Exception $e) {
            return $this->getResponse()->appendBody(
                Zend_Json::encode(array(
                    'success' => false,
                    'messages' => array('error' => $e->getMessage())
                ))
            );
        }
        return $this->getResponse()->appendBody(
            Zend_Json::encode(array(
                'success' => true,
                'messages' => array(
                    'success' => Axis::translate('admin')->__(
                        'Data was imported successfully'
                    )
                )
            ))
        );
    }
}