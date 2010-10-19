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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Helper_Box
{
    /**
     * Return the singleton instance of box object
     *
     * @param string $boxClass locale/currency|Axis_Locale_Box_Currency
     * @param array $config [optional]
     * @return Axis_Core_Box_Abstract
     * @throws Axis_Exception
     */
    public function box($boxClass, $config = array())
    {
        $boxClass = Axis::getClass($boxClass, 'Box');

        $tempClass = str_replace('_Box', '', $boxClass);
        foreach (array('boxCategory', 'boxModule', 'boxName') as $option) {
            if ($option === 'boxName') {
                if (empty($tempClass)) {
                    echo $boxClass;die;
                }
                $config[$option] = $tempClass;
                break;
            }
            $optionLength = strpos($tempClass, '_');
            $config[$option] = substr($tempClass, 0, $optionLength);
            $tempClass = substr($tempClass, $optionLength + 1);
        }

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