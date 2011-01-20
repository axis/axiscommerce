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
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Cache extends Axis_Db_Table
{
    protected $_name = 'core_cache';

    /**
     * @var array
     */
    private $_lifetime = array();

    /**
     * Returns an instance of Zend_Cache_Core
     *
     * @static
     * @return Zend_Cache_Core Provides a fluent interface
     */
    public static function getCache()
    {
        if (!Zend_Registry::isRegistered('cache')
            || !(Zend_Registry::get('cache') instanceof Zend_Cache_Core))
        {
            $defaultLifetime = Axis::model('core/config_value')
                ->select('value')
                ->where("path = 'core/cache/default_lifetime'")
                ->where('site_id IN (0, ?)', Axis::getSiteId())
                ->fetchOne();

            $frontendOptions = array(
                'lifetime'                  => $defaultLifetime,
                'automatic_serialization'   => true
            );
            $cacheDir = Axis::config()->system->path . '/var/cache';
            if (!is_readable($cacheDir)) {
                mkdir($cacheDir, 0777);
            } elseif(!is_writable($cacheDir)) {
                chmod($cacheDir, 0777);
            }
            if (!is_writable($cacheDir)) {
                echo "Cache directory should be writable. Run 'chmod -R 0777 path/to/var'";
                exit();
            }
            $backendOptions = array(
                'cache_dir'                 => $cacheDir,
                'hashed_directory_level'    => 1,
                'file_name_prefix'          => 'axis_cache',
                'hashed_directory_umask'    => 0777
            );
            Zend_Registry::set('cache', Zend_Cache::factory(
                'Core', 'Axis_Cache_Backend_File',
                $frontendOptions,
                $backendOptions,
                false,
                true
            ));
        }
        return Zend_Registry::get('cache');
    }

    /**
     * Retrieve array of cache tags with their lifetimes
     *
     * @return array
     */
    public function getList()
    {
        return $this->select()->fetchAll();
    }

    /**
     * Retrieve the list of disabled tags
     *
     * @return array
     */
    public function getDisabled()
    {
        return $this->select('name')->where('is_active = 0')->fetchCol();
    }

    /**
     * Retrieve the lifetime for the array of tags
     * In case if tags have different lifetime values -
     * min value will be returned
     *
     * @param mixed $tags
     * @return mixed (integer|false)
     */
    public function getLifetimeByTags($tags)
    {
        if (!is_array($tags)) {
            $tags = array($tags);
        }
        $key = implode('-', $tags);
        if (!array_key_exists($key, $this->_lifetime)) {
            $select = $this->select('lifetime')
                ->where('cc.name IN (?)', $tags);

            $result = array_filter($select->fetchCol());

            if (!count($result) || min($result) == 0) {
                $this->_lifetime[$key] = false;
            } else {
                $this->_lifetime[$key] = min($result);
            }
        }
        return $this->_lifetime[$key];
    }

    /**
     * Updates the cache rows
     *
     * @param array $data
     * @return bool
     */
    public function save($data)
    {
        foreach ($data as $id => $values) {
            if (!$row = $this->find($id)->current()) {
                $row = $this->createRow();
                $row->name = $values['name'];
            }
            if (is_array($values)) {
                $row->is_active = $values['is_active'];
                $row->lifetime = (int) $values['lifetime'] ?
                    (int)$values['lifetime'] : new Zend_Db_Expr('NULL');
            } else {
                $row->is_active = $values;
            }
            $row->save();
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Data was saved successfully'
        ));
        return true;
    }

    /**
     * Inserts row to core_cache table
     *
     * @param string $name
     * @param int $isActive
     * @param int $lifetime
     * @return Axis_Core_Model_Cache Provides fluent interface
     */
    public function add($name, $isActive = 1, $lifetime = null)
    {
        if ($this->select('id')->where('name = ?', $name)->fetchOne()) {
            return $this;
        }

        $this->createRow(array(
            'name'      => $name,
            'is_active' => $isActive,
            'lifetime'  => $lifetime
        ))->save();

        return $this;
    }

    /**
     * Clear cache linked with any of recieved tags
     *
     * @param mixed $tags
     * @return boolean
     */
    public function clean($tags = null)
    {
        if (null === $tags) {
            return self::getCache()->clean();
        }
        if (!is_array($tags)) {
            $tags = array($tags);
        }
        return self::getCache()->clean('matchingAnyTag', $tags);
    }
}