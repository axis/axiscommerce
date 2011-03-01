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
     * @param string $block
     * @return Axis_Core_Box_Abstract
     * @throws Axis_Exception
     */
    public function box($block)
    {
        $block = Axis::getClass($block, 'Box');
        list($namespace, $module, $_box_, $name) = explode('_', $block);
        $config = array(
            'box_namespace' => $namespace,
            'box_module'    => $module,
            'box_name'      => $name
        );
        
        if (@!class_exists($block)) {
            $response = Zend_Controller_Front::getInstance()->getResponse();
            if (!count($response->getException())) {
                $exception = new Axis_Exception(
                    Axis::translate('core')->__(
                        'Class %s not found', $block
                    )
                );
                $response->setException($exception);

                throw $exception;
            } else {
                return;
            }
        }

        if (Zend_Registry::isRegistered($block)) {
            return Zend_Registry::get($block)->refresh();
        }
        return Axis::single($block, $config);
    }
}