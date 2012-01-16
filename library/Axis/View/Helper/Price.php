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
 * @copyright   Copyright 2008-2012 Axis
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
    protected $_currency;

    /**
     * @var Axis_Translate
     */
    protected $_translate;

    public function __construct()
    {
        $this->_currency    = Axis::single('locale/currency');
        $this->_translate   = Axis::translate('catalog');
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
        if (!is_array($price)) { // product page
            if (false !== $priceDiscount
                && !empty($priceDiscount)
                && $price != $priceDiscount) {

                $priceSave      = $this->_currency->toCurrency($price - $priceDiscount);
                $price          = $this->_currency->toCurrency($price, $code);
                $priceDiscount  = $this->_currency->toCurrency($priceDiscount, $code);

                return '<div class="price-box"><p class="old-price">'
                    . '<span class="label">' . $this->_translate->__('Regular price') . ':</span> '
                    . '<span class="price">' . $price . '</span></p>'
                    . '<p class="special-price">'
                    . '<span class="label">' . $this->_translate->__('Special price') . ':</span> '
                    . '<span class="price">' . $priceDiscount . '</span></p>'
                    . '<p class="save-price">'
                    . '<span class="label">' . $this->_translate->__('You save') . ':</span> '
                    . '<span class="price">' . $priceSave . '</span></p></div>';
            }

            $price = $this->_currency->toCurrency($price, $code);

            return '<div class="price-box"><p class="regular-price">'
                . '<span class="label">' . $this->_translate->__('Price') . ':</span> '
                . '<span class="price">' . $price . '</span></p></div>';
        }

        // product listing
        $minPrice = $this->_currency->toCurrency($price['min_price'], $code);
        $maxPrice = $this->_currency->toCurrency($price['max_price'], $code);
        $finalMinPrice = $this->_currency->toCurrency($price['final_min_price'], $code);
        $finalMaxPrice = $this->_currency->toCurrency($price['final_max_price'], $code);
        $finalPrice = 0;
        $savePrice  = 0;

        $regularPrice = $minPrice;
        if ($price['min_price'] != $price['max_price']) {
            $regularPrice .= ' &mdash; ' . $maxPrice;
        }

        if ($price['final_min_price'] != $price['min_price']
            || $price['final_max_price'] != $price['max_price']) {

            $finalPrice = $finalMinPrice;
            if ($price['final_min_price'] != $price['final_max_price']) {
                $finalPrice .= ' &mdash; ' . $finalMaxPrice;
            }

            $minSave = $price['min_price'] - $price['final_min_price'];
            $maxSave = $price['max_price'] - $price['final_max_price'];
            $minSavePrice = $this->_currency->toCurrency($minSave, $code);
            $maxSavePrice = $this->_currency->toCurrency($maxSave, $code);

            $savePrice = $minSavePrice;
            if ($minSave != $maxSave) {
                $savePrice = $this->_translate->__(
                    "up to %s",
                    $minSave > $maxSave ? $minSavePrice : $maxSavePrice
                );
                // $savePrice .= ' &mdash; ' . $maxSavePrice;
            }
        }

        if ($savePrice) {
            return '<div class="price-box"><p class="old-price">'
                . '<span class="label">' . $this->_translate->__('Regular price') . ':</span> '
                . '<span class="price">' . $regularPrice . '</span></p>'
                . '<p class="special-price">'
                . '<span class="label">' . $this->_translate->__('Special price') . ':</span> '
                . '<span class="price">' . $finalPrice . '</span></p>'
                . '<p class="save-price">'
                . '<span class="label">' . $this->_translate->__('You save') . ':</span> '
                . '<span class="price">' . $savePrice . '</span></p></div>';
        }

        return '<div class="price-box"><p class="regular-price">'
            . '<span class="label">' . $this->_translate->__('Price') . ':</span> '
            . '<span class="price">' . $regularPrice . '</span></p></div>';
    }
}
