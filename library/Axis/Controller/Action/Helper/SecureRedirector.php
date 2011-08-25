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
 * @package     Axis_Controller
 * @subpackage  Axis_Controller_Action_Helper
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Controller
 * @subpackage  Axis_Controller_Action_Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Controller_Action_Helper_SecureRedirector extends Zend_Controller_Action_Helper_Abstract
{
    public function preDispatch()
    {
        $return = Axis_Area::isBackend() && Axis::config('core/backend/ssl') 
            || (($this->getActionController() instanceof Axis_Core_Controller_Front_Secure)
                && Axis::config('core/frontend/ssl'));
        
        if (!$return) {
            return;
        } 
        
        $request = $this->getRequest();
        if ($request->isSecure()) {
            return;
        }
        
        $url = Zend_Controller_Request_Http::SCHEME_HTTPS . "://" 
            . $request->getServer('HTTP_HOST') 
            . $request->getRequestUri();

        $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        $redirector->setGoToUrl($url);
        $redirector->redirectAndExit();
    }
}
