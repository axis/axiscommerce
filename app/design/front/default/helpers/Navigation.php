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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Helper_Navigation
{
    private $_activeCategories = null;

    public function setView($view)
    {
        $this->view = $view;
    }

    public function navigation(array $items)
    {
        if (!count($items)) {
            return '';
        }

        if (!is_array($this->_activeCategories)) {
            $this->_activeCategories = array();
            if (Zend_Registry::isRegistered('catalog/current_category')) {
                $this->_activeCategories = array_keys(
                    Axis::single('catalog/category')
                        ->find(Zend_Registry::get('catalog/current_category'))
                        ->current()
                        ->cache()
                        ->getParentItems()
                );
            }
        }

        $html = '';//'<ul class="navigation">' . "\n";
        foreach ($items as $key => $item) {
            $prev = $item['lvl'];
            $next = isset($items[$key + 1]) ? $items[$key + 1] : null;
            $url = $this->view->hurl(array(
                'cat' => array('value' => $item['id'], 'seo' => $item['key_word']),
                'controller' => 'catalog',
                'action' => 'view'
            ), false, true);

            $html .= '<li class="';
            $html .= 'level' . ($item['lvl'] - 1);
            if ($next['lvl'] > $item['lvl']) {
                $html .= ' parent';
            }
            if (in_array($item['id'], $this->_activeCategories)) {
                $html .= ' active';
            }
            $html .= ' nav-' . str_replace('.', '-', $item['key_word']);
            $html .= '"><a href="' . $url . '"><span>' . $this->view->escape($item['name']) . '</span></a>';

            if (null === $next || $next['lvl'] < $item['lvl']) {
                $html .= '</li>' . "\n";
                if (null === $next) {
                    $html .= '</ul>' . "\n";
                    $html .= str_repeat('</li></ul>' . "\n", $item['lvl'] - $next['lvl'] - 1);
                } else {
                    $html .= str_repeat('</ul></li>' . "\n", $item['lvl'] - $next['lvl']);
                }
            } elseif ($next['lvl'] > $item['lvl'] && $prev > -1) {
               $html .=  "\n" . '<ul class="level' . ($item['lvl'] - 1) . '">' . "\n";
            } else {
               $html .= '</li>' . "\n";
            }
        }
        return substr($html, 0, -6); //remove last </ul>\n
    }
}