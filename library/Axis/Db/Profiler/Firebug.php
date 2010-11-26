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
 * @package     Axis_Db
 * @subpackage  Axis_Db_Profiler
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Db
 * @subpackage  Axis_Db_Profiler
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Db_Profiler_Firebug extends Zend_Db_Profiler_Firebug
{
    /**
     * Xdebug availability flag adds a backtrace coloumn for each query.
     * @var boolean
     */
    protected $_xdebugAvailable = false;

    /**
     * Constructor
     *
     * @param string $label OPTIONAL Label for the profiling info.
     * @return void
     */
    public function __construct($label = null)
    {
        $this->_label = $label;
        if(!$this->_label) {
            $this->_label = 'Zend_Db_Profiler_Firebug';
        }
    if (function_exists('xdebug_get_function_stack')) {
            $this->_xdebugAvailable = true;
        }
    }

    /**
     * Enable or disable the profiler.  If $enable is false, the profiler
     * is disabled and will not log any queries sent to it.
     *
     * @param  boolean $enable
     * @return Zend_Db_Profiler Provides a fluent interface
     */
    public function setEnabled($enable)
    {
        parent::setEnabled($enable);

        if ($this->getEnabled()) {

            if (!$this->_message) {
                $this->_message = new Zend_Wildfire_Plugin_FirePhp_TableMessage($this->_label);
                $this->_message->setBuffered(true);
                //$this->_message->setHeader(array('Time','Event','Parameters'));
        $this->_message->setHeader(array('Time','Event','Parameters','Backtrace'));
                $this->_message->setDestroy(true);
                $this->_message->setOption('includeLineNumbers', false);
                Zend_Wildfire_Plugin_FirePhp::getInstance()->send($this->_message);
            }

        } else {

            if ($this->_message) {
                $this->_message->setDestroy(true);
                $this->_message = null;
            }

        }

        return $this;
    }

    /**
     * Intercept the query end and log the profiling data.
     *
     * @param  integer $queryId
     * @throws Zend_Db_Profiler_Exception
     * @return void
     */
    public function queryEnd($queryId)
    {
        parent::queryEnd($queryId);

        if (!$this->getEnabled()) {
            return;
        }

        $this->_message->setDestroy(false);

        $profile = $this->getQueryProfile($queryId);

        $this->_totalElapsedTime += $profile->getElapsedSecs();

        $this->_message->addRow(array((string)round($profile->getElapsedSecs(),5),
                                      $profile->getQuery(),
                                      //($params=$profile->getQueryParams())?$params:null));
                    ($params=$profile->getQueryParams())?$params:null,
                    $this->getXdebugStack()));

        $this->updateMessageLabel();
    }

    /**
     * Returns the xdebug stack.
     *
     * @return array|string The xdebug stack as an array or string if unavailable / error
     */
    protected function getXdebugStack()
    {
        if (!$this->_xdebugAvailable) {
            return 'xdebug not installed';
        }

        $debugStack = array();

        foreach (xdebug_get_function_stack() as $i => $stack) {

            if (!isset($stack['function'])) {
                $stack['function'] = '';
            }

            if (!empty($stack['function'])) {
                // if the function == queryEnd from above, exit the loop to
                // skip the last two entries of queryEnd and getXDebugStack
                if ($stack['function'] == 'queryEnd') {
                    break;
                }

                // add the class name too
                if (isset($stack['class']) && !empty($stack['class'])) {
                    $stack['function'] = $stack['class'] . '::' . $stack['function'];
                }

                $stack['function'] = sprintf('in "%s" ', $stack['function']);
            }

            $debugStack[] = sprintf('%scalled from %s at line %s', $stack['function'], $stack['file'], $stack['line']);
        }

        if (empty($debugStack)) {
            $debugStack = 'backtrace unavailable';
        }

        return $debugStack;
    }

    /**
     * Update the label of the message holding the profile info.
     *
     * @return void
     */
    protected function updateMessageLabel()
    {
        if (!$this->_message) {
            return;
        }
        $this->_message->setLabel(str_replace(array('%label%',
                                                    '%totalCount%',
                                                    '%totalDuration%'),
                                              array($this->_label,
                                                    $this->getTotalNumQueries(),
                                                    (string)round($this->_totalElapsedTime,5)),
                                              $this->_label_template));
    }
}