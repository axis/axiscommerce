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
 * @package     Axis_Test
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

require_once dirname(__FILE__) . '../../../bootstrap.php';

/**
 *
 * @category    Axis
 * @package     Axis_Test
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class App_Catalog_Model_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('App_Catalog_Model_AllTests');
        $suite->addTestSuite('App_Catalog_Model_ProductTest');
        return $suite;
    }
}