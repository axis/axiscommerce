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
class Axis_Core_Model_Config_Field_Row extends Axis_Db_Table_Row
{
    public function getTranslationModule()
    {
        $translationModule = $this->translation_module;
        
        if (!empty($translationModule)) {
            return $translationModule;
        }
            
        if (!Zend_Registry::isRegistered('config_translation_modules')) {
            Zend_Registry::set(
                'config_translation_modules',
                Axis::single('core/config_field')
                    ->select(array('path', '*'))
                    ->where('lvl < 3')
                    ->order('lvl DESC')
                    ->fetchAssoc()
            );
        }
        
        $fields = Zend_Registry::get('config_translation_modules');
        $path = $this->path;
        while (empty($translationModule)) {
            if (!strstr($path, '/')) {
                break;
            }
            $path = substr($path, 0, strrpos($path, '/'));
            $translationModule = $fields[$path]['translation_module'];
        }
        
        if (empty($translationModule)) {
            $translationModule = 'Axis_Admin';
        }
        
        $this->translation_module = $translationModule;
        
        return $this->translation_module;
    }
}