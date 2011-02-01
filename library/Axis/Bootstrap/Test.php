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
 * @package     Axis_Bootstrap
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Bootstrap
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Bootstrap_Test extends Axis_Bootstrap
{
    protected function _initConfig()
    {
        $this->bootstrap('Loader');
        $config = Zend_Registry::get('config');

        Zend_Registry::set('config', new Axis_Config($config, true));
        return Axis::config();
    }

    protected function _initArea()
    {
        Zend_Registry::set('area', 'front');
    }
}