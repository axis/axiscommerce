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
 * @package     Axis_Log
 * @subpackage  Axis_Log_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Log
 * @subpackage  Axis_Log_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Log_Model_Observer
{
    public function log($observer) 
    {
        if (!Axis::config('log/main/enabled')){
            return false;
        }
        /**
         * @var $request Zend_Controller_Request_Abstract 
         */
        $request = $observer->getController()->getRequest();
        $url = $request->getScheme() . '://'
            . $request->getHttpHost()
            . $request->getRequestUri();
        $refer     = $request->getServer('HTTP_REFERER', '');
        $timestamp = Axis_Date::now()->toSQLString();
        $siteId    = Axis::getSiteId();
        
        // add new url request
        $modelUrlInfo = Axis::single('log/url_info');
        $rowUrlInfo = $modelUrlInfo->select()
            ->where('url = ?', $url)
            ->where('refer = ?', $refer)
            ->fetchRow();
        
        if (!$rowUrlInfo) {
            $rowUrlInfo = $modelUrlInfo->createRow(array(
                'url'   => $url,
                'refer' => $refer
            ));
            $rowUrlInfo->save();
        }
        
        //add/update visitor
        $visitor = Axis::single('log/visitor')->getVisitor();
        
        //add/update visitor info
        Axis::single('log/visitor_info')
            ->getRow(array(
                'visitor_id'           => $visitor->id,
                'user_agent'           => $request->getServer('HTTP_USER_AGENT', ''),
                'http_accept_charset'  => $request->getServer('HTTP_ACCEPT_CHARSET', ''),
                'http_accept_language' => $request->getServer('HTTP_ACCEPT_LANGUAGE', ''),
                'server_addr'          => $request->getServer('SERVER_ADDR', ''),
                'remote_addr'          => $request->getServer('REMOTE_ADDR', '')
            ))->save();
        
        Axis::single('log/url')->insert(array(
            'url_id'     => $rowUrlInfo->id,
            'visitor_id' => $visitor->id,
            'visit_at'   => $timestamp,
            'site_id'    => $siteId
        ));
    }
    
    public function login()
    {
        $visitor = Axis::single('log/visitor')->getVisitor();
        $visitor->customer_id = Axis::getCustomerId();
        $visitor->save();
    }
    
    public function logout()
    {
        unset(Axis::session()->visitorId);
        // ? Zend_Session::regenerateId();
    }
}