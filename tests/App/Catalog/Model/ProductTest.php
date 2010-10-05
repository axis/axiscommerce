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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */
require_once dirname(__FILE__) . '/../../../bootstrap.php';

/**
 *
 * @category    Axis
 * @package     Axis_Test
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class App_Catalog_Model_ProductTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Axis_Catalog_Model_Product
     */
    private $_product;

    public function setUp()
    {
        $this->_product = new Axis_Catalog_Model_Product();
    }

    public function testProduct()
    {
        $this->assertEquals(true, true);
    }

    public function testSave()
    {
        $data = array(array('is_active' => true));
        $row = $this->_product->save($data);
        $this->assertTrue($row instanceof Axis_Catalog_Model_Product_Row);
    }
}