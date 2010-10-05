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
 * @package     Axis_Debug
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Debug
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_FirePhp
{
    /**
     * @static
     * @var Zend_Log
     */
    private static $_logger;

    protected static $_time;

    protected static $_memory = 0;

    protected static $_memoryPeak = 0;

    protected static $_timer = null;

    //protected static $vars = null;


    /**
     * FirePhp logger
     *
     * @static
     * @return Zend_Log
     */
    public static function getLogger()
    {
        if (null === self::$_logger) {
            $writer = new Zend_Log_Writer_Firebug();
            self::$_logger = new Zend_Log($writer); 
        }
        return self::$_logger;
    }

    /**
     * 
     * @param mixed
     * @static
     * @return void
     */
    public static function dump()
    {
        $headers = apache_request_headers();
        $args = func_get_args();
        self::log($args[0]);
        print_r($headers['X-Requested-With']);
        if ((isset($headers['X_REQUESTED_WITH']) && $headers['X_REQUESTED_WITH'] == 'XMLHttpRequest') ||
             isset($headers['X-REQUESTED-WITH']) && $headers['X-REQUESTED-WITH'] == 'XMLHttpRequest'  ||
             isset($headers['X_Requested_With']) && $headers['X_Requested_With'] == 'XMLHttpRequest'  ||
             isset($headers['X-Requested-With']) && $headers['X-Requested-With'] == 'XMLHttpRequest' ) {

            print_r($args[0]);
            return;
        }
        Zend_Debug::dump($args[0], $args[1]);
    }

    /**
     * Write content to FirePhp log
     *
     * @static
     * @param mixed $content
     */
    public static function log($content, $extras = null)
    {
        /*if(empty($content)) {
            $content = 'NULL';
        }*/
        self::getLogger()->log($content, Zend_Log::INFO, $extras);
    }
    
    /**
     *  @static
     */
//    public static function dump($content)
//    {
//        self::log($content);
//    }

    /**
     * Return call stack
     *
     * @static
     * @return array
     */
    public static function callstack()
    {
        $trace = debug_backtrace();
        foreach ($trace as &$ent) {
            unset($ent['object']);
            unset($ent['args']);
        }
        unset($trace[0]);
        $trace = array_reverse($trace);
        self::getLogger()->log($trace, Zend_Log::INFO);
        return $trace;
    }
    
    /**
     * Dump vars
     *
     * @static
     * @param mixed dumped vars
     * @param string label   
     */
    public static function debug($dump, $label = null) 
    {
        if (null !== $label) {
            $dump = array($label, $dump);
        }
        
        self::getLogger()->debug($dump);
    }

    //@todo
    /**
     * @static
     * @return string
     */
    protected static function _getPosition()
    {
        $back = debug_backtrace();
        return basename(@$back[0]["file"]) . ':' . @$back[0]["line"];
    }
    /**
     * @copyright http://anton.shevchuk.name/php/debug-zend-framework-application-with-firephp/
     * @static
     */
    public static function timeStamp($comment = "")
    {
        if (!self::$_timer instanceof Zend_Wildfire_Plugin_FirePhp_TableMessage) {
            self::$_timer = new Zend_Wildfire_Plugin_FirePhp_TableMessage('Timer');
            self::$_timer->setBuffered(true);
            self::$_timer->setHeader(array('Time (sec)', 'Total (sec)', 'Memory (Kb)', 'Total (Kb)', 'Comment', 'File'));
            self::$_timer->setOption('includeLineNumbers', false);
        }
        $back = debug_backtrace();
        list ($msec, $sec)     = explode(chr(32), microtime());
        list ($mTotal, $mSec)  = self::getMemoryUsage();

        if (! isset(self::$_time)) {
            self::$_time["start"] = $sec + $msec;
            self::$_time["section"] = $sec + $msec;

            self::$_timer->addRow(array(
                sprintf("%01.4f", 0),
                sprintf("%01.4f", 0),
                $mSec,$mTotal,
                $comment,
                basename(@$back[0]["file"]) . ':' . @$back[0]["line"],
            ));
        } else {
            $start = self::$_time["section"];
            self::$_time["section"] = $sec + $msec;

            self::$_timer->addRow(array(
                sprintf("%01.4f", round(self::$_time["section"] - $start, 4)),
                sprintf("%01.4f", round(self::$_time["section"] - self::$_time["start"], 4)),
                $mSec,$mTotal,
                $comment,
                basename(@$back[0]["file"]) . ':' . @$back[0]["line"],
            ));
        }

        self::updateMessageLabel();

        Zend_Wildfire_Plugin_FirePhp::getInstance()->send(self::$_timer);
    }

    /**
     * Update the label of the message holding the profile info.
     *
     * @static
     * @return void
     */
    protected static function updateMessageLabel()
    {
        self::$_timer->setLabel(sprintf('Timer (%s sec @  %s Kb)', round(self::$_time["section"] - self::$_time["start"], 4), number_format(self::$_memory / 1024, 2, '.', ' ') ));
    }

    /**
     * @static
     * @return array
     */
    protected static function getMemoryUsage()
    {
        if (! function_exists('memory_get_usage')) {
            if (substr(PHP_OS, 0, 3) == 'WIN') {
                $output = array();
                exec('tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output);
                $memory = preg_replace('/[\D]/', '', $output[5]) * 1024;
            } else {
                $pid = getmypid();
                exec("ps -eo%mem,rss,pid | grep $pid", $output);
                $output = explode("  ", $output[0]);
                $memory = @$output[1] * 1024;
            }
        } else {
            $memory = memory_get_usage();
        }
        $memorySection = $memory - self::$_memory;
        $memoryTotal   = sprintf("%08s", $memory);
        $memorySection = sprintf("%08s", $memorySection);

        self::$_memory = $memory;

        return array($memoryTotal, $memorySection);
    }

    /**
     * 
     * @static
     * @return <type>
     */
    protected static function getMemoryPeak()
    {
        if (function_exists('memory_get_peak_usage')) {
            self::$_memoryPeak = sprintf("%07s", memory_get_peak_usage());
        }

        return self::$_memoryPeak;
    }
}