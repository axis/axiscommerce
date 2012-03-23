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
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 * @abstract
 */
abstract class Axis_Admin_Controller_Back extends Axis_Controller_Action
{
    public function init()
    {
        parent::init();

        $this->view->adminUrl = '/' . trim(
            Axis::config('core/backend/route'), '/ '
        );
    }

    /**
     * Redirect to another URL. Adds adminRoute by default to given $url parameter
     *
     * @param string $url
     * @param bool $addAdmin
     * @param array $options Options to be used when redirecting
     * @return void
     */
     //@todo */*/* === referer , */*/otherAction
    protected function _redirect($url, array $options = array(), $addAdmin = true)
    {
        if (0 === strpos($url, 'http://')
            || 0 === strpos($url, 'https://')
            || !$addAdmin) {

            parent::_redirect($url, $options);
            return;
        }

        parent::_redirect($this->view->adminUrl . '/' . ltrim($url, '/ '), $options);
    }
}