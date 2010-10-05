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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Log
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Log_Model_Visitor_Info extends Axis_Db_Table
{
    protected $_name = 'log_visitor_info';

    /**
     *
     * @param int $visitorId
     * @return Axis_Log_Model_Visitor_Info
     */
    public function updateVisitorInfo($visitorId)
    {
        if (!function_exists('__ternar')) {
            function __ternar($key) {
                return isset($_SERVER[$key]) ? $_SERVER[$key] : '';
            }
        }
        $rowData = array(
            'visitor_id'           => $visitorId,
            'http_refer'           => __ternar('HTTP_REFERER'),
            'user_agent'           => __ternar('HTTP_USER_AGENT'),
            'http_accept_charset'  => __ternar('HTTP_ACCEPT_CHARSET'),
            'http_accept_language' => __ternar('HTTP_ACCEPT_LANGUAGE'),
            'server_addr'          => __ternar('SERVER_ADDR'),
            'remote_addr'          => __ternar('REMOTE_ADDR')
        );
        if (!$row = $this->fetchRow('visitor_id = ' . $visitorId)) {
            $row = $this->createRow($rowData);
        } else {
            $row->setFromArray($rowData);
        }
        $row->save();
        return $this;
    }
}