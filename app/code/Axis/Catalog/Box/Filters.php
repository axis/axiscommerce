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
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Box
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Catalog_Box_Filters extends Axis_Core_Box_Abstract
{
    protected $_title = 'Filters';
    protected $_class = 'box-filter';
    public $hurl;

    public function init()
    {
        $this->hurl = Axis_HumanUri::getInstance();

        $filterSet = array();
        $filters = $this->_getActiveFilters();

        // Get category filters
        /*$catId = $this->hurl->hasParam('cat') ?
            $this->hurl->getParamValue('cat') : Axis::single('catalog/category')->getRoot()->id;
        $result['category'] = Axis::single('catalog/category')->find($catId)->current()->getChildItems(false, true);*/

        // Get price filters
        $filterSet['price'] = $this->_getPriceFilters($filters);

        // Get manufacturer filters
        if (!$this->hurl->hasParam('manufacturer')) {
            $filterSet['manufacturer'] = $this->_getManufacturerFilters($filters);
        }

        // Attribute filters
        $filterSet['attributes'] = $this->_getAttributeFilters($filters);

        if ((count($filters) && !$this->hurl->hasParam('cat'))
            || (1 < count($filters) && $this->hurl->hasParam('cat'))) { // we don't show categories in filters

            $this->filters = $filterSet;
            return true;
        }

        foreach($filterSet as $filter) {
            if (count($filter)) {
                $this->filters = $filterSet;
                return true;
            }
        }

        return false;
    }

    protected function _beforeRender()
    {
        return $this->hasData('filters');
    }

    /**
     * Retrieve array of applied filters
     * @return array
     */
    protected function _getActiveFilters()
    {
        $filters = array();
        if ($this->hurl->hasParam('cat')) {
            $filters['category_ids'] = $this->hurl->getParamValue('cat');
        }

        // Filter by manufacturer
        if ($this->hurl->hasParam('manufacturer')) {
            $filters['manufacturer_ids'] = $this->hurl->getParamValue('manufacturer');
        }

        // by price
        if ($this->hurl->hasParam('price')) {
            $price = explode(',', $this->hurl->getParam('price'));
            $filters['price'] = array(
                'from'  => $price[0],
                'to'    => !empty($price[1]) ? $price[1] : null
            );
        }

        // by attributes
        if ($this->hurl->hasParam('attributes')) {
            $filters['attributes'] = $this->hurl->getAttributeIds();
        }

        return $filters;
    }

    /**
     * @param  array $filters
     * @return mixed
     */
    protected function _getManufacturerFilters(array $filters)
    {
        $select = Axis::single('catalog/product')->select(array(
                'cnt' => 'COUNT(DISTINCT cp.id)'
            ))
            ->joinCategory()
            ->addCommonFilters($filters)
            ->addManufacturer()
            ->group('cpm.id')
            ->order('cpmd.title')
            ->where('cp.manufacturer_id IS NOT NULL');

        $manufacturers = $select->fetchAll();

        if (!count($manufacturers)) {
            return null;
        }

        return $manufacturers;
    }

    /**
     * Возвращает список фильтрова для атрибутов
     *
     * @return mixed
     */
    protected function _getAttributeFilters(array $filters)
    {
        $select = Axis::single('catalog/product')->select(array(
                'cnt' => 'COUNT(DISTINCT cp.id)'
            ))
            ->joinCategory()
            ->addCommonFilters($filters)
            ->joinInner('catalog_product_attribute',
                'cp.id = cpa.product_id',
                array(
                    'option_id',
                    'option_value_id'
                ))
            ->join('catalog_product_option', 'cpa.option_id = cpo.id')
            ->join('catalog_product_option_text',
                'cpa.option_id = cpot.option_id',
                array(
                    'option_name' => 'name'
                ))
            ->joinInner('catalog_product_option_value_text',
                'cpa.option_value_id = cpovt.option_value_id',
                array(
                    'value_name' => 'name'
                ))
            ->where('cpo.filterable = 1')
            ->where('cpot.language_id = ?', Axis_Locale::getLanguageId())
            ->where('cpovt.language_id = ?', Axis_Locale::getLanguageId())
            ->group(array('cpa.option_id', 'cpa.option_value_id'));

        if (isset($filters['attributes']) && count($filters['attributes'])) {
            $select->where('cpa.option_id NOT IN (?)', array_keys($filters['attributes']));
        }

        $attributes = $select->fetchAll();
        if (!count($attributes)) {
            return null;
        }

        $filters = array();
        foreach ($attributes as $attribute) {
            if (!isset($filters[$attribute['option_id']])) {
                $filters[$attribute['option_id']] = array();
            }

            $filters[$attribute['option_id']][] = $attribute;
        }
        return $filters;
    }

    /**
     * Return price filters
     *
     * @return mixed
     */
    protected function _getPriceFilters(array $filters)
    {
        $select = Axis::single('catalog/product')->select(
            array('cnt' => 'COUNT(cp.price)')
        );
        $select->joinPriceIndex()
            ->columns(array(
                'price_max' => 'MAX(cppi.final_max_price)',
                'price_min' => 'MIN(cppi.final_min_price)'
            ))
            ->joinCategory()
            ->addCommonFilters($filters);

        $row = $select->fetchRow2();

        if (!$row->cnt) {
            return null;
        }

        $currency = Axis::single('locale/currency');
        $row->price_max = $currency->to($row->price_max);
        $row->price_min = $row->price_min > 0 ? $currency->to($row->price_min) : 1;
        $rate = $currency->getData(null, 'rate');

        //Return rounded number, example: 80->10, 120->100, 895->100, 1024->1000
        $roundTo = pow(10, strlen((string) floor($row->price_max - $row->price_min)) - 1);
        $select->reset();
        $select->from('catalog_product', array(
                'cnt'         => 'COUNT(DISTINCT cp.id)',
                'price_group' => new Zend_Db_Expr("floor(cppi.final_min_price * $rate / $roundTo) * $roundTo")
            ))
            ->joinPriceIndex()
            ->joinCategory()
            ->addCommonFilters($filters)
            ->group('price_group')
            ->order('cppi.final_min_price');

        $priceGroups = $select->fetchAll();

        if (count($priceGroups) < 2) {
            return null;
        }

        return array(
            'roundTo' => $roundTo,
            'groups'  => $priceGroups
        );
    }
}