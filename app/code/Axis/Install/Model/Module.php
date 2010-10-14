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
 * @package     Axis_Install
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Install
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Install_Model_Module
{    
    /**
     * Retrieve array of all modules from filesystem
     *
     * @static
     * @return array
     */
    public static function getModules()
    {
        $code_path = AXIS_ROOT . '/app/code';
        try {
            $code_dir = new DirectoryIterator($code_path);
        } catch (Exception $e) {
            throw new Zend_Controller_Exception("Directory $path not readable");
        }
        
        $modules = array();
        foreach ($code_dir as $category) {
            $category_path = $category->getPathname();
            $category = $category->getFilename();
            if ($category[0] == '.') {
                continue;
            }
            try {
                $category_dir = new DirectoryIterator($category_path);
            } catch (Exception $e) {
                continue;
            }
            foreach ($category_dir as $module) {
                $module_path = $module->getPathname();
                $module = $module->getFilename();
                $config_file = $module_path . '/etc/config.php';
                if ($module[0] == '.' || !is_file($config_file)) {
                    continue;
                }
                include_once($config_file);
                if (!isset($config)) {
                    continue;
                }
                $modules += $config;
            }
        }
        return $modules;
    }
}