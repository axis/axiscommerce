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
class Axis_View_Helper_Price
{
    /**
     * @var Axis_Locale_Model_Currency
     */
    private $_currency;

    public function __construct()
    {
        $this->_currency = Axis::single('locale/currency');
    }

    /**
     *
     * @param float $price
     * @param bool $priceDiscount [optional]
     * @param mixed float|bool $useRate [optional]
     * @param string $code [optional]
     * @return string
     */
    public function price($price, $priceDiscount = false, $code  = '')
    {
        // @todo from - to
        if (is_array($price)) {
            /*$priceFrom = $this->_currency->toCurrency($price['from'], $code);
            $priceTo = $this->_currency->toCurrency($price['to'], $code);

            return '<span class="price">' . $priceFrom . '</span> - <span class="price">' . $priceTo . '</span>';
*/            $price = $price['price'];
        } /*elseif*/
        if (false !== $priceDiscount
            && !empty($priceDiscount)
            && $price != $priceDiscount) {

            $priceSave = $this->_currency->toCurrency($price - $priceDiscount);
            $price = $this->_currency->toCurrency($price, $code);
            $priceDiscount = $this->_currency->toCurrency($priceDiscount, $code);

            return '<div class="price-box"><p class="old-price">'
                 . '<span class="label">' . Axis::translate('catalog')->__('Regular price') . ':</span> '
                 . '<span class="price">' . $price . '</span></p>'
                 . '<p class="special-price">'
                 . '<span class="label">' . Axis::translate('catalog')->__('Special price') . ':</span> '
             . '<span class="price">' . $priceDiscount . '</span></p>'
                 . '<p class="save-price">'
                 . '<span class="label">' . Axis::translate('catalog')->__('You save') . ':</span> '
                 . '<span class="price">' . $priceSave . '</span></p></div>';
        }
        $price = $this->_currency->toCurrency($price, $code);
        return '<div class="price-box"><p class="regular-price">'
            . '<span class="label">' . Axis::translate('catalog')->__('Price') . ':</span> '
            . '<span class="price">' . $price . '</span></p></div>';
    }
}