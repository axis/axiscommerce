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
 * @package     Axis_Catalog
 * @subpackage  Axis_Catalog_Box
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Box
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Box_Breadcrumbs extends Axis_Core_Box_Abstract
{
    protected $_disableWrapper = true;

    protected function _construct()
    {
        // disable cache, because this block
        // already did all hard logic inside current action
        $this->setData('cache_lifetime', 0);
    }

    protected function _beforeRender()
    {
        $breadcrumbs = Zend_Controller_Action_HelperBroker::getStaticHelper('breadcrumbs')
            ->getContainer();
        $this->setData('breadcrumbs', $breadcrumbs);
    }

    public function getConfigurationFields()
    {
        return array(
            'link_last' => array(
                'fieldLabel' => Axis::translate('core')->__(
                    'Show last item as link'
                ),
                'initialValue' => 0,
                'data' => array(
                    0 => 'No',
                    1 => 'Yes'
                )
            )
        );
    }

//    protected function _getCacheKeyInfo()
//    {
//        $request = Zend_Controller_Front::getInstance()->getRequest();
//        $hurl    = Axis_HumanUri::getInstance();
//        return array(
//            $request->getModuleName()
//                . $request->getControllerName()
//                . $request->getActionName(),
//            $hurl->getParamValue('cat'),
//            $hurl->getParamValue('manufacturer'),
//            $hurl->getParamValue('product'),
//            $request->getParam('product')
//        );
//    }
}
