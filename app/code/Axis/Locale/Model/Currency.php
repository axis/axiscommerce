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
 * @package     Axis_Locale
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Locale
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Locale_Model_Currency extends Axis_Db_Table
{
    protected $_name = 'locale_currency';

    /**
     * Currencies data (code, rate, etc.)
     *
     * @var array
     */
    private $_data = array();

    /**
     * @var string - 'USD'
     */
    private $_currentCurrencyCode = null;

    /**
     * Array of Axis_Currency objects
     *
     * @var array
     */
    private $_currency = array();

    /**
     *
     * @return array
     */
    public function getFormat()
    {
        $row = $this->getData();
        $currency = $this->getCurrency();

        $position = $row['position'];
        if ($position == 8) { // Standard
           $position = $currency->toCurrency(1);
           $position = strpos($position, $currency->getSymbol());
           if ($position) {
               $position = 'Right';
           } else {
               $position = 'Left';
           }
        } elseif ($position == 16) {
            $position = 'Right';
        } else {
            $position = 'Left';
        }

        $symbols = Zend_Locale_Data::getList($row['format'], 'symbols');
        return array(
            'precision'         => $row['currency_precision'],
            'requiredPrecision' => 2,
            'integerRequired'   => 1,
            'decimalSymbol'     => $symbols['decimal'],
            'groupSymbol'       => $symbols['group'],
            'groupLength'       => 3,
            'position'          => $position,
            'symbol'            => null === $currency->getSymbol() ?
                $currency->getShortName() : $currency->getSymbol(),
            'shortName'         => $currency->getShortName(),
            'name'              => $currency->getName(),
            'display'           => $row['display']
        );
    }

    /**
     *
     * @param string $code
     * @return bool
     */
    public function isExists($code)
    {
        if (!empty($code) && $this->getData($code)) {
            return true;
        }
        return false;
    }

    /**
     * Return Axis_Currency object
     *
     * @param string $code
     * @return Axis_Currency
     */
    public function getCurrency($code = '')
    {
        if (empty($code)) {
            $code = $this->getCode();
        }
        if (!isset($this->_instanses[$code])) {
            $options = $this->_getCurrencyOptions($code);
            $currency = new Axis_Currency(
                $options['currency'],
                $options['format'] === null ?
                    Axis_Locale::getLocale() : $options['format']
            );
            $currency->setFormat($options);
            $this->_currency[$code] = $currency;
        }
        return $this->_currency[$code];
    }

    /**
     * @static
     * @return const array
     */
    public static function getPositionOptions()
    {
        return array(
            '8'     => Axis::translate('locale')->__('Standard'),
            '16'    => Axis::translate('locale')->__('Right'),
            '32'    => Axis::translate('locale')->__('Left')
        );
    }

    /**
     * @static
     * @return const array
     */
    public static function getDisplayOptions()
    {
        return array(
            '1' => Axis::translate('locale')->__('No Symbol'),
            '2' => Axis::translate('locale')->__('Use Symbol'),
            '3' => Axis::translate('locale')->__('Use Shortname'),
            '4' => Axis::translate('locale')->__('Use Name')
        );
    }

    /**
     *
     * @return string
     */
    public function getCode()
    {
        if (null !== $this->_currentCurrencyCode) {
            return $this->_currentCurrencyCode;
        }

        if (isset(Axis::session()->currency)
            && $this->isExists(Axis::session()->currency)) {

            $this->_currentCurrencyCode = Axis::session()->currency;

        } elseif (isset(Axis::config()->locale->main->currency)
            && $this->isExists(Axis::config()->locale->main->currency)) {

            $this->_currentCurrencyCode = Axis::config()->locale->main->currency;

        } elseif ($this->isExists(Axis_Locale::DEFAULT_CURRENCY)) {
            $this->_currentCurrencyCode = Axis_Locale::DEFAULT_CURRENCY;
        } else {
            $this->_currentCurrencyCode = $this->select('code')
                ->order('id')
                ->fetchOne();
            if (!$this->_currentCurrencyCode) {
                throw new Axis_Exception(
                    Axis::translate('locale')->__('No currencies found')
                );
            }
        }
        return $this->_currentCurrencyCode;
    }

    /**
     *
     * @param string $code
     * @return array
     */
    private function _getCurrencyOptions($code)
    {
        $row = $this->getData($code);

        return array(
            'currency'  => $row['code'],
            'position'  => (int) $row['position'],
            'display'   => (int) $row['display'],
            'format'    => $row['format'],
            'precision' => (int) $row['currency_precision']
        );
    }

    //@todo swap arguments
    public function getData($code = '', $key = '')
    {
        if (empty($code)) {
            $code = $this->getCode();
        }

        if (!isset($this->_data[$code])) {
            $this->_data[$code] = $this->select()
                ->where('code = ?', $code)
                ->fetchRow();
        }

        if (!empty($key)) {
            return $this->_data[$code][$key];
        }

        return $this->_data[$code];
    }

    /**
     *
     * @param float $price
     * @param bool $useRate [optional]
     * @param string $code [optional]
     * @param bool $format [optional]
     * @return float
     */
    public function toCurrency($price, $code = '', $format = true)
    {
        $price *= $this->getData($code, 'rate');
        if (!$format) {
            return $price;
        }
        return $this->getCurrency($code)->toCurrency($price);
    }

    /**
     * From some currency to abstract currency
     *
     * @param float $price
     * @param string $code
     * @return float
     */
    public function from($price, $code = '')
    {
        return $price / $this->getData($code, 'rate');
    }

    /**
     * From abstract currency to code currency
     *
     * @param float $price
     * @param string $code
     * @return float
     */
    public function to($price, $code = '')
    {
        return $price * $this->getData($code, 'rate');
    }

    /**
     * Convert currency
     *
     * @param float $price
     * @param string $from
     * @param string $to
     * @return float
     */
    public function convert($price, $from, $to)
    {
        return ($price * $this->getData($to, 'rate')) /
            $this->getData($from, 'rate');
    }

    /**
     *
     * @param array $data
     * @return bool
     */
    public function batchSave($data)
    {
        $result = true;
        foreach ($data as $id => $values) {
            $format = empty($values['format']) ?
                new Zend_Db_Expr('NULL') : $values['format'];
            $rowData = array(
                'id'                 => $id,
                'code'               => $values['code'],
                'title'              => $values['title'],
                'position'           => $values['position'],
                'display'            => $values['display'],
                'format'             => $format,
                'currency_precision' => $values['currency_precision'],
                'rate'               => $values['rate']
            );

            $result = $result && (bool) $this->save($rowData);
        }
        return $result;
    }

    /**
     *
     * @param array $rowData
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function save(array $rowData)
    {
        $id = false;
        if (isset($rowData['id']) && null !== $rowData['id']) {

            $id = $rowData['id'];
            unset($rowData['id']);
        }

        if (!$id || !$row = $this->fetchRow(
                $this->getAdapter()->quoteInto('id = ?', $id)
            )) {

            $row = $this->createRow($rowData);
        } else {
            $row->setFromArray($rowData);

            if ($row->code === Axis::config()->locale->main->currency &&
                $row->rate != 1) {

    //            throw new Axis_Exception(
                Axis::message()->addError(Axis::translate('locale')->__(
                    'Base currency rate should be 1.00'
                ));
                return false;
            }
        }

        $row->rate = str_replace(',', '.', $row->rate);

        return $row->save();
    }
}