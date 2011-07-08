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
 * @package     Axis_Cache
 * @subpackage  Axis_Cache_Frontend
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Cache
 * @subpackage  Axis_Cache_Frontend
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
abstract class Axis_Cache_Frontend_Abstract
{
    protected $_instance = null;

    protected $_cacheByDefault = true;

    protected $_cachableMethods = array();

    protected $_nonCachedMethods = array();

    /**
     * Tags array
     *
     * @var array
     */
    protected $_tags = array();

    /**
     * SpecificLifetime value
     *
     * false => no specific life time
     *
     * @var int
     */
    protected $_specificLifetime = false;

    /**
     * Priority (used by some particular backends)
     *
     * @var int
     */
    protected $_priority = 8;

    /**
     * @param Object $class
     */
    abstract public function setInstance($instance);

    /**
     * Magic
     * @param string $methodName
     * @param array $arguments
     */
    abstract public function __call($methodName, $arguments);

    /**
     * Make a cache id from the method name and parameters
     *
     * @param  string $methodName       Method name
     * @param  array  $parameters Method parameters
     * @return string Cache id
     */
    abstract protected function _makeId($methodName, $parameters);

    /**
     * Set a specific life time
     *
     * @param  int $specificLifetime
     * @return void
     */
    public function setSpecificLifetime($specificLifetime = false)
    {
        $this->_specificLifetime = $specificLifetime;
        return $this;
    }

    /**
     * Set the priority (used by some particular backends)
     *
     * @param int $priority integer between 0 (very low priority) and 10 (maximum priority)
     */
    public function setPriority($priority)
    {
        $this->_priority = $priority;
        return $this;
    }

    /**
     * Set the cache array
     *
     * @param  array $tags
     * @return void
     */
    public function setTagsArray($tags = array())
    {
        $this->_tags = $tags;
        return $this;
    }
}