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
 * @subpackage  Axis_View_Helper
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Axis_View_Helper_Navigation
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Helper_Navigation_Breadcrumbs extends Zend_View_Helper_Navigation_Breadcrumbs
{
    
    /**
     * Breadcrumbs separator string
     *
     * @var string
     */
    protected $_separator = '';
    
    /**
     * The minimum depth a page must have to be included when rendering
     *
     * @var int
     */
    protected $_minDepth = 0;
    
    /**
     * Whether last page in breadcrumb should be hyperlinked
     *
     * @var bool
     */
    protected $_linkLast = false;
     
    /**
     * Renders breadcrumbs by chaining 'a' elements with the separator
     * registered in the helper
     *
     * @param  Zend_Navigation_Container $container  [optional] container to
     *                                               render. Default is to
     *                                               render the container
     *                                               registered in the helper.
     * @return string                                helper output
     */
    public function renderStraight(Zend_Navigation_Container $container = null)
    {
        if (null === $container) {
            $container = $this->getContainer();
        }

        // find deepest active
        if (!$active = $this->findActive($container)) {
            return '';
        }

        $active = $active['page'];

        // put the deepest active page last in breadcrumbs
        if ($this->getLinkLast()) {
            $html = $this->htmlify($active);
        } else {
            $html = $active->getLabel();
            if ($this->getUseTranslator() && $t = $this->getTranslator()) {
                $html = $t->translate($html);
            }
            $html = '<span>' . $this->view->escape($html) . '</span>';
        }
        $html = '<li>' . $html . '</li>';
        // walk back to root
        while ($parent = $active->getParent()) {
            if ($parent instanceof Zend_Navigation_Page) {
                // prepend crumb to html
                $html = '<li>' . $this->htmlify($parent) . '</li>'
                      . $this->getSeparator()
                      . $html;
            }

            if ($parent === $container) {
                // at the root of the given container
                break;
            }

            $active = $parent;
        }
        if (!strlen($html)) {
            return '';
        }

        return '<div class="breadcrumbs-container">' . 
            '<ul class="breadcrumbs">' . 
                $this->getIndent() . $html .
            '</ul>' .
        '</div>';
    }
}