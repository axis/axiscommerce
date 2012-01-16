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
 * @package     Axis_View
 * @subpackage  Axis_View_Helper_Front
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Axis_View_Helper_Front
 * @author      Axis Core Team <core@axiscommerce.com>
 */

/**
 * See also
 * @link http://www.zfsnippets.com/snippets/view/id/30
 */
 
class Axis_View_Helper_GoogleAnalytics
{
    /**
     * Tracker options instance
     */
    protected $_options = array();

    /**
     * Available Trackers options
     */
    static protected $_availableOptions = array(
        // Standard Options
        '_setAccount',
        '_trackPageview',
        '_setVar',
 
        // ECommerce Options
        '_addItem',
        '_addTrans',
        '_trackTrans',
 
        // Tracking Options
        '_setClientInfo',
        '_setAllowHash',
        '_setDetectFlash',
        '_setDetectTitle',
        '_setSessionTimeOut',
        '_setCookieTimeOut',
        '_setDomainName',
        '_setAllowLinker',
        '_setAllowAnchor',
 
        // Campaign Options
        '_setCampNameKey',
        '_setCampMediumKey',
        '_setCampSourceKey',
        '_setCampTermKey',
        '_setCampContentKey',
        '_setCampIdKey',
        '_setCampNoKey',
 
        // Other
        '_addOrganic',
        '_addIgnoredOrganic',
        '_addIgnoredRef',
        '_setSampleRate',
    );

    /**
     *
     * @param string $trackerId the google analytics tracker id
     * @return $this for more fluent interface
     */
    public function GoogleAnalytics($trackerId = null)
    {
        if (!is_null($trackerId)) {
            $this->setAccount($trackerId);
        }
        return $this;
    }

    /**
     *
     * @param string $method
     * @param array $args
     * @return $this for more fluent interface
     */
    public function __call($method, $args)
    {        
        if (false === in_array($method, self::$_availableOptions)) {
            throw new Axis_Exception(
                Axis::translate('GoogleAnalytics')->__(
                    'Unknown "%s" GoogleAnalytics options', $method
            ));
        }
        array_unshift($args, $method);
        $this->_options[] = $args;

        return $this;
    }
    
    /**
     * Cast to string representation
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
    
    /**
     * Rendering Google Analytics Tracker script
     */
    public function toString()
    {
        $xhtml = array();
        $xhtml[] = '<script type="text/javascript">';
        $xhtml[] = "var _gaq = _gaq || [];";
        $options = array();
        foreach ($this->_options as $_option) {
            $args = array();
            foreach ($_option as $arg) {
                $args[] = '"' . addslashes($arg) . '"';
            }
            $options[] = '[' . implode(',', $args) . ']';
        }
        $xhtml[] = "_gaq.push(\n\t" . implode(",\n\t", $options) .  "\n);";
        
        $xhtml[] = '(function() {';
        $xhtml[] = "\tvar ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;";
        $xhtml[] = "\tga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';";
        $xhtml[] = "\tvar s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);";
        $xhtml[] = '})();';
        $xhtml[] = '</script>';

        return implode("\n", $xhtml);
    }
}