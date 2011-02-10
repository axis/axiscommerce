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
 * @package     Axis_View
 * @subpackage  Axis_View_Helper
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Axis_View_Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Helper_Box
{
    /**
     * Return the singleton instance of box object
     *
     * @param mixed string|array $boxClass
     * @return Axis_Core_Box_Abstract
     * @throws Axis_Exception
     */
    public function box($boxClass)
    {
        if (is_array($boxClass)) {
            $config = $boxClass;
            $boxClass = $config['boxCategory']
                . '_' . $config['boxModule']
                . '/' .$config['boxName'];
        } else {
            if (strstr($boxClass, '_')) {
                list($namespace, $module) = explode('_', $boxClass, 2);
            } else {
                $namespace = 'Axis';
                $module = $boxClass;
            }
            list($module, $name) = explode('/', $module, 2);
            $config = array(
                'boxCategory' => $namespace,
                'boxModule'   => $module,
                'boxName'     => $name
            );
        }
        $boxClass = Axis::getClass($boxClass, 'Box');
        if (@!class_exists($boxClass)) {
            $response = Zend_Controller_Front::getInstance()->getResponse();
            if (!count($response->getException())) {
                $exception = new Axis_Exception(
                    Axis::translate('core')->__(
                        'Class %s not found', $boxClass
                    )
                );
                $response->setException($exception);

                throw $exception;
            } else {
                return;
            }
        }

        if (Zend_Registry::isRegistered($boxClass)) {
            return Zend_Registry::get($boxClass)->updateData($config, true);
        }
        return Axis::single($boxClass, $config);
    }
}