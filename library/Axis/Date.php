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
 * @package     Axis_Date
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Date
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Date extends Zend_Date
{
    /**
     * Constructor
     * @param  string|integer|Zend_Date|array  $date    OPTIONAL Date value or value of date part to set
     *                                                 ,depending on $part. If null the actual time is set
     * @param  string                          $part    OPTIONAL Defines the input format of $date
     * @param  string|Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return Axis_Date
     * @throws Zend_Date_Exception
     */
    public function __construct($date = null, $part = null, $locale = null)
    {
        if (null === $locale) {
            $locale = Axis_Locale::getLocale();
        }
        // supress notice about PCRE without UTF8 support
        @parent::__construct($date, $part, $locale);
    }

    /**
     * Returns a clone of $this, with the time part set to 00:00:00.
     *
     * @param  string|Zend_Locale  $locale  OPTIONAL Locale for parsing input
     * @return Axis_Date
     */
    public function getDate($locale = null)
    {
        // supress notice about PCRE without UTF8 support
        return @parent::getDate($locale);
    }

    /**
     * Returns the Axis_Date with determined timestamp
     *
     * @static
     * @param  int     $timestamp Unix timestamp
     * @return Axis_Date
     */
    public static function timestamp($timestamp)
    {
        return new Axis_Date($timestamp);
    }

    /**
     * Returns the actual date as string
     *
     * @static
     * @param  string|Zend_Locale        $locale  OPTIONAL Locale for parsing input
     * @return Axis_Date
     */
    public static function now($locale = null)
    {
        return new Axis_Date(time(), self::TIMESTAMP, $locale);
    }

    /**
     * set timezone
     *
     * @param null | string
     * @return Zend_Date_DateObject  Provides fluent interface
     */
    public function setTimezone($timezone = null)
    {
        if (null === $timezone) {
            $timezone = Axis_Locale::getDefaultTimezone();
        }
        try {
            return parent::setTimezone($timezone);
        } catch(Zend_Date_Exception $e) {
            return parent::setTimezone(Axis_Locale::DEFAULT_TIMEZONE);
        }
    }

    /**
     * @param  string              $format  OPTIONAL Rule for formatting output. If null the default date format is used
     * @param  string|Zend_Locale  $locale  OPTIONAL Locale for parsing input
     * @return string
     */
    public function toPhpString($format = null, $locale = null)
    {
        self::setTimezone(Axis_Locale::getDefaultTimezone());
        return $this->toString($format, 'php', $locale);
    }

    /**
     * Returns a string for sql request
     *
     * @param  string|Zend_Locale  $locale  OPTIONAL Locale for parsing input
     * @return string
     */
    public function toSQLString($locale = null)
    {
        self::setTimezone(Axis_Locale::getDefaultTimezone());
        return $this->toString('yyyy-MM-dd HH:mm:ss', 'iso', $locale);
    }

    /**
     * Returns this, change timestamp on day start time
     *
     * @return Axis_Date provides fluent interface
     */
    public function getStartDay()
    {
        $this->subDay(1);
        return $this->setTimestamp(mktime(
            23, 59, 59,
            $this->get(self::MONTH),
            $this->get(self::DAY),
            $this->get(self::YEAR)
        ));
    }

    /**
     * Returns this, change timestamp on week start time
     *
     * @return Axis_Date provides fluent interface
     */
    public function getStartWeek()
    {//@todo start week day as config option
        $this->subDay($this->get(Zend_Date::WEEKDAY_DIGIT));
        return $this->setTimestamp(mktime(
            23, 59, 59,
            $this->get(self::MONTH),
            $this->get(self::DAY),
            $this->get(self::YEAR)
        ));
    }

    /**
     * Returns this, change timestamp on month start time
     *
     * @return Axis_Date provides fluent interface
     */
    public function getStartMonth()
    {
        $prevMonth = $this->subMonth(1);
        return $this->setTimestamp(mktime(
            23, 59, 59,
            $prevMonth->get(self::MONTH),
            $prevMonth->get(self::MONTH_DAYS),
            $prevMonth->get(self::YEAR)
        ));
    }
}