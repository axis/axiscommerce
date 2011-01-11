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
 * @copyright   Copyright 2008-2010 Axis
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
    /**
     * Acl
     * @var Axis_Acl
     */
    public $acl;

    /**
     *
     * @var bool
     */
    protected $_disableAuth = false;

    /**
     *
     * @var bool
     */
    protected $_disableAcl  = false;

    public function init()
    {
        parent::init();

        $this->view->adminUrl = '/' . trim(
            Axis::config('core/backend/route'), '/ '
        );

        Zend_Auth::getInstance()->setStorage(
            new Zend_Auth_Storage_Session('admin')
        );

        $this->acl = Axis::single('admin/acl');
        if (!empty(Axis::session()->roleId)) {
            $this->acl->loadRules(Axis::session()->roleId);
        }
    }

    public function preDispatch()
    {
        $request = $this->getRequest();
        $currentUrl = $request->getScheme() . '://'
            . $request->getHttpHost()
            . $request->getRequestUri();

        if (Axis::config('core/backend/ssl')
            && 0 !== strpos($currentUrl, $this->view->secureUrl)) {

            $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
            $requestUri = substr($request->getRequestUri(), strlen($baseUrl));
            parent::_redirect($this->view->secureUrl . $requestUri, array(), false);
            die();
        }

        $this->auth();
        $this->checkPermission();
    }

    public function auth()
    {
        if ($this->_disableAuth) {
            return;
        }
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            if ($this->getRequest()->isXmlHttpRequest()) {
                Axis::message()->addError(
                    Axis::translate('admin')->__(
                        'Your session has been expired. Please relogin'
                    )
                );

                $this->_helper->json->sendFailure();
                return;
                // Zend_Controller_Action_Helper_Json if $suppressExit = true;
            }
            $this->_forward('index', 'auth', 'Axis_Admin');
        } elseif (!Axis::single('admin/user')->find(
                Zend_Auth::getInstance()->getIdentity())->current()) {
            $this->view->action('logout', 'auth', 'Axis_Admin');
        }
    }

    public function checkPermission()
    {
        if ($this->_disableAcl) {
            return true;
        }

        $request = $this->getRequest();

        $action = $request->getActionName();
        //$controller = str_replace('_', '/', $request->getControllerName());
        $controller = $request->getControllerName();
        $role = Axis::session()->roleId;
        $resourceIds = explode('/', "admin/$controller/$action");
        // admin is the name of parent resource_id

        while (count($resourceIds)) {
            $resourceId = implode('/', $resourceIds);
            if ($this->acl->has($resourceId)) {
                if ($this->acl->isAllowed($role, $resourceId)) {
                    return true;
                } else {
                    break;
                }
            }
            array_pop($resourceIds);
        }

        if ($request->isXmlHttpRequest()) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'You have no permission for this operation'
                )
            );

            $this->_helper->json->sendFailure();
            return;
            // Zend_Controller_Action_Helper_Json if $suppressExit = true;
        }

        $this->_forward('access-denied', 'informer', 'Axis_Admin');
        return false;
    }

    /**
     * Set "actionName to aclResource" assignment
     *
     * Assignment used for auto-checkPermission in preDispatch method
     *
     * Use this method in init() method
     *
     * Example:
     * setActionToAclAssignment(array(
     *  'index' => 'admin/site/view',
     *  'edit'  => 'admin/site/edit'
     * ))
     *
     * @param array
     */
    public function setActionToAclAssignment($assignment)
    {
        $this->_aclAssignment = $assignment;
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
        $httpReferer = $this->getRequest()->getServer('HTTP_REFERER');
        if (($httpReferer && $url == $httpReferer) || !$addAdmin) {
            parent::_redirect($url, $options);
        }

        parent::_redirect($this->view->adminUrl . '/' . ltrim($url, '/ '), $options);
    }
}