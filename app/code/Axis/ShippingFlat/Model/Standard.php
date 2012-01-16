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
 * @package     Axis_ShippingFlat
 * @subpackage  Axis_ShippingFlat_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_ShippingFlat
 * @subpackage  Axis_ShippingFlat_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_ShippingFlat_Model_Standard extends Axis_Method_Shipping_Model_Abstract
{
    protected $_code = 'Flat_Standard';
    protected $_title = 'Flat Rate';
    protected $_description = 'Flat Rate';
    protected $_icon = '';

    public function getAllowedTypes($request)
    {
        if (!$this->_config->multiPrice) {
            return array();
        }
        $this->_types = array();
        foreach ($this->_config->multiPrice->toArray() as $id => $item) {
            if (!empty($item['minOrderTotal'])
                && $request['price'] < $item['minOrderTotal']) {

                continue;
            }
            if (!empty($item['maxOrderTotal'])
                && $request['price'] > $item['maxOrderTotal']) {

                continue;
            }

            $price = $item['price'];
            if ($this->_config->type === 'Per Item') {
                $price = $request['qty'] * $price;
            }

            $this->_types[] = array(
                'id' => $this->_code . '_' . $id,
                'title' => $this->getTranslator()->__($item['title']),
                'price' => $price
            );
        }
        return $this->_types;
    }
}