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
 * @package     Axis_Event
 * @author      Axis Core Team <core@axiscommerce.com>
 */

/**
 * Event observer class
 *
 * @category    Axis
 * @package     Axis_Event
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Event_Observer
{
    /**
     * Array of loaded event information
     * @var array
     */
    private $_events = array();

    /**
     * Singleton instance
     * @var Axis_Event_Observer
     */
    private static $_instance = null;

    /**
     * Reads the event config from modules configuration
     * and cache it
     */
    private function __construct()
    {
        if ($result = Axis::cache()->load('module_event_list')) {
            $this->_events = $result;
        } else {
            foreach (Axis::single('core/module')->getConfig() as $code => $config) {
                if (isset($config['events']) && is_array($config['events'])) {
                    foreach ($config['events'] as $eventName => $action) {
                        foreach ($action as $key => $value) {
                            if (!is_array($value) || !count($value)) {
                                continue;
                            }
                            $this->_events[$eventName][] = $value;
                        }
                    }
                }
            }
            Axis::cache()->save(
                $this->_events, 'module_event_list', array('modules')
            );
        }
    }

    /**
     * Retrieve singleton instance of Axis_Event_Observer
     *
     * @return Axis_Event_Observer
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Dispatch event by name
     *
     * @param string $name
     * @param array $data [optional]
     * @return Axis_Event_Observer Provides fluent interface
     */
    public function dispatch($name, $data = array())
    {
        if (isset($this->_events[$name])) {
            foreach ($this->_events[$name] as $observer) {
                Axis::$observer['type']($observer['model'])->$observer['method']($data);
            }
        }
        return $this;
    }
}
