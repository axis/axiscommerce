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
 * @subpackage  Axis_Controller_Plugin
 * @copyright   Copyright 2008-2012 Axis
 * @copyright   $Author: danila $
 * @license     GNU Public License V3.0
 */

/**
 * Plugin to support conditional GET for php pages (using ETag)
 * Should be loaded the very last in the plugins stack
 *
 * @category    Axis
 * @package     Axis_Controller
 * @subpackage  Axis_Controller_Plugin
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Controller_Plugin_Conditional extends Zend_Controller_Plugin_Abstract
{

    public function dispatchLoopShutdown()
    {
        $send_body = true;

        $etag = '"' . md5($this->getResponse()->getBody()) . '"';

        $inm = split(',', getenv("HTTP_IF_NONE_MATCH"));

        $inm = str_replace('-gzip', '', $inm);

        // @TODO If the request would, without the If-None-Match header field,
        // result in anything other than a 2xx or 304 status,
        // then the If-None-Match header MUST be ignored

        foreach ($inm as $i) {
            if (trim($i) == $etag) {
                $this->getResponse()
                     ->clearAllHeaders()
                     ->setHttpResponseCode(304)
                     ->clearBody();
                $send_body = false;
                break;
            }
        }

        $this->getResponse()
             ->setHeader('Cache-Control', 'max-age=7200, must-revalidate', true)
             ->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + 2 * 3600) . ' GMT', true)
             ->clearRawHeaders();

        if ($send_body) {
            $this->getResponse()
                 ->setHeader('Content-Length', strlen($this->getResponse()->getBody()));
        }

        $this->getResponse()->setHeader('ETag', $etag, true);
        $this->getResponse()->setHeader('Pragma', '');

    }
}
