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
 * @package     Axis_ShippingTable
 * @subpackage  Axis_ShippingTable_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_ShippingTable
 * @subpackage  Axis_ShippingTable_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_ShippingTable_Model_Standard extends Axis_Method_Shipping_Model_Abstract
{
    protected $_code = 'Table_Standard';
    protected $_title = 'Table Rate';
    protected $_description = 'Table shipping method';

    public function getAllowedTypes($request)
    {
        switch ($this->_config->type) {
            case 'Per Weight':
                $value = $request['weight'];
                break;
            case 'Per Item':
                $value = $request['qty'];
                break;
            case 'Per Price':
            default:
                $value = $request['price'];
                break;
        }

        $select = Axis::table('shippingtable_rate')->select();
        $select->where('value <= ? ', $value)
            ->where('site_id = ? OR site_id = 0', Axis::getSiteId())
            ->where('country_id = ? OR country_id = 0', $request['country']['id'])
            ->where('zip = ? OR zip = "*"', $request['postcode'])
            ->order('site_id DESC')
            ->order('country_id ASC')
            ->order('zone_id ASC')
            ->order('zip ASC')
            ->order('price DESC')
            ->limit(1);

        if (isset($request['zone']['id'])) {
            $select->where('zone_id = ? OR zone_id = 0', $request['zone']['id']);
        } else {
            $select->where('zone_id = 0');
        }

        $rows = $select->query()->fetchAll();
        $row = current($rows);

        $this->_types = array(
            array(
                'id'    => $this->_code,
                'title' => $this->getTitle(),
                'price' => $row['price'] + (is_numeric($this->_config->handling) ?
                    $this->_config->handling : 0)
            )
        );

        return $this->_types;
    }
}