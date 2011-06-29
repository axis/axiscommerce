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
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Template_Mail extends Axis_Db_Table 
{
    protected $_name = 'core_template_mail';
    protected $_rowClass = 'Axis_Db_Table_Row';
    
    /**
     * Updates or add record
     * 
     * @param array $data
     * @return bool
     */
    public function save($data)
    {
        if (!sizeof($data)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'No data to save'
            ));
            return false;
        }

        foreach ($data as $id => $rowset) {
        
            /* saving content into file */ 
            if (!empty($data[$id]['content'])) {    
                $file = Axis::config()->system->path . '/app/design/mail/' 
                          . $data[$id]['template'] . '_'
                          . $data[$id]['type'] . '.phtml';
                if (!is_writable($file)) {
                    Axis::message()->addError(
                        Axis::translate('core')->__(
                            "Can't open file with write permissions : %s", $file
                    ));
                } else {
                    if (!@file_put_contents($file, $data[$id]['content'])) {
                        Axis::message()->addError(
                            Axis::translate('core')->__(
                                "Can't write content to file :%s", $file
                        ));
                    }
                }
            }
        
            if (!isset($rowset['id']) 
                || !$row = $this->find($rowset['id'])->current()) {
                
                $row = $this->createRow();
            }
            $row->setFromArray($rowset);
            $row->save();
        }
        
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
        ));
        return true;
    }
    
    /**
     * Retrieve template info by id
     * 
     * @param int $id
     * @return array
     */
    public function getInfo($id)
    {
        if (!is_numeric($id) || !$info = $this->find($id)->current()) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'Template not found'
            ));
            return array();
        }
        
        $info = $info->toArray();
        $templates = Axis_Collect_MailTemplate::collect();
        
        $file = Axis::config()->system->path . '/app/design/mail/' 
              . $templates[$info['template']] . '_'
              . $info['type'] . '.phtml';

        $content = '';
        if (is_readable($file)) {
            $content = @file_get_contents($file);
        }
        
        $info['content'] = $content;
        
        return $info;
    }
}