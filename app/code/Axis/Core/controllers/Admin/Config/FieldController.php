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
 * @subpackage  Axis_Core_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Admin_Config_FieldController extends Axis_Admin_Controller_Back
{
    public function listAction()
    {
        $node = $this->_getParam('node', '0');
        $model = Axis::single('core/config_field');
        
        $select = $model->select()->order('title ASC');
        if ('0' == $node) {
           $select->where('lvl = 1');
        } else {
           $select->where('lvl = 2')->where("path LIKE ?", $node . '/%');
        }

        $i = 0;
        foreach ($select->fetchRowset() as $row) {
            $_translator = Axis::translate($row->getTranslationModule());
            $data[$i] = array(
                'text' => $_translator->__($row->title),
                'id'   => $row->path,
                'leaf' => false
            );
            if ('0' != $node) {
                $data[$i]['children'] = array();
                $data[$i]['expanded'] = true;
            }
            ++$i;
        }
        
        return $this->_helper->json->sendRaw($data);
    }
    
}