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
 * @package     Axis_Import
 * @subpackage  Axis_Import_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Import
 * @subpackage  Axis_Import_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Import_Admin_IndexController extends Axis_Admin_Controller_Back
{
    private $_adapter;
    private $_supportedTypes = array(
        array('0', 'Creloaded')
    );

    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('admin')->__("Import");
        $this->view->languages = Axis_Locale_Model_Language::collect();
        $this->render();
    }

    public function listAction()
    {
        $data = Axis::single('import/profile')->getList();
        
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }

    public function saveAction()
    {
        $data = $this->_getParam('profile');

        Axis::single('import/profile')->save($data);
            
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Profile was saved successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }

    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));

        Axis::single('import/profile')->delete($data);
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Profile was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
    
    public function listTypeAction()
    {
        $this->_helper->json->sendRaw($this->_supportedTypes);
    }

    public function connectAction()
    {
        $profileOptions = $this->_getParam('profile');

        $this->_adapter = $this->_getAdapter($profileOptions);

        if (!$this->_adapter) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'Cannot connect to database'
                )
            );
            $this->_helper->json->sendFailure();
        }
        $languages = $this->_adapter->getLanguages();
        $queue = $this->_adapter->getQueue();

        $data = array(
            'languages' => $languages,
            'queue'     => $queue
        );
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }

    public function importAction()
    {
        $profile_options = $this->_getParam('profile');
        $general_options = $this->_getParam('general');
        $language_options = array_filter($this->_getParam('language', array()));
        $primary_language = $this->_getParam('primary_language');
        $data_options = $this->_getParam('data', array());

        if (!$primary_language) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'Set the primary language please'
                )
            );
            return $this->_helper->json->sendFailure(array(
                'finalize' => true,
            ));
        }
        if ($this->_getParam('clearSession') && !count($data_options)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'Select data to import'
                )
            );
            return $this->_helper->json->sendFailure(array(
                'finalize' => true
            ));
        }
        if (!count($language_options)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'At least one language should be determined'
                )
            );
            return $this->_helper->json->sendFailure(array(
                'finalize' => true
            ));
        }
        $this->_adapter = $this->_getAdapter($profile_options);

        if (!$this->_adapter) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'Cannot connect to database'
                )
            );
            return $this->_helper->json->sendFailure(array(
                'finalize' => true
            ));
        }
        if ($this->_getParam('clearSession')) {
            Axis::session()->import_queue = $this->_adapter->getQueue();
            Axis::session()->group_iterator = 0;
            Axis::session()->language_iterator = 0;
            unset($_SESSION['processed_count']);
            $data_options = array_keys($data_options);
            Axis::session()->group_queue = array_values(array_intersect($this->_adapter->getQueue(), $data_options));
        }

        $group_to_import = Axis::session()->group_queue[Axis::session()->group_iterator];

        $data = array();

        $data = $this->_adapter->import(
            $group_to_import,
            $language_options,
            $general_options['site'],
            $primary_language
        );

        $data['finalize'] = false;

        if ($data['completed_group'] && isset(Axis::session()->group_queue[Axis::session()->group_iterator+1])) {
            Axis::session()->group_iterator++;
        } elseif ($data['completed_group']) {
            $data['finalize'] = true;
        }

        return $this->_helper->json->sendSuccess(array(
            'silent' => true,
            'group' => $group_to_import, //wtf 1
            'finalize' => $data['finalize'],
            'processed' => $data['processed'],
            'imported' => $data['imported'],
            'messages' => $data['messages'],
            'group' => $data['group'] //wtf 2
        ));
    }

    private function _getAdapter($data)
    {
        $data['adapter'] = $this->db;
        $data['image_path'] = Axis::config()->system->path . '/media';

        return call_user_func(array('Axis_Import_Model_'.$data['type'], 'getInstance'), $data);
    }

    public function disconnectAction()
    {
        $this->_helper->layout->disableLayout();

        $profile_options = $this->_getParam('profile');
        $this->_adapter = $this->_getAdapter($profile_options);
        $this->_adapter->dispose();
    }
}