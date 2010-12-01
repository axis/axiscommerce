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
 * @subpackage  Axis_Cache_Backend
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Cache
 * @subpackage  Axis_Cache_Backend
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Cache_Backend_File extends Zend_Cache_Backend_File
{
    private $_disabledTags = array();

    /**
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        $this->_disabledTags = Axis::single('core/cache')->getDisabled();
    }

    /**
     * Extended parent save method
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param  string $data             Datas to cache
     * @param  string $id               Cache id
     * @param  array  $tags             Array of strings, the cache record will be tagged by each string entry
     * @param  int    $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @return boolean true if no problem
     */
    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        if (count(array_intersect($tags, $this->_disabledTags))) {
            return false;
        }

        if (!$specificLifetime && count($tags)) {
            $specificLifetime = Axis::single('core/cache')->getLifetimeByTags($tags);
        }
        return parent::save($data, $id, $tags, $specificLifetime);
    }

    /**
     * Extended parent load method
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * @param string $id cache id
     * @param boolean $doNotTestCacheValidity if set to true, the cache validity won't be tested
     * @return string|false cached datas
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        $metadatas = $this->getMetadatas($id);

        if (count($metadatas['tags'])) {
            if (count(array_intersect($metadatas['tags'], $this->_disabledTags))) {
                return false;
            }
        }

        return parent::load($id, $doNotTestCacheValidity);
    }

    /**
     * Extended parent test method
     * Test if a cache is available or not (for the given id)
     *
     * @param string $id cache id
     * @return mixed false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    public function test($id)
    {
        $metadatas = $this->getMetadatas($id);

        if (count($metadatas['tags'])) {
            if (count(array_intersect($metadatas['tags'], $this->_disabledTags))) {
                return false;
            }
        }
        return parent::test($id);
    }

}