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
 * @package     Axis_Core
 * @subpackage  Axis_Core_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Admin_ThemeController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('core')->__('Themes');

        $pages = Axis::model('core/page')->select(array(
                'id', 'name' => "CONCAT(module_name, '/', controller_name, '/', action_name)"
            ))->order(array('module_name', 'controller_name', 'action_name'))
            ->fetchAll();

        $this->view->pages = $pages;
        $this->view->boxClasses = Axis::single('core/template_box')->getList();
        $this->render();
    }

    public function listAction()
    {
        $rowset = Axis::model('core/template')->select()
            ->order('name ASC')
            ->fetchRowset();

        foreach ($rowset as $row) {
            $data[] = array(
                'text'     => $row->name,
                'id'       => $row->id,
                'leaf'     => false,
                'cls'      => '',
                'children' => array(),
                'expanded' => true
            );
        }

        return $this->_helper->json->sendRaw($data);
    }

    public function loadAction()
    {
        $themeId = $this->_getParam('templateId');

        $data = Axis::single('core/template')->find($themeId)
            ->current()
            ->toArray();
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess();
    }

    public function saveAction()
    {
        $data  = $this->_getAllParams();
        $model = Axis::single('core/template');
        if (!empty($data['duplicate'])
            &&  $row = $model->duplicate($data['duplicate'], $data['name'])) {

            $row->default_layout = $data['default_layout'];
            $row->save();

        } else {
            $model->save($data);
        }
        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Template was saved successfully'
        ));
        return $this->_helper->json->sendSuccess();
    }

    public function removeAction()
    {
        $themeId = $this->_getParam('templateId');
        if (!$themeId) {
            return $this->_helper->json->sendFailure();
        }
        $model = Axis::model('core/template');
        if ($model->isUsed($themeId)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    "Template is used already and can't be deleted"
                )
            );
            return $this->_helper->json->sendFailure();
        }

        $model->delete($this->db->quoteInto('id = ? ', $themeId));

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Template was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }

    public function importAction()
    {
        $this->_helper->layout->disableLayout();

        try {
            $uploader = new Axis_File_Uploader('template');
            $file = $uploader
                ->setAllowedExtensions(array('xml'))
                ->setUseDispersion(false)
                ->save(Axis::config('system/path') . '/var/templates');

            $result = array(
                'success' => true,
                'data' => array(
                    'path' => $file['path'],
                    'file' => $file['file']
                )
            );
        } catch (Axis_Exception $e) {
            $result = array(
                'success' => false,
                'messages' => array(
                    'error' => $e->getMessage()
                )
            );
            return $this->getResponse()->appendBody(
                Zend_Json::encode($result)
            );
        }

        $themeFile = $result['data']['path'] . $result['data']['file'];
        $model = Axis::model('core/template');

        if (!$this->_getParam('overwrite_existing') &&
            !$model->validateBeforeImport($themeFile)) {

            return $this->getResponse()->appendBody(
                Zend_Json::encode(array(
                    'errorCode' => 'template_exists'
                ))
            );
        }

        if (!$model->importTemplateFromXmlFile($themeFile)) {
            return $this->getResponse()->appendBody(
                Zend_Json::encode(array(
                    'success' => false
                ))
            );
        }
        return $this->getResponse()->appendBody(
            Zend_Json::encode(array(
                'success' => true,
                'messages' => array(
                    'success' => Axis::translate('admin')->__(
                        'Template was imported successfully'
                    )
                )
            ))
        );
    }

    public function exportAction()
    {
        $this->_helper->layout->disableLayout();
        $templateId = $this->_getParam('templateId');
        $template = Axis::model('core/template')->getFullInfo($templateId);
        $this->view->template = $template;
        $script = $this->getViewScript('xml', false);

        $content = $this->view->render($script);
        $filename = $template['name'] . '.xml';

        $this->getResponse()
            ->clearAllHeaders()
            ->setHeader('Content-Description','File Transfer', true)
            ->setHeader('Content-Type','application/octet-stream', true)
            ->setHeader('Content-Disposition','attachment; filename=' . $filename, true)
            ->setHeader('Content-Transfer-Encoding','binary', true)
            ->setHeader('Expires','0', true)
            ->setHeader('Cache-Control','must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Pragma','public', true)
//            ->setHeader('Content-Length: ', filesize($content), true)
            ;
        $this->getResponse()->setBody($content);
    }
}