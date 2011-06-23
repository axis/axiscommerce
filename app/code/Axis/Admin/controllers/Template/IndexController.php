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
 */
class Axis_Admin_Template_IndexController extends Axis_Admin_Controller_Back
{

    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('admin')->__('Templates');

        $pages = Axis::model('core/page')->select(array(
                'id', 'name' => "CONCAT(module_name, '/', controller_name, '/', action_name)"
            ))->order(array('module_name', 'controller_name', 'action_name'))
            ->fetchAll();

        $this->view->pages = $pages;
        $this->view->boxClasses = Axis::single('core/template_box')->getList();
        $this->render();
    }

    public function getNodesAction()
    {
        $rowset = Axis::model('core/template')->fetchAll(null, 'name ASC');
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

        $this->_helper->json->sendRaw($data);
    }

    public function saveAction()
    {
        $this->_helper->layout->disableLayout();

        $data = $this->_getAllParams();

        $this->_helper->json->sendJson(array(
            'success' => Axis::single('core/template')->save($data)
        ));
    }

    public function loadAction()
    {
        $this->_helper->layout->disableLayout();

        $templateId = $this->_getParam('templateId');

        $data = Axis::single('core/template')->find($templateId)
            ->current()
            ->toArray();
        $this->_helper->json->setData($data)->sendSuccess();
    }

    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();

        $themeId = $this->_getParam('templateId');
        if (!$themeId) {
            return $this->_helper->json->sendFailure();
        }
        $modelTheme = Axis::model('core/template');
        if ($modelTheme->isUsed($themeId)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    "Template is used already and can't be deleted"
                )
            );
            return $this->_helper->json->sendFailure();
        }

        $modelTheme->delete($this->db->quoteInto('id = ? ', $themeId));

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Template was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }

    public function listXmlTemplatesAction()
    {
        $this->_helper->layout->disableLayout();

        $data = array();
        $handler = opendir(Axis::config('system/path') . '/var/templates/');
        while ($file = readdir($handler)) {
            if (is_dir($file)) {
                continue;
            }
            $pathinfo = pathinfo($file);
            if ('xml' !== $pathinfo['extension']) {
                continue;
            }
            $data[] = array('template' => $file);
        }
        closedir($handler);
        return $this->_helper->json->setData($data)->sendSuccess();
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
                Zend_Json_Encoder::encode($result)
            );
        }

        $themeFile = $result['data']['path'] . $result['data']['file'];
        $model = Axis::model('core/template');

        if (!$this->_getParam('overwrite_existing') &&
            !$model->validateBeforeImport($themeFile)) {

            return $this->getResponse()->appendBody(
                Zend_Json_Encoder::encode(array(
                    'errorCode' => 'template_exists'
                ))
            );
        }

        if (!$model->importTemplateFromXmlFile($themeFile)) {
            return $this->getResponse()->appendBody(
                Zend_Json_Encoder::encode(array(
                    'success' => false
                ))
            );
        }
        return $this->getResponse()->appendBody(
            Zend_Json_Encoder::encode(array(
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