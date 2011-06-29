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
 * @package     Axis_Message
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Message
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Message
{
    /**
     * $_namespace - namespace inside storage object
     *
     * @static
     * @var string
     */
    private static $_namespace = null;
    
    /**
     * $_session - Zend_Session_Namespace storage object
     *
     * @var Zend_Session_Namespace
     */
    static private $_session = null;
    
    /**
     * Singleton instance of Axis_Message
     *
     * @static
     * @var Axis_Message
     */
    private static $_instance = null;

    /**
     * 
     */
    private function __construct()
    {
        if (!self::$_session instanceof Zend_Session_Namespace) {
            self::$_session = new Zend_Session_Namespace(self::$_namespace);
        }
    }
    
    /**
     * Retrieve Axis_Message object
     *
     * @static
     * @return Axis_Message
     */
    public static function getInstance($namespace = 'messenger')
    {
        self::_setNamespace($namespace);
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Set current message namespace
     *
     * @static
     * @param string $value
     * @return void
     */
    private static function _setNamespace($value)
    {
        self::$_namespace = $value;
    }
    
    /**
     * Add message to current namespace
     * 
     * @param string $message
     * @param string $type
     * @return Axis_Message Provides a fluent interface
     */
    public function add($message, $type)
    {
        if (!is_array(self::$_session->{self::$_namespace}) 
            || !isset(self::$_session->{self::$_namespace}[$type])) {

            self::$_session->{self::$_namespace}[$type] = array();
        }
        self::$_session->{self::$_namespace}[$type][] = $message;
        return $this;
    }
    
    /**
     * Add success message to current namespace
     * 
     * @param string $message
     * @return Axis_Message Provides a fluent interface
     */
    public function addSuccess($message)
    {
        $this->add($message, 'success');
        return $this;
    }
    
    /**
     * Add notice message to current namespace
     * 
     * @param string $message
     * @return Axis_Message Provides a fluent interface
     */
    public function addNotice($message)
    {
        $this->add($message, 'notice');
        return $this;
    }
    
    /**
     * Add warning message to current namespace
     * 
     * @param string $message
     * @return Axis_Message Provides a fluent interface
     */
    public function addWarning($message)
    {
        $this->add($message, 'warning');
        return $this;
    }
    
    /**
     * Add error message to current namespace
     * 
     * @param string $message
     * @return Axis_Message Provides a fluent interface
     */
    public function addError($message)
    {
        $this->add($message, 'error');
        return $this;
    }
    
    /**
     * Add set of messages
     * 
     * @param array $messages
     * @param string $type
     * @return Axis_Message Provides a fluent interface
     */
    public function batchAdd($messages, $type)
    {
        $type = strtolower($type);
        foreach ($messages as $message) {
            $this->add($message, $type);
        }
        return $this;
    }
    
    /**
     * Clears current message namespace
     * 
     * @return Axis_Message Provides a fluent interface
     */
    public function clear()
    {
        self::$_session->{self::$_namespace} = array();
        return $this;
    }
    
    /**
     * Retrieve array of messages from surrent namespace
     * 
     * @param string $type
     * @param string $namespace
     * @return array messages array
     */
    public function get($type = null, $namespace = null)
    {
        if (null !== $type || null !== $namespace) {
            return $this->_getByFilter($type, $namespace);
        }
        
        $messages = array();
        
        if (isset(self::$_session->{self::$_namespace})) {
            $messages = self::$_session->{self::$_namespace};
            unset(self::$_session->{self::$_namespace});
        }
        
        return $messages;
    }
    
    /**
     * Retrieve array of all messages from available namespaces
     * 
     * @return array messages
     */
    public function getAll()
    {
        $messages = array();
        
        foreach (self::$_session->getIterator() as $messageNamespace => $messageStack) {
            foreach ($messageStack as $messageType => $messageArray) {
                if (!isset($messages[$messageType])) {
                    $messages[$messageType] = array();
                }
                foreach ($messageArray as $message) {
                    $messages[$messageType][] = $message;
                }
            }
            unset(self::$_session->{$messageNamespace});
        }
        return $messages;
    }
    
    /**
     * Retrieve array of messages by provided filter
     * 
     * @param string $type
     * @param string $namespace
     * @return array messages array
     */
    private function _getByFilter($type = null, $namespace = null)
    {
        $messages = array();
        
        if (null !== $type && null !== $namespace) {
                
            if (is_array(self::$_session->{$namespace}) 
                && isset(self::$_session->{$namespace}[$type])) {
                
                $messages[$type] = self::$_session->{$namespace}[$type];
                unset(self::$_session->{$namespace}[$type]);
            }
            
        } elseif (null !== $type) {
            
            foreach (self::$_session->getIterator() as $messageNamespace => $messageStack) {
                foreach ($messageStack as $messageType => $messageArray) {
                    if ($messageType != $type) {
                        continue;
                    }
                    foreach ($messageArray as $message) {
                        $messages[$messageType][] = $message;
                    }
                    unset(self::$_session->{$messageNamespace}[$messageType]);
                }
            }
            
        } elseif (is_array(self::$_session->{$namespace})) {
            
            $messages = self::$_session->{$namespace};
            unset(self::$_session->{$namespace});
            
        }
        
        return $messages;
    }
    
}