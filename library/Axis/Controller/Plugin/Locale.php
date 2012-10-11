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
 * @subpackage  Plugin
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Controller
 * @subpackage  Plugin
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Controller_Plugin_Locale extends Zend_Controller_Plugin_Abstract
{
    /**
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        if (Axis_Area::isFrontend()
            && null !== $request->getParam('locale')) {

            $locale = $request->getParam('locale');
        } elseif (isset(Axis::session()->locale)) {
            $locale = Axis::session()->locale;
        } else {
            $locale = Axis::config('locale/main/locale');
        }

        if (!Axis_Locale::isValid($locale)) {
            $locale = Axis_Locale::DEFAULT_LOCALE;
        }
        Zend_Registry::set('Zend_Locale', new Zend_Locale($locale));

        //
        $languages = Axis::single('locale/language')
            ->select(array('locale', 'id'))
            ->fetchPairs();

        if (isset($languages[$locale])) {
            $language = $languages[$locale];
        } else {
            $language = Axis::config('locale/main/language_' . Axis_Area::getArea());
        }

        if (!array_search($language, $languages)) {
            $language = current($languages);
        }

        Axis::session()->language = $language;
    }
}
